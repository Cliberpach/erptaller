<div class="modal fade" id="mdl_edit_product" tabindex="-1" aria-labelledby="mdl_edit_product_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <i class="fa fa-cogs text-primary me-2"></i>
                <div>
                    <h5 class="modal-title mb-0" id="mdl_edit_product_label">Producto</h5>
                    <small class="text-muted">Editar producto.</small>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                @include('workshop.quotes.forms.form_edit_product')
            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-warning small">
                    <i class="fa fa-exclamation-circle"></i>
                    Los campos marcados con asterisco (<label class="required"></label>) son obligatorios.
                </div>
                <div class="mt-sm-0 mt-2 text-end">
                    <button type="submit" form="form_edit_product" class="btn btn-primary btn-sm">
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
    const paramsMdlEditProduct = {
        id: null,
        warehouse_id: null
    };

    async function openMdlEditProduct(productId) {
        paramsMdlEditProduct.id = productId;

        const productEdit = lstProducts.find((item) => item.id == productId);
        paramsMdlEditProduct.warehouse_id = productEdit.warehouse_id;
        paintProductEdit(productEdit);

        $('#mdl_edit_product').modal('show');
    }

    function eventsMdlEditProduct() {
        document.querySelector('#form_edit_product').addEventListener('submit', (e) => {
            e.preventDefault();
            updateItemProduct();
        })
    }

    function paintProductEdit(item) {
        document.querySelector('#product_price_edit').value = parseFloat(item.sale_price).toFixed(2);
        document.querySelector('#product_quantity_edit').value = formatQuantity(item.quantity);
        document.querySelector('#product_name_edit').textContent = item.name;
        document.querySelector('#product_category_edit').textContent = item.category_name;
        document.querySelector('#product_brand_edit').textContent = item.brand_name;
        document.querySelector('#product_original_price_edit').textContent = item.sale_price;

    }

    async function updateItemProduct() {

        toastr.clear();
        const product = getFormProductEdit();
        if (!product) {
            return;
        }

        saveProductEdit(product, lstProducts);

        dtProducts = destroyDataTable(dtProducts);
        clearTable('dt-quotes-products');
        paintOrderProducts(lstProducts);
        dtProducts = loadDataTableSimple('dt-quotes-products');

        calculateAmounts();
        paintAmounts();
        toastr.success('PRODUCTO ACTUALIZADO EN EL DETALLE DE PRODUCTOS');

        $('#mdl_edit_product').modal('hide');

    }

    function getFormProductEdit() {

        const id = paramsMdlEditProduct.id;
        const salePrice = parseFloat(document.querySelector('#product_price_edit').value);
        const quantity = parseFloat(document.querySelector('#product_quantity_edit').value);

        const validation = validationFormProduct(id, quantity, salePrice);
        if (!validation) {
            return false;
        };

        const product = {
            warehouse_id: paramsMdlEditProduct.warehouse_id,
            id,
            sale_price: salePrice,
            quantity,
            total: salePrice * quantity
        }

        return product;

    }

    function saveProductEdit(product, lstItems) {
        const index = lstItems.findIndex((i) => i.id == product.id);

        lstItems[index].sale_price = product.sale_price;
        lstItems[index].quantity = product.quantity;
        lstItems[index].total = product.total;

    }
</script>
