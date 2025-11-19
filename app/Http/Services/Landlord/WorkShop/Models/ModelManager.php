<?php

namespace App\Http\Services\Landlord\WorkShop\Models;

use App\Models\Landlord\ModelV;

class ModelManager
{
    private ModelService $s_model;

    public function __construct(){
        $this->s_model   =   new ModelService();
    }

    public function store(array $datos):ModelV{
        return $this->s_model->store($datos);
    }

    public function getModel(int $id):ModelV{
        return $this->s_model->getModel($id);
    }

    public function update (int $id,array $datos):ModelV{
        return $this->s_model->update($id,$datos);
    }

    public function destroy(int $id):ModelV{
        return $this->s_model->destroy($id);
    }

}
