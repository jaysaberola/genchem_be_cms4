<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Sync CMS page HTML/styles from the static genchemph design folder.
 * Run: php artisan db:seed --class=GenchemphDesignSeeder
 */
class GenchemphDesignSeeder extends Seeder
{
    private string $sourceRoot;

    /** Shared inline CSS from genchemph index.html */
    private string $sharedStyles = '';

    public function run(): void
    {
        $this->sourceRoot = env(
            'GENCHEMPH_SOURCE_PATH',
            'C:\\Users\\saber\\Downloads\\genchemph\\genchemph'
        );

        if (!is_dir($this->sourceRoot)) {
            $this->command?->error("Genchemph source not found: {$this->sourceRoot}");
            return;
        }

        $indexHtml = $this->readFile('index.html');
        $this->sharedStyles = $this->patchSharedStyles($this->extractInlineStyles($indexHtml));

        $map = [
            'home'       => 'index.html',
            'about-us'   => 'about-us.html',
            'products'   => 'products.html',
            'contact-us' => 'contact-us.html',
        ];

        foreach ($map as $slug => $file) {
            $html = $this->readFile($file);
            $contents = $this->extractContentSection($html);
            $contents = $this->normalizeHtml($contents);

            if ($slug === 'home') {
                $contents = $this->patchHomeContent($contents);
            }

            if ($slug === 'contact-us') {
                $contents .= "\n" . $this->extractContactCopyright($html);
                $contents = $this->normalizeHtml($contents);
            }

            $styles = $this->buildPageStyles($slug, $html);

            $this->upsertPage($slug, $contents, $styles);
            $this->command?->info("Synced page: {$slug}");
        }

        $footerHtml = $this->extractFooter($indexHtml);
        $footerHtml = $this->normalizeHtml($footerHtml);
        $this->upsertPage('footer', $footerHtml, $this->sharedStyles);
        $this->command?->info('Synced page: footer');
    }

    private function readFile(string $name): string
    {
        $path = rtrim($this->sourceRoot, '\\/') . DIRECTORY_SEPARATOR . $name;
        if (!File::exists($path)) {
            throw new \RuntimeException("Missing source file: {$path}");
        }
        return File::get($path);
    }

