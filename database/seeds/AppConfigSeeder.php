<?php

use Illuminate\Database\Seeder;
use App\AppConfig;

class AppConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing to prevent duplicates if re-seeded
        AppConfig::where('key', 'version_code')->delete();
        AppConfig::where('key', 'force_update')->delete();
        AppConfig::where('key', 'maintenance_message')->delete();

        AppConfig::create([
            'key' => 'version_code',
            'value' => '15',
            'description' => 'Current App Version Code'
        ]);

        AppConfig::create([
            'key' => 'force_update',
            'value' => 'false',
            'description' => 'Force Update Flag (true/false)'
        ]);

        AppConfig::create([
            'key' => 'maintenance_message',
            'value' => '',
            'description' => 'Maintenance Message (Empty if normal)'
        ]);
    }
}

