<?php

namespace App\Models\Tenant\WorkShop;

use App\Models\Landlord\Brand;
use App\Models\Landlord\Color;
use App\Models\Landlord\ModelV;
use App\Models\Landlord\Year;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;
    protected $table = 'vehicles';

    protected $fillable = [
        'plate',
        'name',
        'customer_id',
        'brand_id',
        'model_id',
        'year_id',
        'color_id',
        'observation',
        'vin',
        'serie',
        'status',
        'creator_user_id',
        'editor_user_id',
        'delete_user_id',
        'delete_user_name',
        'editor_user_name',
        'create_user_name'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function model()
    {
        return $this->belongsTo(ModelV::class, 'model_id');
    }

    public function year()
    {
        return $this->belongsTo(Year::class, 'year_id');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vehicle) {
            if (auth()->check()) {
                $vehicle->creator_user_id = auth()->id();
                $vehicle->create_user_name = auth()->user()->name;
            }

            if (empty($vehicle->name)) {
                $vehicle->name = $vehicle->plate;
            }

            if (empty($vehicle->status)) {
                $vehicle->status = 'ACTIVO';
            }
        });

        static::updating(function ($vehicle) {
            if (auth()->check()) {
                $vehicle->editor_user_id = auth()->id();
                $vehicle->editor_user_name = auth()->user()->name;
            }
            if ($vehicle->isDirty('status') && $vehicle->status === 'ANULADO') {
                if (auth()->check()) {
                    $vehicle->delete_user_id = auth()->id();
                    $vehicle->delete_user_name = auth()->user()->name;
                }
            }
        });
    }
}
