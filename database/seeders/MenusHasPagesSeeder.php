<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MenusHasPagesSeeder extends Seeder
{
    public function run()
    {
        \App\Models\MenusHasPages::insert([
            [
                'menu_id' => 1,
                'page_id' => 1,
                'parent_id' => 0,
                'page_order' => 1,
                'uri' => '',
                'label' => 'Home',
                'target' => '',
                'type' => 'page',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ],
            [
                'menu_id' => 1,
                'page_id' => 2,
                'parent_id' => 0,
                'page_order' => 2,
                'uri' => '',
                'label' => 'About Us',
                'target' => '',
                'type' => 'page',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ],
            [
                'menu_id' => 1,
                'page_id' => 3,
                'parent_id' => 0,
                'page_order' => 3,
                'uri' => '',
                'label' => 'Contact Us',
                'target' => '',
                'type' => 'page',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ],
        ]);
    }
}
