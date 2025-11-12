<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         
            $user                   =   new User();
            $user->name             =   'SUPERADMIN';
            $user->email            =   'admin@gmail.com';
            $user->password         =   Hash::make('12345678');
            $user->password_visible  =   '12345678';
            $user->save();

            $role = Role::where('name', 'admin')->first();
            $user->assignRole($role);
        
    }
}
