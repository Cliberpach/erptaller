<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleChild extends Model
{
    use HasFactory;

    protected $table ="module_children";

    protected $guarded = [''];

    public function parent() {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function grandchildren() {
        return $this->hasMany(ModuleGrandChild::class, 'module_child_id');
    }
}
