<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleGrandChild extends Model
{
    use HasFactory;

    protected $table ="module_grand_children";

    protected $guarded = [''];

    public function parent() {
        return $this->belongsTo(ModuleChild::class, 'module_child_id');
    }
}
