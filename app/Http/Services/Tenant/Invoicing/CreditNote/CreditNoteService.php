<?php

namespace App\Http\Services\Tenant\Invoicing\CreditNote;

use App\Models\Tenant\CreditNote;
use DateTime;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\See;
use Greenter\Utils\Util;

class CreditNoteService
{
    /**
     * Arma y envía la Nota de Crédito a SUNAT. $details vienen de credit_notes_details
     * (columnas SUNAT ya precalculadas en emit(), no se recalculan acá).
     */
    public function sendCreditNote(CreditNote $creditNote, array $details, object $customer, string $filesRoute, Util $util, See $see): array
    {
        $client = new Client();
        $client->setTipoDoc(ltrim($creditNote->customer_document_code, '0'))
            ->setNumDoc($creditNote->customer_document_number)
            ->setRznSocial($creditNote->customer_name)
            ->setAddress((new Address())->setDireccion($customer->address))
            ->setEmail($customer->email)
            ->setTelephone($customer->phone);

        $note = new Note();
        $note
            ->setUblVersion($creditNote->ublVersion ?? '2.1')
            ->setTipoDoc($creditNote->type_sale_code)
            ->setSerie($creditNote->serie)
            ->setCorrelativo($creditNote->correlative)
            ->setFechaEmision(new DateTime($creditNote->fechaEmision ?? $creditNote->created_at))
            ->setTipDocAfectado($creditNote->tipDocAfectado)
            ->setNumDocfectado($creditNote->numDocfectado)
            ->setCodMotivo($creditNote->codMotivo)
            ->setDesMotivo($creditNote->desMotivo)
            ->setTipoMoneda($creditNote->tipoMoneda ?? 'PEN')
            ->setCompany($util->shared->getCompany())
            ->setClient($client)
            ->setMtoOperGravadas($creditNote->mtoOperGravadas)
            ->setMtoIGV(round($creditNote->mtoIGV, 2))
            ->setTotalImpuestos($creditNote->totalImpuestos)
            ->setValorVenta($creditNote->subtotal)
            ->setSubTotal((float) $creditNote->total)
            ->setMtoImpVenta((float) $creditNote->mtoImpVenta);

        $items = [];
        foreach ($details as $item) {
            $items[] = (new SaleDetail())
                ->setCodProducto($item->codProducto)
                ->setUnidad($item->unity)
                ->setDescripcion($item->description)
                ->setCantidad($item->quantity)
                ->setMtoValorUnitario($item->mtoValorUnitario)
                ->setMtoValorVenta($item->mtoValorVenta)
                ->setMtoBaseIgv($item->mtoBaseIgv)
                ->setPorcentajeIgv($item->porcentajeIgv)
                ->setIgv($item->igv)
                ->setTipAfeIgv($item->tipAfeIgv)
                ->setTotalImpuestos($item->totalImpuestos)
                ->setMtoPrecioUnitario($item->mtoPrecioUnitario);
        }

        $note->setDetails($items)
            ->setLegends([
                (new Legend())->setCode('1000')->setValue($creditNote->legend),
            ]);

        $res = $see->send($note);
 
        // '07-01' = NC de factura, '07-03' = NC de boleta (rutas de archivo separadas, ver Util::writeFile).
        $tipoComprobante = '07-' . $creditNote->tipDocAfectado;
        $carpeta         = $creditNote->tipDocAfectado === '01' ? 'notas_credito_facturas' : 'notas_credito_boletas';

        $data = [
            'sunat_status'              => 'PENDIENTE',
            'response_success'          => $res->isSuccess(),
            'response_cdrZip'           => false,
            'response_error_code'       => null,
            'response_error_message'    => null,
            'cdr_response_id'           => null,
            'cdr_response_code'         => null,
            'cdr_response_description'  => null,
            'cdr_response_notes'        => null,
            'cdr_response_reference'    => null,
            'ruta_xml'                  => null,
            'ruta_cdr'                  => null,
            'message'                   => null,
        ];

        $util->writeXml($note, $see->getFactory()->getLastXml(), $tipoComprobante, $filesRoute, null);
        $data['ruta_xml'] = 'storage/' . $filesRoute . '/greenter/' . $carpeta . '/xml/' . $note->getName() . '.xml';

        if ($res->isSuccess()) {
            $cdr                     = $res->getCdrResponse();
            $data['response_cdrZip'] = $cdr ? true : false;

            if ($cdr) {
                $code                              = $cdr->getCode();
                $data['cdr_response_id']           = $cdr->getId();
                $data['cdr_response_code']         = $code;
                $data['cdr_response_description']  = $cdr->getDescription();
                $data['cdr_response_notes']        = '|' . implode('|', $cdr->getNotes()) . '|';
                $data['cdr_response_reference']    = $cdr->getReference();
                $data['message']                   = $data['cdr_response_description'];

                $util->writeCdr($note, $res->getCdrZip(), $tipoComprobante, $filesRoute, null);
                $data['ruta_cdr'] = 'storage/' . $filesRoute . '/greenter/' . $carpeta . '/cdr/' . $note->getName() . '.zip';

                if ($code == 0) {
                    $data['sunat_status'] = 'ACEPTADO';
                } elseif ($code > 0 && $code < 2000) {
                    $data['sunat_status'] = 'OBSERVADO';
                } else {
                    $data['sunat_status'] = 'RECHAZADO';
                }
            } else {
                $data['message']      = $creditNote->serie . '-' . $creditNote->correlative . ' enviada a Sunat, sin CDR recibido';
                $data['sunat_status'] = 'PENDIENTE';
            }

            return $data;
        }

        $res_error = $res->getError();
        $data['sunat_status'] = 'PENDIENTE';

        if ($res_error) {
            $data['response_error_code']    = $res_error->getCode();
            $data['response_error_message'] = $res_error->getMessage();
            $data['message']                = 'CÓDIGO: ' . $res_error->getCode() . ' | DESCRIPCIÓN: ' . $res_error->getMessage();
        } else {
            $data['message'] = $creditNote->serie . '-' . $creditNote->correlative . ' falló el envío a Sunat';
        }

        return $data;
    }
}
