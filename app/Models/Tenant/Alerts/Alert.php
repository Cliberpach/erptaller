<?php

namespace App\Models\Tenant\Alerts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $guarded = [''];
    protected $table = 'alerts';
    protected $connection   = 'tenant';

    protected $fillable = [
        'name',
        'description',

        'object_id',
        'type_object',

        'notice_date',
        'advance_date',
        'advance_days',
        'status',

        'creator_user_id',
        'editor_user_id',
        'delete_user_id',

        'delete_user_name',
        'editor_user_name',
        'creator_user_name',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->creator_user_id = auth()->id();
                $model->creator_user_name = auth()->user()->name;
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->editor_user_id = auth()->id();
                $model->editor_user_name = auth()->user()->name;
            }
            if ($model->isDirty('status') && $model->status === 'ANULADO') {
                if (auth()->check()) {
                    $model->delete_user_id = auth()->id();
                    $model->delete_user_name = auth()->user()->name;
                }
            }
        });
    }
}
