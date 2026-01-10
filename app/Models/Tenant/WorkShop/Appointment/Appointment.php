<?php

namespace App\Models\Tenant\WorkShop\Appointment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $table = 'appointments';

    protected $fillable = [

        'name',
        'description',

        'start_date',
        'end_date',

        'start_time',
        'end_time',

        'type_calendar',
        'full_day',

        'location',
        'status',

        'customer_id',
        'customer_name',
        'customer_type_document_abbreviation',
        'customer_document_number',
        'vehicle_id',
        'plate',

        // ====== AUDITORÍA ======
        'creator_user_id',
        'editor_user_id',
        'delete_user_id',

        'creator_user_name',
        'editor_user_name',
        'delete_user_name',
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
