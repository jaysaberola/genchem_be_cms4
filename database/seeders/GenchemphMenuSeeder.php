<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Align active menu with genchemph header (Products sub-links).
 * Run: php artisan db:seed --class=GenchemphMenuSeeder
 */
class GenchemphMenuSeeder extends Seeder
{
    public function run(): void
    {
        $pages = Page::whereIn('slug', ['home', 'about-us', 'products', 'contact-us'])
            ->pluck('id', 'slug');

        $items = [
            [
                'page_id'  => $pages['home'] ?? null,
                'type'     => 'page',
                'label'    => 'Home',
                'target'   => '/public/home',
                'children' => [],
            ],
            [
                'page_id'  => $pages['about-us'] ?? null,
                'type'     => 'page',
                'label'    => 'About Us',
                'target'   => '/public/about-us',
                'children' => [],
            ],
            [
                'page_id'  => $pages['products'] ?? null,
                'type'     => 'page',
                'label'    => 'Products',
                'target'   => '/public/products',
                'children' => [
                    [
                        'page_id'  => $pages['products'] ?? null,
                        'type'     => 'page',
                        'label'    => 'PVC Resins',
                        'target'   => '/public/products#pvc-resins',
                        'children' => [],
                    ],
                    [
                        'page_id'  => $pages['products'] ?? null,
                        'type'     => 'page',
                        'label'    => 'PVC Stabilizers',
                        'target'   => '/public/products#pvc-stabilizers',
                        'children' => [],
                    ],
                ],
            ],
            [
                'page_id'  => $pages['contact-us'] ?? null,
                'type'     => 'page',
                'label'    => 'Contact Us',
                'target'   => '/public/contact-us',
                'children' => [],
            ],
        ];

        $menu = Menu::where('is_active', true)->first() ?? Menu::query()->first();

        if (!$menu) {
            $this->command?->warn('No menu found — skipping GenchemphMenuSeeder');
            return;
        }

        $menu->update([
            'pages_json' => json_encode($items),
            'is_active'  => true,
        ]);

        $this->command?->info('Synced genchemph menu structure.');
    }
}
