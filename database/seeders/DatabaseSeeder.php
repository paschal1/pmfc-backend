<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Service; 
use App\Models\Permission;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory(100)->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
 

// Create roles
    $adminRole = Role::create(['name' => 'Admin']);
    $trainerRole = Role::create(['name' => 'Trainer']);
    $traineeRole = Role::create(['name' => 'Trainee']);
    $customerRole = Role::create(['name' => 'Customer']);

    // Create permissions
    $permissions = [
        'manage_users',
        'view_products',
        'manage_orders',
        'manage_training_programs',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    // Assign permissions to roles
    $adminRole->permissions()->sync(Permission::pluck('id')); // Admin gets all permissions
    $trainerRole->permissions()->sync(Permission::where('name', 'manage_training_programs')->pluck('id'));
    $customerRole->permissions()->sync(Permission::where('name', 'view_products')->pluck('id'));

    Service::create(['title' => 'Furniture Design', 'price' => 1000, 'description' => 'Custom furniture design tailored to your needs.']);
    Service::create(['title' => 'Carpentry', 'price' => 800, 'description' => 'Professional carpentry services for your home and office.']);
    Service::create(['title' => 'Woodworking', 'price' => 600, 'description' => 'Expert woodworking services for unique wooden crafts.']);
    

        $this->call([
            CategoriesTableSeeder::class,
            ProductsTableSeeder::class,
            StudentsTableSeeder::class,
            OrdersTableSeeder::class,
            UsersTableSeeder::class,
        ]);
    }
}
