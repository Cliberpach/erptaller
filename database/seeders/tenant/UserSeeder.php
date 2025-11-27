<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\Maintenance\Collaborator\Collaborator;
use App\Models\User;
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
        $collaborator                   =   new Collaborator();
        $collaborator->full_name        =   'LUIS DANIEL ALVA LUJAN';
        $collaborator->document_type_id =   39;
        $collaborator->document_number  =   77412431;
        $collaborator->address          =   'AV HUSARES 123';
        $collaborator->phone            =   '989392912';
        $collaborator->work_days        =   30;
        $collaborator->rest_days        =   20;
        $collaborator->monthly_salary   =   12000;
        $collaborator->daily_salary     =   400;
        $collaborator->position_id      =   1;
        $collaborator->save();

        $user                       =   new User();
        $user->name                 =   'SUPERADMIN';
        $user->email                =   'admin@gmail.com';
        $user->password             =   Hash::make('123456789');
        $user->password_visible     =   '123456789';
        $user->collaborator_id      =   $collaborator->id;
        $user->save();

        $role = Role::where('name', 'admin')->first();
        $user->assignRole($role);
    }
}
