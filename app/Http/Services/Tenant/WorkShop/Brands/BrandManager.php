<?php

namespace App\Http\Services\Tenant\WorkShop\Brands;

use App\Almacenes\Producto;
use App\Models\Tenant\WorkShop\Brand;
use App\Models\Tenant\WorkShop\Color;
use Illuminate\Support\Collection;

class BrandManager
{
    private BrandService $s_marca;

    public function __construct(){
        $this->s_marca   =   new BrandService();
    }

    public function store(array $datos):Brand{
        return $this->s_marca->store($datos);
    }

    public function getMarca(int $id):Brand{
        return $this->s_marca->getMarca($id);
    }

    public function update (int $id,array $datos):Brand{
        return $this->s_marca->update($id,$datos);
    }

    public function destroy(int $id):Brand{
        return $this->s_marca->destroy($id);
    }

}
