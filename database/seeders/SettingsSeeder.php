<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Default User Settings:');
        
        $settings = [
            ['key' => 'locale', 'value' => 'id_ID', 'type' => 'string', 'description' => 'Bahasa'],
            ['key' => 'currency', 'value' => 'IDR', 'type' => 'string', 'description' => 'Mata uang default'],
            ['key' => 'currency_symbol', 'value' => 'Rp', 'type' => 'string', 'description' => 'Simbol mata uang'],
            ['key' => 'date_format', 'value' => 'DD/MM/YYYY', 'type' => 'string', 'description' => 'Format tanggal'],
            ['key' => 'start_of_week', 'value' => 'monday', 'type' => 'string', 'description' => 'Awal minggu'],
            ['key' => 'theme', 'value' => 'light', 'type' => 'string', 'description' => 'Tema tampilan'],
            ['key' => 'notifications_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Notifikasi aktif'],
            ['key' => 'reminder_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Reminder aktif'],
            ['key' => 'reminder_time', 'value' => '09:00', 'type' => 'string', 'description' => 'Waktu reminder'],
            ['key' => 'default_account_id', 'value' => '', 'type' => 'integer', 'description' => 'Akun default'],
            ['key' => 'quick_add_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Quick add aktif'],
        ];

        foreach ($settings as $setting) {
            $this->command->line("  - {$setting['key']}: {$setting['value']} ({$setting['description']})");
        }
    }

    public static function getDefaults(): array
    {
        return [
            ['key' => 'locale', 'value' => 'id_ID', 'type' => 'string'],
            ['key' => 'currency', 'value' => 'IDR', 'type' => 'string'],
            ['key' => 'currency_symbol', 'value' => 'Rp', 'type' => 'string'],
            ['key' => 'date_format', 'value' => 'DD/MM/YYYY', 'type' => 'string'],
            ['key' => 'start_of_week', 'value' => 'monday', 'type' => 'string'],
            ['key' => 'theme', 'value' => 'light', 'type' => 'string'],
            ['key' => 'notifications_enabled', 'value' => 'true', 'type' => 'boolean'],
            ['key' => 'reminder_enabled', 'value' => 'true', 'type' => 'boolean'],
            ['key' => 'reminder_time', 'value' => '09:00', 'type' => 'string'],
            ['key' => 'quick_add_enabled', 'value' => 'true', 'type' => 'boolean'],
        ];
    }
}
