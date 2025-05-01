<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // You can remove the roles and permissions seeding, as you have already handled that elsewhere.
        
        // Now, create some services
        Service::create(['title' => 'Furniture Design', 'price' => 1000, 'description' => 'Custom furniture design tailored to your needs.']);
        Service::create(['title' => 'Carpentry', 'price' => 800, 'description' => 'Professional carpentry services for your home and office.']);
        Service::create(['title' => 'Woodworking', 'price' => 600, 'description' => 'Expert woodworking services for unique wooden crafts.']);

        // If you have other seeders for Categories, Products, Orders, etc., call them here
        $this->call([
            CategoriesTableSeeder::class,
            ProductsTableSeeder::class,
            StudentsTableSeeder::class,
            OrdersTableSeeder::class,
            UsersTableSeeder::class,
        ]);
    }
}
