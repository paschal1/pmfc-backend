<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method will create 50 sample orders using the Order factory.
     */
    public function run(): void
    {
        // Generate 50 sample orders
        Order::factory()->count(50)->create();
    }
}
