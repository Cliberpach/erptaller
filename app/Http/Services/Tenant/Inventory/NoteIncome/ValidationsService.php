<?php

namespace App\Http\Services\Tenant\Inventory\NoteIncome;

use Exception;

class ValidationsService
{

    public function validationStore(array $data){
        $lstNoteIncome  =   json_decode($data['lstNoteIncome']);

        if(count($lstNoteIncome) === 0){
            throw new Exception("EL DETALLE DE LA NOTA DE INGRESO ESTÁ VACÍO");
        }
    }
}
