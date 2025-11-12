<!-- Modal product -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <hr>
        <div class="modal-body">
            
                <form class="row" id="my-form" action="">
                    <div class="col-lg-6 col-md-6">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 mb-3">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" class="form-control name" id="name" aria-describedby="emailHelp" oninput="this.value = this.value.toUpperCase()">
                                <p class="msgError productData.name"></p>
                            </div>
                            <div class="col-lg-12 col-md-12 mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <input type="text" class="form-control description" id="description" aria-describedby="emailHelp" oninput="this.value = this.value.toUpperCase()"> 
                            </div>
                            <div class="col-lg-6 col-md-6 mb-3">
                                <label for="sale-price" class="form-label">Precio venta</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                    <input type="number" step="0.01" min="0" class="form-control sale-price" id="sale-price" aria-label="Amount (to the nearest dollar)">    
                                </div>
                                <p  class="msgError productData.sale_price"></p>
                            </div>
                            <div class="col-lg-6 col-md-6 mb-3">
                                <label for="purchase-price" class="form-label">Precio compra</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                    <input type="number" step="0.01" min="0" class="form-control purchase-price" id="purchase-price" aria-label="Amount (to the nearest dollar">
                                </div>
                                <p  class="msgError productData.purchase_price"></p>
                            </div>
                            <div class="col-lg-6 col-md-6 mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control stock" min="0" id="stock" aria-describedby="emailHelp" placeholder="STOCK"> 
                                <p  class="msgError productData.stock"></p>
                            </div>
                            <div class="col-lg-6 col-md-6 mb-3">
                                <label for="stock_min" class="form-label">Stock mínimo</label>
                                <input type="number" class="form-control stock_min" min="0" id="stock_min" placeholder="STOCK MÍNIMO" aria-label="Username" aria-describedby="basic-addon1">
                                <p  class="msgError productData.stock_min"></p>
                            </div>
                            <div class="col-lg-12 col-md-12 mb-3">
                                <label for="factorycode" class="form-label">Cod Fábrica</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-industry"></i></span>
                                    <input type="number" class="form-control factorycode" id="factorycode" aria-label="Amount (to the nearest dollar)">    
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12">
                                <label for="barcode" class="form-label">Cod Barras</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    <input type="number" class="form-control barcode" id="barcode" aria-label="Amount (to the nearest dollar)">    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                       <div class="row">
                        <div class="col-lg-12 col-md-12 mb-3">
                            <label for="category" class="form-label">Categoría</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tags"></i></span>
                                    
                                    <select style="text-transform: uppercase;" id="category" class="form-select category" aria-label="Default select example">
                                        <option value="0" selected>SELECCIONAR CATEGORÍA</option>
                                        @foreach ($categories as $category)
                                            <option style="text-transform: uppercase;" value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                    </select>
                                    <a onclick="openCreateCategoryModal()" class="btn btn-success">
                                        <i class="fas fa-plus-circle"></i>
                                    </a>
                                </div>
                                <p  class="msgError productData.category_id"></p>
                        </div>
                        <div class="col-lg-12 col-md-12 mb-3">
                            
                                <label for="brand" class="form-label">Marca</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-th-list"></i></span>
                                    <select style="text-transform: uppercase;" id="brand" class="form-select brand" aria-label="Default select example">
                                        <option value="0" selected>SELECCIONAR MARCA</option>
                                        @foreach ($brands as $brand)
                                            <option style="text-transform: uppercase;" value="{{$brand->id}}">{{$brand->name}}</option>
                                        @endforeach
                                    </select>
                                    <a onclick="openCreateBrandModal()" class="btn btn-success">
                                        <i class="fas fa-plus-circle"></i>
                                    </a>
                                </div>
                                <p  class="msgError productData.brand_id"></p>
                        </div>
                        <div class="col-lg-12 col-md-12">
                            <div class="mb-3">
                                <label for="input-image" class="form-label">IMAGEN</label>
                                <input class="form-control image" accept="image/*" type="file" id="input-image">
                            </div>
                            <div  class="container-img">
                                <img src="{{asset('assets/img/products/img_default.png')}}" id="preview-image" alt="img-product">
                            </div>
                            <div class="d-flex justify-content-between">
                                <p class="nameImage"></p>
                                <a class="delete-image" href="javascript:void(0);">Quitar imagen</a>
                            </div>
                        </div>
                       </div>
                    </div>
                </form>
           
        </div>
        <hr>
        <div class="modal-footer">
            <div class="col-info">
                <i class="fas fa-info-circle"></i>
                <p style="margin:0">Los campos marcados con asterisco (*) son obligatorios.</p>
            </div>
            <div class="col-buttons">
                <button  type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">
                    <i style="margin-right: 3px;" class="fas fa-window-close"></i>Cancelar
                </button>
                <button type="submit" form="my-form"
                class="btn btn-primary btn-save" data-bs-dismiss="modal">
                    <i style="margin-right: 3px;" class="fas fa-save"></i>Guardar
                </button>
            </div>
        </div>
      </div>
    </div>
  </div>

  @include('brand.create')
  @include('category.create')
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    
    function openCreateBrandModal() {
        $('#createBrandModal').modal('toggle');
    }

    function openCreateCategoryModal() {
        $('#createCategoryModal').modal('toggle');
    }

     // Enviar formulario de categoría vía AJAX
     $('#createCategoryForm').on('submit', function(e) {
    e.preventDefault(); // Evitar el envío tradicional del formulario

    let formData = $(this).serialize(); // Obtener los datos del formulario
    $.ajax({
        type: 'POST',
        url: '{{ route("tenant.inventarios.productos.categoria.store") }}',
        data: formData,
        success: function(response) {
            console.log('Respuesta completa:', response); // Añade esto para ver la respuesta completa

            if (response.type === 'success') {
                // Asegúrate de que response.data y response.data.name existen
                if (response.data && response.data.name) {
                    // Agregar la nueva categoría al select de categorías
                    $('#category').append(new Option(response.data.name, response.data.id, true, true));
                    // Cerrar el modal de creación de categoría
                    $('#createCategoryModal').modal('hide');
                } else {
                    console.error('La respuesta no contiene los datos esperados:', response);
                    alert('Hubo un problema al agregar la categoría. Respuesta inesperada del servidor.');
                }
            } else {
                alert('Hubo un error al agregar la categoría.');
            }
        },
        error: function(error) {
            console.error('Error:', error);
            alert('Hubo un problema al agregar la categoría.');
        }
    });
});


    // Enviar formulario de marca vía AJAX
    $('#createBrandForm').on('submit', function(e) {
        e.preventDefault();

        let formData = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: '{{ route("tenant.inventarios.productos.marca.store") }}', // Asegúrate de que la ruta esté correctamente definida
            data: formData,
            success: function(response) {
                if (response.type === 'success') {
                    // Agregar la nueva marca al select
                    $('#brand').append(new Option(response.data.name, response.data.id, true, true));
                    $('#createBrandModal').modal('hide'); // Cerrar modal de marca
                } else {
                    alert('Hubo un error al agregar la marca.');
                }
            },
            error: function(error) {
                console.error('Error:', error);
                alert('Hubo un problema al agregar la marca.');
            }
        });
    });
  </script>


