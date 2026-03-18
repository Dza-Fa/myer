<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Account Templates:');
        
        $templates = [
            ['name' => 'Cash', 'type' => 'cash', 'currency' => 'IDR', 'icon' => 'wallet'],
            ['name' => 'Bank BCA', 'type' => 'bank', 'currency' => 'IDR', 'icon' => 'building'],
            ['name' => 'Bank Mandiri', 'type' => 'bank', 'currency' => 'IDR', 'icon' => 'building'],
            ['name' => 'Bank BRI', 'type' => 'bank', 'currency' => 'IDR', 'icon' => 'building'],
            ['name' => 'GoPay', 'type' => 'ewallet', 'currency' => 'IDR', 'icon' => 'smartphone'],
            ['name' => 'OVO', 'type' => 'ewallet', 'currency' => 'IDR', 'icon' => 'smartphone'],
            ['name' => 'Dana', 'type' => 'ewallet', 'currency' => 'IDR', 'icon' => 'smartphone'],
            ['name' => 'ShopeePay', 'type' => 'ewallet', 'currency' => 'IDR', 'icon' => 'smartphone'],
            ['name' => 'Reksadana', 'type' => 'investment', 'currency' => 'IDR', 'icon' => 'trending-up'],
            ['name' => 'Saham', 'type' => 'investment', 'currency' => 'IDR', 'icon' => 'trending-up'],
            ['name' => 'Kartu Kredit', 'type' => 'credit', 'currency' => 'IDR', 'icon' => 'credit-card'],
        ];

        foreach ($templates as $account) {
            $this->command->line("  - {$account['name']} ({$account['type']})");
        }
    }

    public static function getTemplate(): array
    {
        return [
            ['name' => 'Cash', 'type' => 'cash', 'currency' => 'IDR', 'initial_balance' => 0],
            ['name' => 'Bank', 'type' => 'bank', 'currency' => 'IDR', 'initial_balance' => 0],
            ['name' => 'E-Wallet', 'type' => 'ewallet', 'currency' => 'IDR', 'initial_balance' => 0],
        ];
    }
}
