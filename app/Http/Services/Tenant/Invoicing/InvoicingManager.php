<?php

namespace App\Http\Services\Tenant\Invoicing;

use App\Http\Services\Tenant\Invoicing\CreditNote\CreditNoteService;
use App\Models\Tenant\CreditNote;
use Greenter\Utils\Util;
use Exception;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Facades\DB;

/**
 * Única fuente de config Greenter (certificado, credenciales SOL, ambiente). Una sola empresa
 * por tenant (company_id=1) — las sedes NO tienen RUC/certificado propio, comparten esta config.
 */
class InvoicingManager
{
    public function getUtil(): Util
    {
        return Util::getInstance();
    }

    public function config(Util $util): See
    {
        $greenter_config = DB::table('company_invoices as ci')
            ->join('companies as c', 'c.id', '=', 'ci.company_id')
            ->select('ci.environment', 'ci.certificate_url', 'c.files_route', 'c.ruc', 'ci.secondary_user', 'ci.secondary_password')
            ->where('ci.company_id', 1)
            ->first();

        if (!$greenter_config) {
            throw new Exception('NO SE ENCONTRÓ NINGUNA CONFIGURACIÓN PARA GREENTER');
        }
        if ($greenter_config->environment !== 'DEMO' && $greenter_config->environment !== 'PRODUCCION') {
            throw new Exception('NO SE HA CONFIGURADO EL AMBIENTE BETA O PRODUCCIÓN PARA GREENTER');
        }

        // En DEMO, Util::getSee() hardcodea las credenciales SOL (MODDATOS/RUC 20000000001) y
        // no toca secondary_user/secondary_password/ruc: solo son obligatorios en PRODUCCION.
        if ($greenter_config->environment === 'PRODUCCION') {
            if (!$greenter_config->secondary_user) {
                throw new Exception('DEBE ESTABLECER LA CREDENCIAL SOL_USER');
            }
            if (!$greenter_config->secondary_password) {
                throw new Exception('DEBE ESTABLECER LA CREDENCIAL SOL_PASS');
            }
            if (!$greenter_config->ruc) {
                throw new Exception('LA EMPRESA NO TIENE RUC EN LA BD!!!');
            }
        }

        $endpoint = $greenter_config->environment === 'DEMO' ? SunatEndpoints::FE_BETA : SunatEndpoints::FE_PRODUCCION;
        $see      = $util->getSee($endpoint, $greenter_config);

        if (!$see) {
            throw new Exception('ERROR EN LA CONFIGURACIÓN DE GREENTER, SEE ES NULO');
        }

        return $see;
    }

    public function filesRoute(): string
    {
        return DB::table('companies')->where('id', 1)->value('files_route');
    }

    public function sendCreditNote(CreditNote $creditNote, array $details, object $customer): array
    {
        $util       = $this->getUtil();
        $see        = $this->config($util);
        $filesRoute = $this->filesRoute();

        return (new CreditNoteService())->sendCreditNote($creditNote, $details, $customer, $filesRoute, $util, $see);
    }

    /**
     * ¿El tipo de comprobante está activo (tiene serie configurada) en la empresa?
     */
    public function isActiveTypeInvoice(int $document_type_id): void
    {
        $isActive = DB::table('document_serializations')
            ->where('company_id', 1)
            ->where('document_type_id', $document_type_id)
            ->exists();

        if (!$isActive) {
            throw new Exception('EL TIPO DE FACTURACIÓN NO ESTÁ ACTIVO EN LA EMPRESA!!!');
        }
    }
}
