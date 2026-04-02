<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@kslang.app',
            'login_id' => 'admin',
            'password' => bcrypt('R@W9tn!232323'),
        ]);

        Page::create([
            'slug' => 'privacy',
            'title' => 'Privacy Policy',
            'content' => '',
        ]);

        Page::create([
            'slug' => 'terms',
            'title' => 'Terms of Service',
            'content' => '',
        ]);

        AppSetting::create(['key' => 'min_version', 'value' => '1.0.0']);
        AppSetting::create(['key' => 'latest_version', 'value' => '1.0.0']);
        AppSetting::create(['key' => 'play_store_url', 'value' => '']);
    }
}