    private function extractInlineStyles(string $html): string
    {
        if (preg_match('/<style>([\s\S]*?)<\/style>/i', $html, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function extractContentSection(string $html): string
    {
        if (preg_match('/<section\s+id="content"[^>]*>([\s\S]*?)<\/section>\s*<!--\s*#content end\s*-->/i', $html, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/<section\s+id="content"[^>]*>([\s\S]*?)<\/section>/i', $html, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function extractFooter(string $html): string
    {
        if (preg_match('/<footer\s+id="footer"[\s\S]*?<\/footer>\s*<!--\s*#footer end\s*-->/i', $html, $m)) {
            return trim($m[0]);
        }
        if (preg_match('/<footer\s+id="footer"[\s\S]*?<\/footer>/i', $html, $m)) {
            return trim($m[0]);
        }
        return '';
    }

    /** Contact Us page uses inline copyright footer (reference contact-us.html). */
    private function extractContactCopyright(string $html): string
    {
        if (preg_match('/<footer\s+id="footer"[\s\S]*?<\/footer>/i', $html, $m)) {
            return trim($m[0]);
        }
        return '';
    }

    private function normalizeHtml(string $html): string
    {
        $api = rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/');

        $replacements = [
            'href="index.html"' => 'href="/public/home"',
            'href="about-us.html"' => 'href="/public/about-us"',
            'href="products.html"' => 'href="/public/products"',
            'href="contact-us.html"' => 'href="/public/contact-us"',
            'href="products.html#tab-1"' => 'href="/public/products#pvc-resins"',
            'href="products.html#tab-2"' => 'href="/public/products#pvc-stabilizers"',
            'href="/public/products#tab-1"' => 'href="/public/products#pvc-resins"',
            'href="/public/products#tab-2"' => 'href="/public/products#pvc-stabilizers"',
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $html);

        // Asset paths → site-relative public folder (works on frontend and in GrapesJS preview)
        $html = preg_replace(
            '/src="images\/logos\/genchemph-logo\.png"/',
            'src="/images/genchemph/logos/genchemph-logo-transparent.png"',
            $html
        );

        $html = preg_replace(
            '/src="images\/icons\/(call|mobile|email|globe|call_red|c-call|c-mobile|c-mail|c-cogs|c-case)\.(png|svg)"/',
            'src="/images/genchemph/icons/$1.$2"',
            $html
        );

        $html = preg_replace(
            '/src="images\/([^"]+)"/',
            'src="/images/genchemph/$1"',
            $html
        );

        $html = preg_replace(
            "/src='images\\/([^']+)'/",
            "src='/images/genchemph/$1'",
            $html
        );

        $html = preg_replace(
            "/url\\(['\"]?images\\/([^'\"\\)]+)['\"]?\\)/",
            "url(/images/genchemph/$1)",
            $html
        );

        $html = str_replace('class="tab-content pt-5"', 'class="tab-content pt-5" id="gc-tab-content"', $html);

        $html = str_replace('id="tab-1"', 'id="pvc-resins" data-genchem-tab="pvc-resins"', $html);
        $html = str_replace('id="tab-2"', 'id="pvc-stabilizers" data-genchem-tab="pvc-stabilizers"', $html);
        $html = str_replace('data-bs-target="#tab-1"', 'data-bs-target="#pvc-resins"', $html);
        $html = str_replace('data-bs-target="#tab-2"', 'data-bs-target="#pvc-stabilizers"', $html);
        $html = str_replace('id="canvas-tab-1"', 'id="canvas-tab-1" data-genchem-tab="pvc-resins"', $html);
        $html = str_replace('id="canvas-tab-2"', 'id="canvas-tab-2" data-genchem-tab="pvc-stabilizers"', $html);

        $html = preg_replace(
            '/<video([^>]*?)>/',
            '<video$1 poster="/images/genchemph/banners/HOMEPAGE_ABOUT_US.png">',
            $html
        );

        $html = preg_replace('/\ssrc=(["\'])data:[^"\']*\1/i', '', $html);
        $html = preg_replace('/\scontrols(=(["\'])[^"\']*\2)?/i', '', $html);

        $html = preg_replace('/<div\s+id="copyrights"[^>]*>/i', '<div id="copyrights" class="p-0 dark bg-dark">', $html);

        $html = preg_replace(
            '/<!--\s*floating logo\s*-->[\s\S]*?<!--\s*floating logo\s*-->[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/i',
            '',
            $html
        );
        $html = preg_replace(
            '/<div\s+class="floating-logo"[^>]*>[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/i',
            '',
            $html
        );

        return $html;
    }

    /** Home-only: intro hero id, product tabs, drone video source (genchemph reference). */
    private function patchHomeContent(string $html): string
    {
        $html = preg_replace(
            '/<div class="position-relative py-6 about-us-low"/',
            '<div id="iz2p" class="position-relative py-6 about-us-low home-intro-hero"',
            $html,
            1
        );

        $html = str_replace(
            'id="canvas-tab-1" data-genchem-tab="pvc-resins"',
            'id="canvas-tab-1" data-genchem-tab="pvc-resins" type="button" onclick="gcSwitchTab(1)"',
            $html
        );
        $html = str_replace(
            'id="canvas-tab-2" data-genchem-tab="pvc-stabilizers"',
            'id="canvas-tab-2" data-genchem-tab="pvc-stabilizers" type="button" onclick="gcSwitchTab(2)"',
            $html
        );

        $html = preg_replace(
            '/<source\s+src=(["\'])[^"\']*video\.mp4\1/i',
            '<source src="/images/genchemph/video.mp4"',
            $html
        );

        $html = str_replace(
            '<i class="me-3 fs-4 bi-shield-fill-check"></i>',
            '<i class="me-3 fs-4 fa-solid fa-shield-halved" aria-hidden="true"></i>',
            $html
        );

        if (!str_contains($html, 'class="video-wrap')) {
            return $html;
        }

        return preg_replace(
            '/<video([^>]*?)>/i',
            '<video$1 muted autoplay loop playsinline preload="auto" poster="/images/genchemph/banners/HOMEPAGE_ABOUT_US.png">',
            $html
        );
    }

    private function buildPageStyles(string $slug, string $html): string
    {
        $styles = $this->sharedStyles;

        if (in_array($slug, ['about-us', 'products', 'contact-us'], true)) {
            $pageStyles = $this->extractInlineStyles($html);
            if ($pageStyles !== '') {
                $styles .= "\n" . $pageStyles;
            }
        }

        return $this->patchSharedStyles($styles);
    }

    private function patchSharedStyles(string $styles): string
    {
        $styles = str_replace(
            '.about-us-low::before {',
            '.about-us-low[style*="background-image"]::before {',
            $styles
        );

        $styles = preg_replace(
            '/\.about-us-high\s*\{\s*position:\s*relative;\s*z-index:\s*2;\s*color:\s*white;\s*\}/i',
            '.about-us-low[style*="background-image"] .about-us-high { position: relative; z-index: 2; color: white; }',
            $styles
        );

        $styles .= "\n.about-us-page .about-us-low::before { display: none !important; }\n";
        $styles .= ".about-us-page .about-us-high { color: inherit; }\n";
        $styles .= ".about-us-page .about-us-high .text-dark, .about-us-page .text-dark { color: #333333 !important; }\n";

        return $styles;
    }

    private function upsertPage(string $slug, string $contents, string $styles): void
    {
        $page = Page::where('slug', $slug)->first();

        if (!$page) {
            $this->command?->warn("Page not found for slug: {$slug} — skipping create");
            return;
        }

        $page->update([
            'contents' => $contents,
            'styles'   => $styles,
            'json'     => null,
            'status'   => 'PUBLISHED',
        ]);
    }
}
