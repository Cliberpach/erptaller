<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return view('user');
    }

    public function store()
    {
        User::create([
            'name' => 'Jhon Livias',
            'email' => 'jlivias@gmail.com',
            'password' => Hash::make('123123qwe'),
        ]);

        return back();
    }
}
