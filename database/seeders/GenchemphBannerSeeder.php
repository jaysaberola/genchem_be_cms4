<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Banner;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Assign genchemph banner images to home + subpages.
 * Run: php artisan db:seed --class=GenchemphBannerSeeder
 */
class GenchemphBannerSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'home'       => ['type' => 'main_banner', 'name' => 'Home Banner',       'image' => 'banners/home_header.png'],
            'about-us'   => ['type' => 'sub_banner',  'name' => 'About Us Banner',   'image' => 'banners/about_us.png'],
            'products'   => ['type' => 'sub_banner',  'name' => 'Products Banner',   'image' => 'banners/our_products.png'],
            'contact-us' => ['type' => 'sub_banner',  'name' => 'Contact Us Banner', 'image' => 'banners/contact_us.png'],
        ];

        foreach ($map as $slug => $cfg) {
            $page = Page::where('slug', $slug)->first();
            if (!$page) {
                $this->command?->warn("Page missing: {$slug}");
                continue;
            }

            $album = ($page->album_id && (int) $page->album_id !== 0)
                ? Album::find($page->album_id)
                : null;

            if (!$album) {
                $album = Album::create([
                    'name'           => $cfg['name'],
                    'transition_in'  => 1,
                    'transition_out' => 2,
                    'transition'     => 6,
                    'type'           => $cfg['type'],
                    'banner_type'    => 'image',
                    'user_id'        => 1,
                ]);
                $page->update(['album_id' => $album->id]);
            } else {
                $album->update([
                    'name'        => $cfg['name'],
                    'type'        => $cfg['type'],
                    'banner_type' => 'image',
                ]);
            }

            Banner::updateOrCreate(
                ['album_id' => $album->id, 'order' => 1],
                [
                    'title'       => $page->name,
                    'description' => '',
                    'alt'         => $page->name,
                    // Site-relative path — survives zip/deploy; frontend resolves via /images/
                    'image_path'  => "/images/genchemph/{$cfg['image']}",
                    'button_text' => '',
                    'url'         => '',
                    'user_id'     => 1,
                ]
            );

            $this->command?->info("Banner synced: {$slug}");
        }
    }
}
