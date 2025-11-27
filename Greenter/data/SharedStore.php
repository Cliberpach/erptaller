<?php

namespace App\Greenter\Data;

use Illuminate\Http\Request;
use Greenter\Model\Company\Company;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Illuminate\Support\Facades\DB;

class SharedStore
{
    public function getCompany(): Company
    {
        //======= OBTENIENDO DATA DE EMPRESA =========
        $company    =   DB::select('select * from companies as c where c.id=1')[0];
        $company_invoice    =   DB::select('select * from company_invoices as ci where ci.company_id = 1')[0];
        //====== NOTA COD LOCAL POR DEFECTO 0000 DE LA CENTRAL ======= //

        return (new Company())
            ->setRuc($company->ruc)
            ->setNombreComercial($company->abbreviated_business_name)
            ->setRazonSocial($company->business_name)
            ->setAddress((new Address())
                ->setUbigueo($company_invoice->ubigeo)
                ->setDistrito($company_invoice->district_name)
                ->setProvincia($company_invoice->province_name)
                ->setDepartamento($company_invoice->department_name)
                ->setUrbanizacion($company_invoice->urbanization)
                ->setCodLocal($company_invoice->local_code)
                ->setDireccion($company->fiscal_address))
            ->setEmail($company->email)
            ->setTelephone($company->phone);
    }

    public function getClientPerson(): Client
    {
        $client = new Client();
        $client->setTipoDoc('1')
            ->setNumDoc('48285071')
            ->setRznSocial('NIPAO GUVI')
            ->setAddress((new Address())
                ->setDireccion('Calle fusión 453, SAN MIGUEL - LIMA - PERU'));

        return $client;
    }

    public function getClient(): Client
    {
        $client = new Client();
        $client->setTipoDoc('6')
            ->setNumDoc('20000000001')
            ->setRznSocial('EMPRESA 1 S.A.C.')
            ->setAddress((new Address())
                ->setDireccion('JR. NIQUEL MZA. F LOTE. 3 URB.  INDUSTRIAL INFAÑTAS - LIMA - LIMA -PERU'))
            ->setEmail('client@corp.com')
            ->setTelephone('01-445566');

        return $client;
    }

    public function getSeller(): Client
    {
        $client = new Client();
        $client->setTipoDoc('1')
            ->setNumDoc('44556677')
            ->setRznSocial('VENDEDOR 1')
            ->setAddress((new Address())
                ->setDireccion('AV INFINITE - LIMA - LIMA - PERU'));

        return $client;
    }
}
