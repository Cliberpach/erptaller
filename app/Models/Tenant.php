<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $guarded = [];

    public static function booted()
    {
        static::creating(fn (Tenant $tenant) => $tenant->createDatabase($tenant));
        static::created(fn (Tenant $tenant) => $tenant->runMigrationsSeeders($tenant));
    }


    public function createDatabase($tenant)
    {
        $database_name = "tenancy_".$tenant->domain;
        $database = Str::of($database_name)->replace('.','_')->lower()->__toString();

        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
        $db = DB::select($query,[$database]);
        if(empty($db)){
            DB::connection('tenant')->statement("CREATE DATABASE {$database};");
            $tenant->database = $database;
        }
    }

    public function runMigrationsSeeders($tenant)
    {
        $tenant->refresh();
        Artisan::call('tenants:artisan',[
            'artisanCommand'=>'migrate --path=database/migrations/tenant --database=tenant --seed --force',
            '--tenant'=> "{$tenant->id}"
        ]);
    }
}
