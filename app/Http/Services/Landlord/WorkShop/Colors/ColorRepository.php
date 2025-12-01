<?php

namespace App\Http\Services\Landlord\WorkShop\Colors;

use App\Models\Landlord\Color;

class ColorRepository
{
    public function insertColor(array $data): Color
    {
        $color              =   new Color();
        $color->description =   mb_strtoupper($data['description'], 'UTF-8');
        $color->codigo      =   $data['codigo'] ?? null;
        $color->save();

        return $color;
    }

    public function findColor(string $description): ?Color
    {
        return Color::where('description', $description)->where('status', 'ACTIVE')->first();
    }
}
