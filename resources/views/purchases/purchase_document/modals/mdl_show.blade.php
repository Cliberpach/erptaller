<style>
    .data-key {
      font-weight: bold !important;
      color: #007bff !important; 
      width: 160px;
      display: inline-block; 
    }
    .data-value {
      color: #343a40 !important; 
      word-break: break-word !important; 
    }
    .modal-body {
      background-color: #f8f9fa; 
    }
    .modal-body .col-12 {
      border: 1px solid #dee2e6;
      background-color: #ffffff; 
    }
</style>

<div class="modal fade" id="mdlShowPurchaseDocument" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    DOCUMENTO COMPRA <span style="padding:0;margin:0;" id="spanNoteId"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="col-12 border rounded p-3 bg-light shadow-sm" style="margin-bottom: 10px;">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <p class="mb-2"><span class="data-key">ID:</span> <span class="data-value" id="noteId"></span></p>
                            <p class="mb-2"><span class="data-key">Usuario Registrador:</span> <span class="data-value" id="userRecorderName"></span></p>
                            <p class="mb-2"><span class="data-key">Observación:</span> <span class="data-value" id="observation"></span></p>
                            <p class="mb-2"><span class="data-key">Estado:</span> <span class="data-value" id="estado"></span></p>
                            <p class="mb-2"><span class="data-key">Creado el:</span> <span class="data-value" id="createdAt"></span></p>
                            <p class="mb-2"><span class="data-key">Actualizado el:</span> <span class="data-value" id="updatedAt"></span></p>
                            <p class="mb-2"><span class="data-key">Proveedor:</span> <span class="data-value" id="supplierName"></span></p>
                            <p class="mb-2"><span class="data-key">Documento del Proveedor:</span> 
                                <span class="data-value" id="supplierDocument"></span></p>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <p class="mb-2"><span class="data-key">Condición:</span> <span class="data-value" id="condition"></span></p>
                            <p class="mb-2"><span class="data-key">Moneda:</span> <span class="data-value" id="currency"></span></p>
                            <p class="mb-2"><span class="data-key">Documento:</span> <span class="data-value" id="documentType"></span></p>
                            <p class="mb-2"><span class="data-key">Serie y Correlativo:</span> <span class="data-value" id="documentNumber"></span></p>
                            <p class="mb-2"><span class="data-key">IGV:</span> <span class="data-value" id="igv"></span></p>
                            <p class="mb-2"><span class="data-key">Subtotal:</span> <span class="data-value" id="subtotal"></span></p>
                            <p class="mb-2"><span class="data-key">Monto IGV:</span> <span class="data-value" id="amountIgv"></span></p>
                            <p class="mb-2"><span class="data-key">Total:</span> <span class="data-value" id="total"></span></p>
                            <p class="mb-0"><span class="data-key">Precios con IGV:</span> 
                                <span class="data-value" id="pricesWithIgv"></span></p>
                        </div>
                    </div>
                </div>
                
                
                
                <div class="col-12 border rounded p-2 bg-light shadow-sm">
                    @include('purchases.purchase_document.tables.tbl_purchase_document_show')
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Salir</button>
            </div>
        </div>
    </div>
</div>


