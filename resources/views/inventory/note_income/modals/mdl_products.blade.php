<div class="modal fade" id="mdlProductos" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                        @include('inventory.note_income.tables.tbl_products')
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
    const lstTableProducts  =   [];
    const product_selected  =   {
                                    product_id:null,
                                    product_name:null,
                                    brand_name:null,
                                    category_name:null,
                                    quantity:null
                                }

    function eventsMdlProductos(){

    }

    function openMdlProducts(){
        $('#mdlProductos').modal('show');
    }

    function selectProduct(producto_id) {

        const fila  =   getRowById(dtProductos,producto_id);
        
        if(!fila){
            toastr.error('NO SE ENCONTRÓ EL PRODUCTO EN LA TABLA PRODUCTOS');
            return;
        }

        console.log(fila);

        //======= SETTEAR PRODUCTO =======
        const product                               =   fila;
        document.querySelector('#producto').value   =   product.name;
        document.querySelector('#unidad').value     =   'NIU';

        product_selected.product_id             =   product.id;
        product_selected.product_name           =   product.name;
        product_selected.category_name          =   product.category_name;
        product_selected.brand_name             =   product.brand_name;

        console.log('PRODUCTO ELEGIDO');
        console.log(product_selected);


        $('#mdlProductos').modal('hide');
        document.querySelector('#cantidad').focus();

    }

    function clearFormSelectProduct(){

        const inputProducto =   document.querySelector('#producto');
        const inputUnidad   =   document.querySelector('#unidad');
        const inputCantidad =   document.querySelector('#cantidad');

        inputProducto.value =   '';
        inputUnidad.value   =   '';
        inputCantidad.value =   '';
        
        product_selected.product_id             =   null;
        product_selected.product_name           =   null;
        product_selected.category_name          =   null;
        product_selected.brand_name             =   null;
        product_selected.quantity               =   null;

    }

</script>