<?php

namespace App\Http\Services\Tenant\WorkShop\Quotes;

use App\Models\Tenant\WorkShop\Quote\Quote;
use Illuminate\Contracts\View\View;

class QuoteManager
{
    private QuoteService $s_quote;

    public function __construct()
    {
        $this->s_quote   =   new QuoteService();
    }

    public function store(array $datos): Quote
    {
        return $this->s_quote->store($datos);
    }

    public function getQuote(int $id): array
    {
        return $this->s_quote->getQuote($id);
    }

    public function update(array $data, int $id): Quote
    {
        return $this->s_quote->update($data, $id);
    }

    public function destroy(int $id)
    {
        $this->s_quote->destroy($id);
    }

    public function pdfOne(int $id)
    {
        return $this->s_quote->pdfOne($id);
    }

    public function convertOrderCreate(int $id):View{
        return $this->s_quote->convertOrderCreate($id);
    }

}
