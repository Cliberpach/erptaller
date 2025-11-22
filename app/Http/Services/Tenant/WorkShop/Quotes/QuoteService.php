<?php

namespace App\Http\Services\Tenant\WorkShop\Quotes;

use App\Models\Tenant\WorkShop\Quote\Quote;

class QuoteService
{
    private QuoteRepository $s_repository;
    private QuoteDto $s_dto;
    private QuoteValidation $s_validation;

    public function __construct()
    {
        $this->s_repository =   new QuoteRepository();
        $this->s_dto        =   new QuoteDto();
        $this->s_validation =   new QuoteValidation();
    }

    public function store(array $data): Quote
    {
        $data           =   $this->s_validation->validationStore($data);
        $dto            =   $this->s_dto->getDtoStore($data);

        $quote      =   $this->s_repository->insertQuote($dto);
        $this->s_repository->insertQuoteDetail($data['lst_products'],$data['lst_services'], $quote);
        dd($quote);
        return $quote;
    }

    public function update(array $data, int $id): Quote
    {
        $dto        =   $this->s_dto->getDtoStore($data);
        $quote    =   $this->s_repository->updateQuote($dto, $id);
        return $quote;
    }

    public function destroy(int $id):Quote
    {
        return $this->s_repository->destroy($id);
    }

    public function getQuote(int $id): Quote
    {
        return $this->s_repository->getQuote($id);
    }
}
