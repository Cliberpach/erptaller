<?php

namespace App\Http\Services\Landlord\WorkShop\Years;

use App\Models\Landlord\Year;

class YearService
{
    public function store(array $datos): Year
    {
        $year                 =     new Year();
        $year->description    =     mb_strtoupper($datos['description'], 'UTF-8');
        $year->save();

        return $year;
    }

    public function getYear(int $id): Year
    {
        $year = Year::findOrFail($id);

        return $year;
    }

    public function update(int $id, array $datos): Year
    {
        $datos = collect($datos)->mapWithKeys(function ($value, $key) {
            if (str_ends_with($key, '_edit')) {
                $key = str_replace('_edit', '', $key);
            }
            return [$key => $value];
        })->toArray();


        $year                 =   Year::findOrFail($id);
        $year->description    =   mb_strtoupper($datos['description'], 'UTF-8');
        $year->update();

        return $year;
    }

    public function destroy(int $id): Year
    {
        $year = Year::findOrFail($id);
        $year->status = 'INACTIVE';
        $year->update();

        return $year;
    }
}
