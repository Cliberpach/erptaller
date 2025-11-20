<?php

namespace App\Models\Tenant\WorkShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $table = 'services';

    protected $fillable = [
        'name',
        'description',
        'price',
        'status',
        'creator_user_id',
        'editor_user_id',
        'delete_user_id',
        'delete_user_name',
        'editor_user_name',
        'create_user_name'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (auth()->check()) {
                $service->creator_user_id = auth()->id();
                $service->create_user_name = auth()->user()->name;
            }

            if (empty($service->status)) {
                $service->status = 'ACTIVE';
            }
        });

        static::updating(function ($service) {
            if (auth()->check()) {
                $service->editor_user_id = auth()->id();
                $service->editor_user_name = auth()->user()->name;
            }
            if ($service->isDirty('status') && $service->status === 'INACTIVE') {
                if (auth()->check()) {
                    $service->delete_user_id = auth()->id();
                    $service->delete_user_name = auth()->user()->name;
                }
            }
        });
    }
}
