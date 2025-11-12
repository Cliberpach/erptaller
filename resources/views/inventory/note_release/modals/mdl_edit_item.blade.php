<div class="modal fade" id="mdlEditItem" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Editar Item</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

           @include('inventory.note_income.forms.form_edit_item')

        </div>
        <div class="modal-footer">
            
            <div class="col-12">
                <div style="display:flex;justify-content:end;">
                    <button type="button" style="margin-right:5px;" class="btn btn-secondary mr-1" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" form="formEditItem" class="btn btn-primary" >Guardar</button>
                </div>
                <span  style="color:rgb(219, 155, 35);font-size:14px;font-weight:bold;display:block;">Los campos con * son obligatorios</span>
            </div>
        
        </div>
      </div>
    </div>
</div>

<script>

    const product_edition   =   {product_id:null};

    function eventsMdlEditItem(){
        document.addEventListener('click',(e)=>{

            if(e.target.classList.contains('btnEditItem')){
                toastr.clear();

                const producto_id   =   e.target.getAttribute('data-producto-id');

                if(!producto_id){
                    toastr.error('ERROR AL SETTEAR PRODUCTO');
                    return;
                }

                setProducto(producto_id);
                openMdlEditItem();
            }

            if(e.target.classList.contains('btnDeleteItem')){
                toastr.clear();

                const producto_id       =   e.target.getAttribute('data-producto-id');

                const res_delete_item   =    deleteItem(producto_id);

                if(res_delete_item){
                    clearTable('tbl_note_income_detail');
                    destruirDataTableCompraDetalle();
                    pintarTableCompraDetalle(lstNoteRelease);
                    iniciarDataTableCompraDetalle();
                    toastr.success('ITEM ELIMINADO!!');
                }

                
            }

        })

        document.querySelector('#formEditItem').addEventListener('submit',(e)=>{

            mostrarAnimacion1();
            e.preventDefault();
            const dataFormEdit          =   getDataFormEdit();
            const validacionFormEdit    =   validarDataFormEdit(dataFormEdit);

            if(validacionFormEdit){
                const actualizacion = actualizarItem(dataFormEdit);
                if(actualizacion){
                    clearTable('tbl_note_income_detail');
                    destruirDataTableCompraDetalle();
                    pintarTableCompraDetalle(lstNoteRelease);
                    iniciarDataTableCompraDetalle();
                    $('#mdlEditItem').modal('hide');
                    toastr.success('ITEM ACTUALIZADO');
                }
            }
            ocultarAnimacion1();

        })
    }

    function openMdlEditItem(){
        $('#mdlEditItem').modal('show');
    }

    function deleteItem(producto_id){

        const indiceProducto    =   lstNoteRelease.findIndex((lcd)=>{
            return lcd.product_id == producto_id;
        })

        if(indiceProducto === -1){
            toastr.error('NO SE ENCONTRÓ EL ITEM EN EL DETALLE!!!');
            return false;
        }

        lstNoteRelease.splice(indiceProducto,1);
        return true;

    }

    function actualizarItem(dataFormEdit){

        //======= GRABANDO =========
        const indiceProducto    =   lstNoteRelease.findIndex((lcd)=>{
            return lcd.product_id == product_edition.product_id;
        })

        if(indiceProducto === -1){
            toastr.error('NO SE ENCONTRÓ EL PRODUCTO A EDITAR');
            return false;
        }

        lstNoteRelease[indiceProducto].quantity   =   dataFormEdit.cantidad;
        return true;
    }

    function getDataFormEdit(){
        const cantidad  =   document.querySelector('#item_cantidad_edit').value;
        const data      =   {cantidad};
        return data;
    }

    function validarDataFormEdit(data){
        let validacion  =   false;

        if(data.cantidad === null){
            toastr.error('DEBE INGRESAR UNA CANTIDAD!!');
            return validacion;
        }

        if(data.cantidad == 0){
            toastr.error('LA CANTIDAD DEBE SER MAYOR A 0!!');
            return validacion;
        }

        return true;
    }

    function setProducto(producto_id){

        const productoIndice    =   lstNoteRelease.findIndex((lcd)=>{
            return lcd.product_id == producto_id;
        })

        if(productoIndice === -1){
            toastr.error('NO SE ENCUENTRA EL PRODUCTO EN EL DETALLE!!');
            return;
        }

        const producto_find   =   lstNoteRelease[productoIndice];

        document.querySelector('#item_nombre_edit').value   =   producto_find.product_name;   
        document.querySelector('#item_unidad_edit').value   =   'NIU';   
        document.querySelector('#item_cantidad_edit').value =   producto_find.quantity;   
        product_edition.product_id                          =   producto_find.product_id;

    }
</script>