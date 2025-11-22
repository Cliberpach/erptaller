<?php

namespace App\Http\Services\Tenant\WorkShop\Quotes;

use App\Models\Tenant\WorkShop\Quote\Quote;
use App\Models\Tenant\WorkShop\Quote\QuoteProduct;
use App\Models\Tenant\WorkShop\Service;
use App\Models\Tenant\WorkShop\Vehicle;

class QuoteRepository
{

    private QuoteDto $s_dto;

    public function __construct(){
        $this->s_dto    =   new QuoteDto();
    }

    public function insertQuote(array $dto): Quote
    {
        return Quote::create($dto);
    }

    public function insertQuoteDetail(array $lst_products,array $lst_services, Quote $quote)
    {
        foreach ($lst_products as $item) {

            $dto_item = $this->s_dto->getDtoQuoteProduct($item, $quote);
            QuoteProduct::create($dto_item);
        }

    }

    public function updateQuote(array $dto,int $id): Quote
    {
        $quote    =   Quote::findOrFail($id);
        $quote->update($dto);
        return $quote;
    }

    public function destroy(int $id):Quote{
        $quote            =   Quote::findOrFail($id);
        $quote->status    =   'INACTIVE';
        $quote->save();
        return $quote;
    }

    public function getQuote(int $id):Quote{
        $quote    =   Quote::findOrFail($id);
        return $quote;
    }
}
