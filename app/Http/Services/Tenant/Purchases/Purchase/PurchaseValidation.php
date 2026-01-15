<?php

namespace App\Http\Services\Tenant\Purchases\Purchase;

use App\Http\Services\Tenant\Cash\PettyCashBook\PettyCashBookService;
use Exception;
use Illuminate\Support\Facades\Auth;

class PurchaseValidation
{

    public function validationStore(array $data): array
    {
        $lstPurchaseDocument    =   json_decode($data['lstPurchaseDocument']);

        if (count($lstPurchaseDocument) === 0) {
            throw new Exception("EL DETALLE DE LA COMPRA ESTÁ VACÍO");
        }

        $collect_detail =   collect($lstPurchaseDocument);
        $quantity_total =   $collect_detail->sum('quantity');

        if ($quantity_total == 0) {
            throw new Exception("LA CANTIDAD TOTAL DE LA COMPRA ES CERO");
        }

        $data['lst_purchase']   =   $lstPurchaseDocument;
        return $data;
    }

}
