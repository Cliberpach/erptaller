<form action="" id="formRegistrarCotizacionCompra" method="post">    
    <div class="row">
        @csrf     
        
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                <label for="fecha_registro" class="required_field" style="font-weight: bold;">FECHA REGISTRO</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fa-solid fa-calendar-days"></i>
                    </span>
                    <input value="{{ date('Y-m-d') }}" readonly required id="fecha_registro" name="fecha_registro" type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1">
                </div>
            </div>
    
            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                <label for="proyecto" class="required_field" style="font-weight: bold;">REGISTRADOR</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fa-solid fa-diagram-project"></i>
                    </span>
                    <input value="{{$colaborador_registrador->name}}" readonly required id="proyecto" name="proyecto" type="text" class="form-control inputEnteroPositivo" placeholder="Proyecto" aria-label="Username" aria-describedby="basic-addon1">
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                <label for="observation" class="required_field" style="font-weight: bold;">OBSERVACIÓN</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-text-width"></i>
                    </span>
                    <div class="form-floating">
                        <textarea class="form-control" placeholder="Escribir..." id="observation" name="observation"></textarea>
                        <label for="observation">Máximo 200 caracteres</label>
                    </div>                
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mt-3 mb-3">
                <div class="card">
                    <div class="card-header" style="background-color: rgb(0, 102, 255);font-weight:bold;color:white;">
                    SELECCIONAR PRODUCTOS
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-lg-5 col-md-7 col-sm-12 col-xs-12">
                                <label for="categoria" style="font-weight: bold;">PRODUCTO</label>

                                <div class="input-group mb-3">
                                    <input id="producto" name="producto" readonly type="text" class="form-control" placeholder="Producto" aria-label="Recipient's username" aria-describedby="button-addon2">
                                    <button class="btn btn-primary" type="button" id="button-addon2" onclick="openMdlProducts()">
                                        <i class="fa-solid fa-magnifying-glass"></i> Buscar
                                    </button>
                                  </div>
                            </div>

                            <div class="col-lg-3 col-md-5 col-sm-12 col-xs-12">
                                <label for="categoria" style="font-weight: bold;">UNIDAD</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </span>
                                    <input id="unidad" name="unidad" readonly type="text" class="form-control" placeholder="Unidad" aria-label="Username" aria-describedby="basic-addon1">
                                  </div>
                            </div>

                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                                <label for="categoria" style="font-weight: bold;">CANTIDAD</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <i class="fa-solid fa-box-open"></i>                                    
                                    </span>
                                    <input id="cantidad" name="cantidad" type="text" class="form-control inputDecimalPositivo" placeholder="Cantidad" aria-label="Username" aria-describedby="basic-addon1">
                                  </div>
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-3 d-flex justify-content-end">
                                <button class="btn btn-primary btnAgregarProducto" type="button">
                                    <i class="fa-solid fa-cart-plus"></i> AGREGAR 
                                </button>
                            </div>
                        </div>

                        {{-- <div class="row mt-3">
                            <div class="col-12">
                               @include('logistica.registro_compra.tables.table_productos')
                            </div>
                        </div> --}}
                       
                    </div>
                </div>
            </div>
        </div>  
        
        <div class="row mt-3">
            <div class="col-12 mt-3 mb-3">
                <div class="card">
                    <div class="card-header" style="background-color: rgb(0, 102, 255);font-weight:bold;color:white;">
                    DETALLE DE LA NOTA DE SALIDA
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    @include('inventory.note_income.tables.tbl_note_income_detail')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

           
    </div>
</form> 