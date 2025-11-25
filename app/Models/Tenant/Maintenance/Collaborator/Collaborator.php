<?php

namespace App\Models\Tenant\Maintenance\Collaborator;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collaborator extends Model
{
    use HasFactory;
    protected $table = 'collaborators';

    protected $fillable = [

        'document_type_id',
        'position_id',
        'document_number',
        'full_name',
        'address',
        'phone',
        'work_days',
        'rest_days',
        'monthly_salary',
        'daily_salary',

        'status',

        'creator_user_id',
        'editor_user_id',
        'delete_user_id',

        'delete_user_name',
        'editor_user_name',
        'create_user_name',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->creator_user_id = auth()->id();
                $model->create_user_name = auth()->user()->name;
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
