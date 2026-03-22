<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@pos.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Cashier user
        User::create([
            'name'     => 'Cashier',
            'email'    => 'cashier@pos.com',
            'password' => Hash::make('password'),
            'role'     => 'cashier',
        ]);

        // Categories
        $categories = [
            'Food & Beverage',
            'Snacks',
            'Drinks',
            'Household',
            'Personal Care',
        ];

        foreach ($categories as $name) {
            $cat = Category::create(['name' => $name]);

            // 5 items per category
            for ($i = 1; $i <= 5; $i++) {
                Item::create([
                    'category_id' => $cat->id,
                    'name'        => "{$cat->name} Item {$i}",
                    'price'       => rand(1000, 50000),
                    'stock'       => rand(10, 100),
                    'barcode'     => null,
                ]);
            }
        }
    }
}

