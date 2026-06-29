<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\Setting;
use App\Mail\InquiryAdminMail;
use App\Mail\InquiryMail;
use App\Models\Article;
use App\Models\EmailRecipient;
use App\Models\ArticleCategory;
use App\Models\Menu;
use App\Models\MenusHasPages;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PublicPageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::with([
                'album.banners' => function ($q) {
                    $q->orderBy('order');
                }
            ])
            ->where('slug', $slug)
            ->where('status', 'PUBLISHED')
            ->firstOrFail();

        return response()->json([
            'id'        => $page->id,
            'title'     => $page->name,
            'label'     => $page->label,
            'slug'      => $page->slug,
            'content'   => $page->contents,
            'json'      => $page->json,
            'styles'    => $page->styles,
            'page_type' => $page->page_type,
            'template'  => $page->template,
            'image_url' => $page->image_url,

            'meta' => [
                'title'       => $page->meta_title,
                'description' => $page->meta_description,
                'keywords'    => $page->meta_keyword,
            ],
            'album' => ($page->album && $page->album->id != 0) ? [
                'id'             => $page->album->id,
                'name'           => $page->album->name,
                'type'           => $page->album->type,
                'banner_type'    => $page->album->banner_type,
                'transition'     => $page->album->transition,
                'transition_in'  => $page->album->transition_in,
                'transition_out' => $page->album->transition_out,
                'banners' => $page->album->banners->map(function ($banner) {
                    return [
                        'id'          => $banner->id,
                        'title'       => $banner->title,
                        'description' => $banner->description,
                        'alt'         => $banner->alt,
                        'image_url'   => $banner->image_path,
                        'button_text' => $banner->button_text,
                        'url'         => $banner->url,
                        'order'       => $banner->order,
                    ];
                })->values(),
            ] : null,
        ]);
    }

    public function active()
    {
        $menu = Menu::where('is_active', true)->first();

        if (!$menu) {
            return response()->json(['data' => null]);
        }

        $items = $this->resolveActiveMenuItems($menu);

        return response()->json([
            'data' => [
                'id'    => $menu->id,
                'name'  => $menu->name,
                'items' => $items,
            ],
        ]);
    }

    /**
     * Build the public menu from the same source as the Laravel admin (CMS → Menu).
     * Prefer pages_json when present; otherwise fall back to menus_has_pages rows.
     */
    private function resolveActiveMenuItems(Menu $menu): array
    {
        $jsonItems = is_string($menu->pages_json)
            ? json_decode($menu->pages_json, true)
            : ($menu->pages_json ?? []);

        if (is_array($jsonItems) && count($jsonItems) > 0) {
            $pageIds = $this->collectPageIds($jsonItems);
            $pages = Page::whereIn('id', $pageIds)
                ->select('id', 'slug', 'name', 'label', 'status')
                ->get()
                ->keyBy('id');

            return $this->normalizeItems($jsonItems, $pages);
        }

        return $this->buildItemsFromNavigation($menu);
    }

    private function buildItemsFromNavigation(Menu $menu): array
    {
        return $menu->parent_navigation()
            ->map(fn (MenusHasPages $item) => $this->navigationItemToApi($item))
            ->filter()
            ->values()
            ->all();
    }

    private function navigationItemToApi(MenusHasPages $item): ?array
    {
        if ($item->is_page_type()) {
            $page = $item->page;

            if (!$page || strtoupper((string) $page->status) !== 'PUBLISHED') {
                return null;
            }

            $label = trim((string) ($item->label ?: ($page->label ?: $page->name)));
            $children = $item->sub_pages_by_order()
                ->map(fn (MenusHasPages $subItem) => $this->navigationItemToApi($subItem))
                ->filter()
                ->values()
                ->all();

            return [
                'id'       => $item->id,
                'label'    => $label,
                'type'     => 'page',
                'target'   => '/public/' . $page->slug,
                'children' => $children,
            ];
        }

        if ($item->is_external_type()) {
            $children = $item->sub_pages_by_order()
                ->map(fn (MenusHasPages $subItem) => $this->navigationItemToApi($subItem))
                ->filter()
                ->values()
                ->all();

            return [
                'id'       => $item->id,
                'label'    => trim((string) $item->label),
                'type'     => 'external',
                'target'   => $item->uri ?: '#',
                'children' => $children,
            ];
        }

        return null;
    }

    private function collectPageIds(array $items): array
    {
        $ids = [];
        foreach ($items as $item) {
            if (!empty($item['page_id'])) {
                $ids[] = $item['page_id'];
            }
            if (!empty($item['children'])) {
                $ids = array_merge($ids, $this->collectPageIds($item['children']));
            }
        }
        return $ids;
    }

    private function normalizeItems(array $items, $pages = null): array
    {
        return collect($items)->map(function ($item) use ($pages) {
            $type = $item['type'] ?? 'page';

            if ($type === 'page') {
                $page = !empty($item['page_id']) && $pages
                    ? $pages->get($item['page_id'])
                    : null;

                if ($page && strtoupper((string) $page->status) !== 'PUBLISHED') {
                    return null;
                }

                $target = $item['target'] ?? '';
                if (empty($target) && $page) {
                    $target = '/public/' . $page->slug;
                }

                $label = trim((string) ($item['label'] ?? ''));
                if ($label === '' && $page) {
                    $label = trim((string) ($page->label ?: $page->name));
                }

                return [
                    'id'       => $item['id'] ?? $item['page_id'] ?? null,
                    'label'    => $label,
                    'type'     => 'page',
                    'target'   => $target ?: '#',
                    'children' => $this->normalizeItems($item['children'] ?? [], $pages),
                ];
            }

            if ($type === 'external') {
                return [
                    'id'       => $item['id'] ?? null,
                    'label'    => trim((string) ($item['label'] ?? '')),
                    'type'     => 'external',
                    'target'   => $item['uri'] ?? ($item['target'] ?? '#'),
                    'children' => $this->normalizeItems($item['children'] ?? [], $pages),
                ];
            }

            return null;
        })->filter()->values()->all();
    }

    public function footer()
    {
        $footer = Page::where('slug', 'footer')
            ->where('status', 'PUBLISHED')
            ->first();

        if (!$footer) {
            return response()->json([
                'message' => 'Footer not found'
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $footer->id,
                'slug' => $footer->slug,
                'content' => $footer->contents,
                'contents' => $footer->contents,
                'styles' => $footer->styles,
                'json' => $footer->json,
            ]
        ]);
    }
    
    public function public_articles(Request $request)
    {
        $query = Article::query()
            ->with(['category:id,name,slug', 'user:id,firstname,lastname'])
            ->where('status', 'published')
            ->orderBy('date', 'desc');

        // 🔍 Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('teaser', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        $articles = $query->paginate(
            $request->get('per_page', 10)
        );

        return response()->json($articles);
    }

    public function public_articles_show(string $slug)
    {
        $article = Article::with(['category:id,name,slug', 'user:id,firstname,lastname'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return response()->json($article);
    }

    public function public_article_categories()
    {
        $categories = ArticleCategory::query()
            ->select('id', 'name', 'slug')
            ->withCount([
                'articles as articles_count' => function ($q) {
                    $q->where('status', 'published');
                }
            ])
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function archive()
    {
        $rows = Article::where('status', 'published')
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Transform to nested structure
        $archive = [];

        foreach ($rows as $row) {
            $archive[$row->year][] = [
                'month' => $row->month,
                'total' => $row->total,
            ];
        }

        return response()->json($archive);
    }
    public function send(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:200',
            'company' => 'nullable|string|max:200',
            'email'   => 'required|email|max:255',
            'contact' => 'required|string|max:30',
            'message' => 'required|string|max:2000',
        ]);

        $client = array_merge($data, [
            'subject'  => 'Contact Us' . (! empty($data['company']) ? ' — ' . $data['company'] : ''),
            'services' => $data['company'] ?? 'Contact Us',
        ]);

        if (! empty($data['company'])) {
            $client['message'] = "Company: {$data['company']}\n\n" . $data['message'];
        }

        $setting = Setting::info();
        $emailRecipients = EmailRecipient::all();

        if ($emailRecipients->isEmpty()) {
            $fallback = $setting->email ?? config('mail.from.address');
            if ($fallback) {
                $emailRecipients = collect([(object) ['name' => 'Admin', 'email' => $fallback]]);
            }
        }

        try {
            Mail::to($client['email'])->send(new InquiryMail($setting, $client));

            foreach ($emailRecipients as $recipient) {
                Mail::to($recipient->email)->send(new InquiryAdminMail($setting, $client, $recipient));
            }
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Failed to send your message. Please try again later.',
            ], 500);
        }

        return response()->json([
            'message' => 'Your message has been sent successfully.',
        ]);
    }
}
