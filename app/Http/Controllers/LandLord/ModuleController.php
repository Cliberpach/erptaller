<?php

namespace App\Http\Controllers\LandLord;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Module;
use App\Models\ModuleChild;
use App\Models\ModuleGrandchild;

class ModuleController extends Controller
{
    public function home()
    {
        return view('dashboard');
    }
}
