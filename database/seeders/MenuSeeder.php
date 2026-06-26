<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run()
    {
        \App\Models\Menu::insert([
            'name' => 'Main Menu',
            'is_active' => 1,
            'pages_json' => json_encode([
                [
                    'page_id' => 1,
                    'type' => 'page',
                    'label' => 'Home',
                ],
                [
                    'page_id' => 2,
                    'type' => 'page',
                    'label' => 'About Us',
                ],
                [
                    'page_id' => 3,
                    'type' => 'page',
                    'label' => 'Contact Us',
                ],
            ]),
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ]);
    }
}
