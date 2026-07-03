<?php

namespace App\Http\Services\Tenant\Invoicing\Invoice;

use App\Models\Tenant\Sale;
use DateTime;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\SaleDetail;
use Greenter\See;
use Greenter\Utils\Util;

class InvoiceService
{
    /**
     * Arma y envía el comprobante a SUNAT. $products y $services vienen de
     * sales_documents_details / sales_documents_services (columnas SUNAT ya precalculadas
     * en el momento de la venta, no se recalculan acá).
     */
    public function sendInvoice(Sale $sale, array $products, array $services, object $customer, string $filesRoute, Util $util, See $see): array
    {
        $client = new Client();
        $client->setTipoDoc(ltrim($sale->customer_document_code, '0'))
            ->setNumDoc($sale->customer_document_number)
            ->setRznSocial($sale->customer_name)
            ->setAddress((new Address())->setDireccion($customer->address))
            ->setEmail($customer->email)
            ->setTelephone($customer->phone);

        $invoice = new Invoice();
        $invoice
            ->setUblVersion('2.1')
            ->setFecVencimiento(new DateTime($sale->created_at))
            ->setTipoOperacion('0101')
            ->setTipoDoc(str_pad($sale->type_sale_code, 2, '0', STR_PAD_LEFT))
            ->setSerie($sale->serie)
            ->setCorrelativo($sale->correlative)
            ->setFechaEmision(new DateTime($sale->created_at))
            ->setFormaPago(new FormaPagoContado())
            ->setTipoMoneda('PEN')
            ->setCompany($util->shared->getCompany())
            ->setClient($client)
            ->setMtoOperGravadas($sale->subtotal)
            ->setMtoIGV(round($sale->igv_amount, 2))
            ->setTotalImpuestos($sale->igv_amount)
            ->setValorVenta($sale->subtotal)
            ->setSubTotal((float) $sale->total)
            ->setMtoImpVenta((float) $sale->total);

        // Detalle: productos + servicios (mano de obra). Antes solo viajaban productos:
        // una venta de OT con servicios mandaba el comprobante SIN esas líneas a SUNAT.
        $items = [];
        foreach ([...$products, ...$services] as $item) {
            $items[] = (new SaleDetail())
                ->setCodProducto($item->code)
                ->setUnidad($item->unit)
                ->setDescripcion($item->description)
                ->setCantidad($item->quantity)
                ->setMtoValorUnitario($item->mto_valor_unitario)
                ->setMtoValorVenta($item->mto_valor_venta)
                ->setMtoBaseIgv($item->mto_base_igv)
                ->setPorcentajeIgv($item->porcentaje_igv)
                ->setIgv($item->igv)
                ->setTipAfeIgv($item->tip_afe_igv)
                ->setTotalImpuestos($item->total_impuestos)
                ->setMtoPrecioUnitario($item->mto_precio_unitario);
        }

        $invoice->setDetails($items)
            ->setLegends([
                (new Legend())->setCode('1000')->setValue($sale->legend),
            ]);

        $res = $see->send($invoice);
        
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

        $util->writeXml($invoice, $see->getFactory()->getLastXml(), $sale->type_sale_code, $filesRoute, null);

        if ($sale->type_sale_code == 1) {
            $data['ruta_xml'] = 'storage/' . $filesRoute . '/greenter/facturas/xml/' . $invoice->getName() . '.xml';
        }
        if ($sale->type_sale_code == 3) {
            $data['ruta_xml'] = 'storage/' . $filesRoute . '/greenter/boletas/xml/' . $invoice->getName() . '.xml';
        }

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

                $util->writeCdr($invoice, $res->getCdrZip(), $sale->type_sale_code, $filesRoute, null);

                if ($sale->type_sale_code == 1) {
                    $data['ruta_cdr'] = 'storage/' . $filesRoute . '/greenter/facturas/cdr/' . $invoice->getName() . '.zip';
                }
                if ($sale->type_sale_code == 3) {
                    $data['ruta_cdr'] = 'storage/' . $filesRoute . '/greenter/boletas/cdr/' . $invoice->getName() . '.zip';
                }

                if ($code == 0) {
                    $data['sunat_status'] = 'ACEPTADO';
                } elseif ($code > 0 && $code < 2000) {
                    $data['sunat_status'] = 'OBSERVADO';
                } else {
                    $data['sunat_status'] = 'RECHAZADO';
                }
            } else {
                $data['message']      = $sale->serie . '-' . $sale->correlative . ' enviado a Sunat, sin CDR recibido';
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

            // Códigos 1033/2223 = "ya fue informado antes" -> se deja en PENDIENTE (no RECHAZADO)
            // para permitir reintento, igual que la referencia.
        } else {
            $data['message'] = $sale->serie . '-' . $sale->correlative . ' falló el envío a Sunat';
        }

        return $data;
    }
}
