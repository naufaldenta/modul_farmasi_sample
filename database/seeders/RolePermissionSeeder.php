<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Define roles
        $roles = [
            'admin',
            'petugas-gudang',
            'apoteker',
            'kepala-instalasi'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create default users
        $users = [
            [
                'name' => 'Admin System',
                'email' => 'admin@simrs.com',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ],
            [
                'name' => 'Petugas Gudang',
                'email' => 'gudang@simrs.com',
                'password' => Hash::make('password'),
                'role' => 'petugas-gudang'
            ],
            [
                'name' => 'Apoteker',
                'email' => 'apoteker@simrs.com',
                'password' => Hash::make('password'),
                'role' => 'apoteker'
            ],
            [
                'name' => 'Kepala Instalasi',
                'email' => 'kepala@simrs.com',
                'password' => Hash::make('password'),
                'role' => 'kepala-instalasi'
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            
            $user->assignRole($role);
        }
    }
}
