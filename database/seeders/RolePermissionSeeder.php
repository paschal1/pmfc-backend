<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define all permissions
        $permissions = [
            // Admin / General permissions
            'view dashboard',
            'manage products',
            'manage orders',
            'manage customers',
            'manage categories',
            'manage payments',
            'manage quotes',
            'manage blogs',
            'manage testimonials',
            'manage projects',
            'manage services',

            // Training Center permissions
            'view training dashboard',
            'manage training programs',
            'manage enrollments',
            'manage course content',

            // Customer-specific
            'view products',
            'place orders',
            'track orders',
            'view order history',
            'request quotes',
            'book services',
            'view services',
            'submit testimonials',
            'view blogs',
            'view training programs',
            'enroll in training',
            'view training schedule',
            'download training materials',
            'update profile',
            'view notifications',
            'send support requests',
        ];

        // 2. Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'sanctum',
            ]);
        }

        // 3. Define base role permissions
        $customerPermissions = [
            'view dashboard',
            'view products',
            'place orders',
            'track orders',
            'view order history',
            'request quotes',
            'book services',
            'view services',
            'submit testimonials',
            'view blogs',
            'view training programs',
            'enroll in training',
            'view training schedule',
            'download training materials',
            'update profile',
            'view notifications',
            'send support requests',
        ];

        $studentPermissions = [
            'view training programs',
            'enroll in training',
            'view training schedule',
            'download training materials',
        ];

        $managerPermissions = [
            'view dashboard',
            'manage orders',
            'manage customers',
            'manage services',
            'manage projects',
        ];

        $trainerPermissions = [
            'view training dashboard',
            'manage training programs',
            'manage enrollments',
            'manage course content',
        ];

        // 4. Create roles
        $roles = [
            'admin' => [], // All permissions will be added after
            'customer' => $customerPermissions,
            'student' => $studentPermissions,
            'manager' => $managerPermissions,
            'trainer' => $trainerPermissions,
        ];

        // Create all roles and assign base permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'sanctum']);
            $role->syncPermissions($rolePermissions);
            Log::info("Role '{$roleName}' created with permissions.");
        }

        // 5. Assign all permissions to the admin role (including customer, student, manager, trainer)
        $adminRole = Role::where('name', 'admin')->first();
        $adminRole->syncPermissions(Permission::all());

        // 6. Assign Admin role to a specific user
        $adminEmail = 'info@princem-fc.com'; // Replace with your admin email
        $adminUser = User::where('email', $adminEmail)->first();

        if ($adminUser) {
            if (!$adminUser->hasRole('admin')) {
                $adminUser->assignRole('admin');
                Log::info("Admin role assigned to user: {$adminEmail}");
            } else {
                Log::info("User {$adminEmail} already has admin role.");
            }
        } else {
            Log::warning("Admin user with email {$adminEmail} not found.");
        }

        // 7. Optional: assign 'customer' role by default during user registration
        User::creating(function ($user) {
            $user->assignRole('customer');
            Log::info("Default 'Customer' role assigned to new user: {$user->email}");
        });
    }
}
