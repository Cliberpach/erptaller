<?php

namespace App\Http\Services\Landlord\WorkShop\Years;

use App\Models\Landlord\Year;

class YearManager
{
    private YearService $s_year;

    public function __construct(){
        $this->s_year   =   new YearService();
    }

    public function store(array $datos):Year{
        return $this->s_year->store($datos);
    }

    public function getYear(int $id):Year{
        return $this->s_year->getYear($id);
    }

    public function update (int $id,array $datos):Year{
        return $this->s_year->update($id,$datos);
    }

    public function destroy(int $id):Year{
        return $this->s_year->destroy($id);
    }

}
