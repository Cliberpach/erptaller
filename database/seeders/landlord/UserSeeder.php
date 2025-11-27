<?php

namespace Database\Seeders\landlord;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name'              =>  'admin',
            'email'             =>  'admin@gmail.com',
            'password'          =>  Hash::make('123456789'),
            'password_visible'  =>  '123456789'
        ]);


        $role = Role::where('name', 'admin')->first();
        $user->assignRole($role);
    }
}
