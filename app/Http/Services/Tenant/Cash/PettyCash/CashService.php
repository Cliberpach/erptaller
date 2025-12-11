<?php

namespace App\Http\Services\Tenant\Cash\PettyCash;

use App\Models\Tenant\Cash\PettyCash;

class CashService
{
    private CashRepository $s_repository;
    private CashDto $s_dto;

    public function __construct()
    {
        $this->s_repository =   new CashRepository();
        $this->s_dto        =   new CashDto();
    }

    public function store(array $data): PettyCash
    {
        $dto    =   $this->s_dto->getDtoStore($data);
        $cash   =   $this->s_repository->insertCash($dto);
        return $cash;
    }

    public function update(array $data, int $id): PettyCash
    {
        $dto    =   $this->s_dto->getDtoStore($data);
        $cash   =   $this->s_repository->updateCash($dto, $id);
        return $cash;
    }

    public function getCash(int $id): PettyCash
    {
        return $this->s_repository->findCash($id);
    }

    public function destroy(int $id): PettyCash
    {
        return $this->s_repository->destroy($id);
    }

    public function searchCashAvailable(array $data)
    {
        $cashes = $this->s_repository->searchCashAvailable($data);
        return $cashes;
    }

    public function setStatus(int $id, string $status)
    {
        $this->s_repository->setStatus($id,$status);
    }
}
