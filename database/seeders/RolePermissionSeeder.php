<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        $permissions = [
            // User management
            ['name' => 'view users', 'group' => 'users'],
            ['name' => 'create users', 'group' => 'users'],
            ['name' => 'edit users', 'group' => 'users'],
            ['name' => 'delete users', 'group' => 'users'],
            ['name' => 'manage users', 'group' => 'users'],
            
            // Role management
            ['name' => 'view roles', 'group' => 'roles'],
            ['name' => 'create roles', 'group' => 'roles'],
            ['name' => 'edit roles', 'group' => 'roles'],
            ['name' => 'delete roles', 'group' => 'roles'],
            ['name' => 'manage roles', 'group' => 'roles'],
            
            // Class management
            ['name' => 'view classes', 'group' => 'classes'],
            ['name' => 'create classes', 'group' => 'classes'],
            ['name' => 'edit classes', 'group' => 'classes'],
            ['name' => 'delete classes', 'group' => 'classes'],
            ['name' => 'manage classes', 'group' => 'classes'],
            
            // Attendance
            ['name' => 'view attendance', 'group' => 'attendance'],
            ['name' => 'take attendance', 'group' => 'attendance'],
            ['name' => 'edit attendance', 'group' => 'attendance'],
            ['name' => 'manage attendance', 'group' => 'attendance'],
            
            // Library
            ['name' => 'view library', 'group' => 'library'],
            ['name' => 'manage library', 'group' => 'library'],
            ['name' => 'borrow books', 'group' => 'library'],
            ['name' => 'return books', 'group' => 'library'],
            
            // Reports
            ['name' => 'view reports', 'group' => 'reports'],
            ['name' => 'generate reports', 'group' => 'reports'],
            ['name' => 'export reports', 'group' => 'reports'],
            
            // System
            ['name' => 'access dashboard', 'group' => 'system'],
            ['name' => 'manage settings', 'group' => 'system'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create roles
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $teacher = Role::create(['name' => 'teacher']);
        $teacher->givePermissionTo([
            'view users',
            'view classes',
            'view attendance',
            'take attendance',
            'view library',
            'view reports',
            'access dashboard'
        ]);

        $student = Role::create(['name' => 'student']);
        $student->givePermissionTo([
            'view attendance',
            'view library',
            'borrow books',
            'access dashboard'
        ]);

         // Create default users
        $superAdminUser = User::create([
            'name' => 'Bereket Tsegay',
            'email' => 'berie@bntech.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
}