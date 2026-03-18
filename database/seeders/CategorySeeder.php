<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $incomeCategories = [
            ['name' => 'Salary', 'icon' => 'briefcase', 'color' => '#22C55E', 'position' => 1],
            ['name' => 'Freelance', 'icon' => 'laptop', 'color' => '#10B981', 'position' => 2],
            ['name' => 'Business', 'icon' => 'store', 'color' => '#14B8A6', 'position' => 3],
            ['name' => 'Investments', 'icon' => 'trending-up', 'color' => '#0D9488', 'position' => 4],
            ['name' => 'Rental Income', 'icon' => 'home', 'color' => '#06B6D4', 'position' => 5],
            ['name' => 'Dividends', 'icon' => 'percent', 'color' => '#0EA5E9', 'position' => 6],
            ['name' => 'Gifts', 'icon' => 'gift', 'color' => '#3B82F6', 'position' => 7],
            ['name' => 'Refunds', 'icon' => 'rotate-left', 'color' => '#6366F1', 'position' => 8],
            ['name' => 'Other Income', 'icon' => 'plus-circle', 'color' => '#8B5CF6', 'position' => 9],
        ];

        $expenseCategories = [
            ['name' => 'Food & Dining', 'icon' => 'coffee', 'color' => '#EF4444', 'position' => 1],
            ['name' => 'Transportation', 'icon' => 'car', 'color' => '#F97316', 'position' => 2],
            ['name' => 'Shopping', 'icon' => 'shopping-bag', 'color' => '#F59E0B', 'position' => 3],
            ['name' => 'Entertainment', 'icon' => 'film', 'color' => '#EAB308', 'position' => 4],
            ['name' => 'Bills & Utilities', 'icon' => 'zap', 'color' => '#84CC16', 'position' => 5],
            ['name' => 'Health', 'icon' => 'heart', 'color' => '#22C55E', 'position' => 6],
            ['name' => 'Education', 'icon' => 'book', 'color' => '#10B981', 'position' => 7],
            ['name' => 'Insurance', 'icon' => 'shield', 'color' => '#14B8A6', 'position' => 8],
            ['name' => 'Subscriptions', 'icon' => 'repeat', 'color' => '#06B6D4', 'position' => 9],
            ['name' => 'Gifts & Donations', 'icon' => 'gift', 'color' => '#0EA5E9', 'position' => 10],
            ['name' => 'Travel', 'icon' => 'map-pin', 'color' => '#3B82F6', 'position' => 11],
            ['name' => 'Groceries', 'icon' => 'shopping-cart', 'color' => '#6366F1', 'position' => 12],
            ['name' => 'Personal Care', 'icon' => 'user', 'color' => '#8B5CF6', 'position' => 13],
            ['name' => 'Home & Living', 'icon' => 'home', 'color' => '#A855F7', 'position' => 14],
            ['name' => 'Investment', 'icon' => 'trending-up', 'color' => '#D946EF', 'position' => 15],
            ['name' => 'Taxes', 'icon' => 'file-text', 'color' => '#EC4899', 'position' => 16],
            ['name' => 'Other Expense', 'icon' => 'minus-circle', 'color' => '#F43F5E', 'position' => 17],
        ];

        // Simpan template ke tabel category_templates (belum dibuat)
        // Ini akan digunakan saat user baru register
        
        // Untuk saat ini, tampilkan di console
        $this->command->info('Income Categories Template:');
        foreach ($incomeCategories as $cat) {
            $this->command->line("  - {$cat['name']} ({$cat['icon']})");
        }

        $this->command->info('Expense Categories Template:');
        foreach ($expenseCategories as $cat) {
            $this->command->line("  - {$cat['name']} ({$cat['icon']})");
        }
    }

    public static function getIncomeTemplate(): array
    {
        return [
            ['name' => 'Salary', 'icon' => 'briefcase', 'color' => '#22C55E', 'position' => 1],
            ['name' => 'Freelance', 'icon' => 'laptop', 'color' => '#10B981', 'position' => 2],
            ['name' => 'Business', 'icon' => 'store', 'color' => '#14B8A6', 'position' => 3],
            ['name' => 'Investments', 'icon' => 'trending-up', 'color' => '#0D9488', 'position' => 4],
            ['name' => 'Rental Income', 'icon' => 'home', 'color' => '#06B6D4', 'position' => 5],
            ['name' => 'Dividends', 'icon' => 'percent', 'color' => '#0EA5E9', 'position' => 6],
            ['name' => 'Gifts', 'icon' => 'gift', 'color' => '#3B82F6', 'position' => 7],
            ['name' => 'Refunds', 'icon' => 'rotate-left', 'color' => '#6366F1', 'position' => 8],
            ['name' => 'Other Income', 'icon' => 'plus-circle', 'color' => '#8B5CF6', 'position' => 9],
        ];
    }

    public static function getExpenseTemplate(): array
    {
        return [
            ['name' => 'Food & Dining', 'icon' => 'coffee', 'color' => '#EF4444', 'position' => 1],
            ['name' => 'Transportation', 'icon' => 'car', 'color' => '#F97316', 'position' => 2],
            ['name' => 'Shopping', 'icon' => 'shopping-bag', 'color' => '#F59E0B', 'position' => 3],
            ['name' => 'Entertainment', 'icon' => 'film', 'color' => '#EAB308', 'position' => 4],
            ['name' => 'Bills & Utilities', 'icon' => 'zap', 'color' => '#84CC16', 'position' => 5],
            ['name' => 'Health', 'icon' => 'heart', 'color' => '#22C55E', 'position' => 6],
            ['name' => 'Education', 'icon' => 'book', 'color' => '#10B981', 'position' => 7],
            ['name' => 'Insurance', 'icon' => 'shield', 'color' => '#14B8A6', 'position' => 8],
            ['name' => 'Subscriptions', 'icon' => 'repeat', 'color' => '#06B6D4', 'position' => 9],
            ['name' => 'Gifts & Donations', 'icon' => 'gift', 'color' => '#0EA5E9', 'position' => 10],
            ['name' => 'Travel', 'icon' => 'map-pin', 'color' => '#3B82F6', 'position' => 11],
            ['name' => 'Groceries', 'icon' => 'shopping-cart', 'color' => '#6366F1', 'position' => 12],
            ['name' => 'Personal Care', 'icon' => 'user', 'color' => '#8B5CF6', 'position' => 13],
            ['name' => 'Home & Living', 'icon' => 'home', 'color' => '#A855F7', 'position' => 14],
            ['name' => 'Investment', 'icon' => 'trending-up', 'color' => '#D946EF', 'position' => 15],
            ['name' => 'Taxes', 'icon' => 'file-text', 'color' => '#EC4899', 'position' => 16],
            ['name' => 'Other Expense', 'icon' => 'minus-circle', 'color' => '#F43F5E', 'position' => 17],
        ];
    }
}