<script>

    let dtPurchaseDocumentShow    =   null;

    function eventsMdlPurchaseDocumentShow(){
        document.getElementById('mdlShowPurchaseDocument').addEventListener('hidden.bs.modal', () => {
            
            const dataFields = [
                'noteId', 'userRecorderName', 'observation', 'estado', 
                'createdAt', 'updatedAt', 'supplierName', 'supplierDocument', 
                'condition', 'currency', 'documentType', 'documentNumber', 
                'igv', 'subtotal', 'amountIgv', 'total', 'pricesWithIgv'
            ];
            
            dataFields.forEach(fieldId => {
                document.getElementById(fieldId).textContent = '';
            });

            destroyDataTable(dtPurchaseDocumentShow);
            clearTable('tbl_purchase_document_show');
            dtPurchaseDocumentShow    =   loadDataTableSimple(dtPurchaseDocumentShow,'tbl_purchase_document_show');
        });
    }

    function openMdlShowPurchaseDocument(purchase_document_id) {

        getPurchaseDocument(purchase_document_id);
        
    }

    async function getPurchaseDocument(purchase_document_id){
        try {
            toastr.clear();
            mostrarAnimacion1();
            const token                         =   document.querySelector('input[name="_token"]').value;
            const urlGetPurchaseDocument        =   @json(route('tenant.compras.documento_compra.show', ['id' => 'ID']));
            const url                           =   urlGetPurchaseDocument.replace('ID', purchase_document_id);

            const response  =   await fetch(url, {
                                    method: 'GET',
                                    headers: {
                                        'X-CSRF-TOKEN': token 
                                    }
                                });

            const   res =   await response.json();

            if(res.success){
                paintPurchaseDocument(res.purchase_document);

                destroyDataTable(dtPurchaseDocumentShow);
                clearTable('tbl_purchase_document_show');
                paintPurchaseDocumentDetail(res.detail);
                dtPurchaseDocumentShow    =   loadDataTableSimple(dtPurchaseDocumentShow,'tbl_purchase_document_show');

                $('#mdlShowPurchaseDocument').modal('show');
                toastr.success('MOSTRANDO DOCUMENTO DE COMPRA');
            }else{
                toastr.error(res.message,'ERROR EN EL SERVIDOR');
            }

        } catch (error) {
            toastr.error(error,'ERROR EN LA PETICIÓN VER DOCUMENTO DE COMPRA');
        }finally{
            ocultarAnimacion1();
        }
    }

    function paintPurchaseDocument(purchase_document) {
        document.getElementById('noteId').textContent = purchase_document.id;
        document.getElementById('userRecorderName').textContent = purchase_document.user_recorder_name;
        document.getElementById('observation').textContent = purchase_document.observation;
        document.getElementById('estado').textContent = purchase_document.estado;
        document.getElementById('createdAt').textContent = purchase_document.created_at;
        document.getElementById('updatedAt').textContent = purchase_document.updated_at;

        document.getElementById('supplierName').textContent = purchase_document.supplier_name;
        document.getElementById('supplierDocument').textContent = 
            `${purchase_document.supplier_type_document_abbreviation} ${purchase_document.supplier_document_number}`;
        document.getElementById('condition').textContent = purchase_document.condition;
        document.getElementById('currency').textContent = purchase_document.currency;
        document.getElementById('documentType').textContent = purchase_document.document_type;
        document.getElementById('documentNumber').textContent = 
            `${purchase_document.serie}-${purchase_document.correlative}`;
        document.getElementById('igv').textContent = `${parseFloat(purchase_document.igv).toFixed(2)}%`;
        document.getElementById('subtotal').textContent = parseFloat(purchase_document.subtotal).toFixed(2);
        document.getElementById('amountIgv').textContent = parseFloat(purchase_document.amount_igv).toFixed(2);
        document.getElementById('total').textContent = parseFloat(purchase_document.total).toFixed(2);
        document.getElementById('pricesWithIgv').textContent = 
        purchase_document.prices_with_igv === 1 ? "Sí" : "No";
    }


    function paintPurchaseDocumentDetail(details) {
        
        const tbody = document.querySelector("#tbl_purchase_document_show tbody");

        details.forEach(detail => {
            const row = document.createElement("tr");
            
            row.innerHTML = `
                <td>${detail.product_name}</td>
                <td>${detail.category_name}</td>
                <td>${detail.brand_name}</td>
                <td>${parseFloat(detail.quantity).toFixed(2)}</td>
                <td>${parseFloat(detail.purchase_price).toFixed(2)}</td>
                <td>${parseFloat(detail.subtotal).toFixed(2)}</td>
            `;

            tbody.appendChild(row); 
        });

    }
</script>