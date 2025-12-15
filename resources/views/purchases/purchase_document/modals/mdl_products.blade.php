<div class="modal fade" id="mdlProducts" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Seleccionar Producto</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                    <label for="categoria" style="font-weight: bold;">CATEGORÍA</label>

                    <select data-placeholder="Seleccione una opción" name="categoria" id="categoria" class="select2_form_mdl" onchange="dtProductos.ajax.reload();">
                        <option></option>
                        @foreach ($categories as $category)
                            <option value="{{$category->id}}">{{$category->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                    <label for="marca" style="font-weight: bold;">MARCA</label>

                    <select data-placeholder="Seleccione una opción" name="marca" id="marca" class="select2_form_mdl" onchange="dtProductos.ajax.reload();">
                        <option></option>
                        @foreach ($brands as $brand)
                            <option value="{{$brand->id}}">{{$brand->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        @include('purchases.purchase_document.tables.tbl_purchase_documents_products')
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

        </div>
      </div>
    </div>
</div>

<script>
    const product_selected  =   {
                                    product_id:null,
                                    product_name:null,
                                    category_name:null,
                                    brand_name:null,
                                    producto_unidad_medida:null,
                                    quantity:null,
                                    purchase_price:null,
                                    almacen_id:null
                                }

    function eventsMdlProducts(){

    }

    function openMdlProducts(){
        $('#mdlProducts').modal('show');
    }

    function selectProduct(producto_id) {

        const fila  =   getRowById(dtProductos,producto_id);

        if(!fila){
            toastr.error('NO SE ENCONTRÓ EL PRODUCTO EN LA TABLA PRODUCTOS');
            return;
        }

        console.log(fila);

        //======= SETTEAR PRODUCTO =======
        const producto                              =   fila;
        document.querySelector('#producto').value   =   producto.name;
        document.querySelector('#precio').value     =   producto.purchase_price;


        product_selected.product_id                 =   producto.id;
        product_selected.product_name               =   producto.name;
        product_selected.category_name              =   producto.category_name;
        product_selected.brand_name                 =   producto.brand_name;
        product_selected.producto_unidad_medida     =   'NIU';
        product_selected.purchase_price             =   producto.purchase_price;

        console.log('PRODUCTO ELEGIDO',product_selected);


        $('#mdlProducts').modal('hide');
        document.querySelector('#cantidad').focus();

    }

    function clearFormSelectProduct(){
        const inputProducto =   document.querySelector('#producto');
        //const inputUnidad   =   document.querySelector('#unidad');
        const inputCantidad =   document.querySelector('#cantidad');
        const inputPrecio   =   document.querySelector('#precio');

        inputProducto.value =   '';
        //inputUnidad.value   =   '';
        inputCantidad.value =   '';
        inputPrecio.value   =   '';
        product_selected.product_id             =   null;
        product_selected.product_name           =   null;
        product_selected.category_name          =   null;
        product_selected.brand_name             =   null;
        product_selected.producto_unidad_medida =   null;
        product_selected.quantity               =   null;
        product_selected.purchase_price         =   null;
        //$('#almacen').val(1).trigger('change');
    }

</script>
