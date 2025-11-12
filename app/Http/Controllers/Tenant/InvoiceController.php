<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Ws\Services\SunatEndpoints;
use DateTime;
use App\Greenter\Utils\Util;
use App\Models\Company as ModelsCompany;
use App\Models\Tenant\ReservationDocument;
use App\Models\Tenant\Sale;
use Exception;
use Greenter\Model\Sale\Note;
use Illuminate\Support\Facades\DB;
use Luecano\NumeroALetras\NumeroALetras;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class InvoiceController extends Controller
{

/*
    Greenter\Model\Response\BillResult {#2038 // app\Http\Controllers\Tenant\InvoiceController.php:157
    #success: true
    #error: null
    #cdrZip: b"PK
    \x00\x00\e\x00\x00\x00
    R-20370146994-03-B001-1.xmlÁW█n█8
    R-20370146994-03-B001-1.xmlPK
    "
    #cdrResponse: 
    Greenter\Model\Response
    \
    CdrResponse {#2061
        #id: "B001-1"
        #code: "0"
        #description: "La Boleta numero B001-1, ha sido aceptada"
        #notes: []
        #reference: null
    }
    }
*/ 
    public static function send_sunat(Request $request){
        try {

            $sale_document_id       =   $request->get('sale_document_id');
            $sale_document          =   null;
            $detail                 =   [];

            if($request->get('type')  === 'RESERVATION_DOCUMENT'){
                $sale_document  =   ReservationDocument::find($sale_document_id);
                $detail         =   DB::select('select rdd.* 
                                    from reservation_documents_detail as rdd
                                    where rdd.reservation_document_id = ?',[$sale_document_id]);
            }

            if($request->get('type')  === 'SALE_DOCUMENT'){
                $sale_document  =   Sale::find($sale_document_id);
                $detail         =   DB::select('select sdd.* 
                                    from sales_documents_details as sdd
                                    where sdd.sale_document_id = ?',[$sale_document_id]);
            }

            if(!$sale_document){
                throw new Exception("DOCUMENTO DE VENTA NO ENCONTRADO EN LA BD!!!");
            }

            if(count($detail) === 0){
                throw new Exception("EL DETALLE DEL DOCUMENTO DE VENTA ESTÁ VACÍO!!!");
            }

            //====== VERIFICAR SI EL COMPROBANTE ESTÁ ACTIVO EN LA EMPRESA =========
            InvoiceController::isActiveTypeInvoice($sale_document->type_sale_code);
           
            //======= VALIDAR TIPO COMPROBANTE CON TIPO DOCUMENTO =======
            $company    =   ModelsCompany::find(1);

            //======= INSTANCIAMOS LA CLASE UTIL ========
            $util = Util::getInstance();
    
            //======== INSTANCIAR OBJETO FACTURA O BOLETA ========
            $invoice = new Invoice();
                
            //====== CONSTRUIR CLIENTE =========
            $customer = DB::connection('landlord')->select('
                        SELECT 
                            c.address,
                            c.email,
                            c.phone 
                        FROM customers AS c
                        WHERE c.id = ?', [$sale_document->customer_id]);

            if(count($customer) === 0){
                throw new Exception("ERROR AL OBTENER CLIENTE DE LA VENTA!!!");
            }

            $client = new Client();
            $client->setTipoDoc(ltrim($sale_document->customer_document_code, '0'))
                    ->setNumDoc($sale_document->customer_document_number)
                    ->setRznSocial($sale_document->customer_name)
                    ->setAddress((new Address())
                        ->setDireccion($customer[0]->address))
                    ->setEmail($customer[0]->email)
                    ->setTelephone($customer[0]->phone);
 
            //======= CONSTRUIR FACTURA ENCABEZADO ======
            $invoice
                ->setUblVersion('2.1')
                ->setFecVencimiento(new DateTime($sale_document->created_at))
                ->setTipoOperacion('0101')
                ->setTipoDoc(str_pad($sale_document->type_sale_code, 2, '0', STR_PAD_LEFT))
                ->setSerie($sale_document->serie)
                        ->setCorrelativo($sale_document->correlative)
                        ->setFechaEmision(new DateTime($sale_document->created_at))
                        ->setFormaPago(new FormaPagoContado())
                        ->setTipoMoneda('PEN')
                        ->setCompany($util->shared->getCompany())
                        ->setClient($client)
                        ->setMtoOperGravadas($sale_document->subtotal)
                        ->setMtoIGV(round($sale_document->igv_amount, 2))
                        ->setTotalImpuestos($sale_document->igv_amount)
                        ->setValorVenta($sale_document->subtotal)
                        ->setSubTotal((float)$sale_document->total)
                        ->setMtoImpVenta((float)$sale_document->total);

            //======== CONSTRUIR DETALLE BOLETA/FACTURA ========
            $items      =   [];

            foreach ($detail as $product) {

                $items[] = (new SaleDetail())
                        ->setCodProducto($product->product_code)
                        ->setUnidad($product->product_unit)
                        ->setDescripcion($product->product_description)
                        ->setCantidad($product->quantity)
                        ->setMtoValorUnitario($product->mto_valor_unitario)
                        ->setMtoValorVenta($product->mto_valor_venta)
                        ->setMtoBaseIgv($product->mto_base_igv)
                        ->setPorcentajeIgv($product->porcentaje_igv) 
                        ->setIgv($product->igv)
                        ->setTipAfeIgv($product->tip_afe_igv)
                        ->setTotalImpuestos($product->total_impuestos)
                        ->setMtoPrecioUnitario($product->mto_precio_unitario);

            }

            $invoice->setDetails($items)
                ->setLegends([
                    (new Legend())
                    ->setCode('1000')
                    ->setValue($sale_document->lengd)
                ]);

         
            $see =  InvoiceController::setConfigurationGreenter($util);
           
            $res = $see->send($invoice);

            $util->writeXml($invoice, $see->getFactory()->getLastXml(),$sale_document->type_sale_code,$company->files_route,null);
            
            if($sale_document->type_sale_code   ==  1){
                $sale_document->ruta_xml      =   'storage/'.$company->files_route.'/greenter/facturas/xml/'.$invoice->getName().'.xml';
            }

            if($sale_document->type_sale_code   ==  3){
                $sale_document->ruta_xml      =   'storage/'.$company->files_route.'/greenter/boletas/xml/'.$invoice->getName().'.xml';
            }

            //======== ENVÍO CORRECTO Y ACEPTADO ==========
            if($res->isSuccess()){

                //====== GUARDANDO RESPONSE ======
                $sale_document->response_success        =   $res->isSuccess();
                $cdr                                    =   $res->getCdrResponse();
                $sale_document->response_cdrZip         =   $cdr?true:false;
                $sale_document->estado                  =   'ENVIADO';

                //====== EN CASO HAYA CDR ========
                if($cdr){
                    $sale_document->cdr_response_id             =   $cdr->getId();
                    $sale_document->cdr_response_code           =   $cdr->getCode();
                    $sale_document->cdr_response_description    =   $cdr->getDescription();
                    $sale_document->cdr_response_notes          =   '|' . implode('|', $cdr->getNotes()) . '|';
                    $sale_document->cdr_response_reference      =   $cdr->getReference();

                    $util->writeCdr($invoice, $res->getCdrZip(),$sale_document->type_sale_code,$company->files_route,null);

                    if($sale_document->type_sale_code   ==  1){
                        $sale_document->ruta_cdr      =   'storage/'.$company->files_route.'/greenter/facturas/cdr/'.$invoice->getName().'.zip';
                    }

                    if($sale_document->type_sale_code   ==  3){
                        $sale_document->ruta_cdr      =   'storage/'.$company->files_route.'/greenter/boletas/cdr/'.$invoice->getName().'.zip';
                    }

                    if($cdr->getCode() == 0){
                        $sale_document->estado  =   'ACEPTADO';
                    }
                }
                       
                $sale_document->update(); 
    
                return response()->json(["success"   =>  true,"message"=>$cdr->getDescription()]);

            }else{
                        
                //====== GUARDANDO RESPONSE ======
                $sale_document->response_success        =   $res->isSuccess();
                $res_error                              =   $res->getError();
                $message_error                          =   '';
                $sale_document->estado                  =   'PENDIENTE';

                if($res_error){

                    $sale_document->response_error_code         =   $res_error->getCode();
                    $sale_document->response_error_message      =   $res_error->getMessage();
                    $sale_document->estado                      =   'RECHAZADO';

                    $message_error  =   "CÓDIGO: ".$res_error->getCode()." | DESCRIPCIÓN: ".$res_error->getMessage();   
                    /*
                        ================================================================
                        ERROR 1033 
                        El comprobante fue registrado previamente con otros datos 
                        - Detalle: xxx.xxx.xxx value='ticket: 202413738761966 
                        error: El comprobante B001-1704 fue informado anteriormente'
        
                        ERROR 2223
                        El documento ya fue informado
                    ================================================================
                    */
                    if($res_error->getCode() == 1033 || $res_error->getCode() == 2223){

                        $sale_document->estado              =   'RECHAZADO';       
                        $sale_document->update(); 
        
                        return response()->json(["success"   =>  false,"message"   =>  $res_error->getMessage()]);
                    }

                }
                     
                $sale_document->update(); 
                return response()->json(["success"   =>  false,"message"   =>  $message_error]);
    
            }
    
        } catch (\Throwable $th) {
            return response()->json(['success'=>false,"message"   =>  $th->getMessage()]);        
        }
    }

    public static function setConfigurationGreenter($util){

        //==== OBTENIENDO CONFIGURACIÓN DE GREENTER ======
        $greenter_config    =   DB::select('select 
                                ci.environment,
                                ci.certificate_url,
                                c.files_route,
                                c.ruc,
                                ci.secondary_user,
                                ci.secondary_password
                                from company_invoices as ci
                                inner join companies as c on c.id = ci.company_id
                                where ci.company_id = 1');


        if(count($greenter_config) === 0){
            throw new Exception('NO SE ENCONTRÓ NINGUNA CONFIGURACIÓN PARA GREENTER');
        }

        if(!$greenter_config[0]->secondary_user){
            throw new Exception('DEBE ESTABLECER LA CREDENCIAL SOL_USER');
        }
        if(!$greenter_config[0]->secondary_password){
            throw new Exception('DEBE ESTABLECER LA CREDENCIAL SOL_PASS');
        }
        if ($greenter_config[0]->environment !== "DEMO" && $greenter_config[0]->environment !== "PRODUCCION") {
            throw new Exception('NO SE HA CONFIGURADO EL AMBIENTE BETA O PRODUCCIÓN PARA GREENTER');
        }
        if(!$greenter_config[0]->ruc){
                throw new Exception('LA EMPRESA NO TIENE RUC EN LA BD!!!');
        }

       $see    =   null;
       if($greenter_config[0]->environment === "DEMO"){
           //===== MODO BETA DEMO ======
           $see = $util->getSee(SunatEndpoints::FE_BETA,$greenter_config[0]);
       }

       if($greenter_config[0]->environment === "PRODUCCION"){
           //===== MODO PRODUCCION ======
           $see = $util->getSee(SunatEndpoints::FE_PRODUCCION,$greenter_config[0]);
       }

       if(!$see){
           throw new Exception('ERROR EN LA CONFIGURACIÓN DE GREENTER, SEE ES NULO');
       }

       return $see;
    }

    public static function isActiveTypeInvoice($document_type_id){

        $isActive   =   DB::select('select 
                            ds.*
                            from document_serializations as ds
                            where 
                            ds.company_id = ?
                            and ds.document_type_id = ?',
                            [ModelsCompany::find(1)->id,$document_type_id]);
    
        if(count($isActive) === 0){
            throw new Exception("EL TIPO DE FACTURACIÓN NO ESTÁ ACTIVO EN LA EMPRESA!!!");
        }
        
    }

    

}
