<div class="modal fade" id="mdl_edit_service" tabindex="-1" aria-labelledby="mdl_edit_service_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <i class="fa fa-cogs text-primary me-2"></i>
                <div>
                    <h5 class="modal-title mb-0" id="mdl_edit_service_label">Servicio</h5>
                    <small class="text-muted">Editar servicio.</small>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                @include('workshop.quotes.forms.form_edit_service')
            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-warning small">
                    <i class="fa fa-exclamation-circle"></i>
                    Los campos marcados con asterisco (<label class="required"></label>) son obligatorios.
                </div>
                <div class="mt-sm-0 mt-2 text-end">
                    <button type="submit" form="form_edit_service" class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> Actualizar
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>


<script>
    const paramsMdlEditService = {
        id: null
    };

    async function openMdlEditService(serviceId) {
        paramsMdlEditService.id = serviceId;

        const serviceEdit = lstServices.find((item) => item.id == serviceId);
        paintServiceEdit(serviceEdit);

        $('#mdl_edit_service').modal('show');
    }

    function eventsMdlEditService() {
        document.querySelector('#form_edit_service').addEventListener('submit', (e) => {
            e.preventDefault();
            updateItemService();
        })
    }

    function paintServiceEdit(item) {
        document.querySelector('#service_price_edit').value = parseFloat(item.sale_price).toFixed(2);
        document.querySelector('#service_quantity_edit').value = formatQuantity(item.quantity);
        document.querySelector('#service_name_edit').textContent = item.name;
        document.querySelector('#service_original_price_edit').textContent = item.sale_price;

    }

    function updateItemService() {

        toastr.clear();
        const service = getFormServiceEdit();
        setServiceEdit(service, lstServices);
        dtServices = destroyDataTable(dtServices);
        clearTable('dt-quotes-services');
        paintOrderServices(lstServices);
        dtServices = loadDataTableSimple('dt-quotes-services');

        calculateAmounts();
        paintAmounts();
        toastr.success('SERVICIO ACTUALIZADO EN EL DETALLE DE SERVICIOS');

        $('#mdl_edit_service').modal('hide');

    }

    function getFormServiceEdit() {

        const id = paramsMdlEditService.id;
        const salePrice = parseFloat(document.querySelector('#service_price_edit').value);
        const quantity = parseFloat(document.querySelector('#service_quantity_edit').value);

        const validation = validationFormService(id, quantity, salePrice);
        if (!validation) {
            return null;
        };

        const service = {
            id,
            sale_price: salePrice,
            quantity,
            total: salePrice * quantity
        }

        return service;

    }

    function setServiceEdit(service, lstItems) {
        const index = lstItems.findIndex((i) => i.id == service.id);

        lstItems[index].sale_price = service.sale_price;
        lstItems[index].quantity = service.quantity;
        lstItems[index].total = service.total;

    }
</script>
