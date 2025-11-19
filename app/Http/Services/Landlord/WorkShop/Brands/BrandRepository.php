<?php

namespace App\Http\Services\Landlord\WorkShop\Brands;

use App\Models\Landlord\Brand;

class BrandRepository
{
    public function insertBrand(array $data):Brand
    {
        $brand              =   new Brand();
        $brand->description =   mb_strtoupper($data['description'], 'UTF-8');
        $brand->save();

        return $brand;
    }

    public function findBrand(string $description):?Brand{
        return Brand::where('description',$description)->where('status','ACTIVE')->first();
    }
}
