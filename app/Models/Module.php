<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $table = "modules";

    protected $guarded = [''];

    public function children() {
        return $this->hasMany(ModuleChild::class, 'module_id');
    }
}
