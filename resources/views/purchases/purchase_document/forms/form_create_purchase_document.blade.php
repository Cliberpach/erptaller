<form action="" id="formStorePurchaseDocument" method="post">    
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
                <label for="fecha_registro" class="required_field" style="font-weight: bold;">FECHA ENTREGA</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fa-solid fa-calendar-days"></i>
                    </span>
                    <input value="{{ date('Y-m-d') }}"  required id="fecha_entrega" name="fecha_entrega" type="date" class="form-control" aria-label="Username" aria-describedby="basic-addon1">
                </div>
            </div>
    
            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                <label for="usuario" class="required_field" style="font-weight: bold;">REGISTRADOR</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fa-solid fa-diagram-project"></i>
                    </span>
                    <input value="{{$colaborador_registrador->name}}" readonly required id="usuario" name="usuario" type="text" class="form-control inputEnteroPositivo" placeholder="usuario" aria-label="Username" aria-describedby="basic-addon1">
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-3">
                <label class="required_field" for="proveedor" style="font-weight: bold;">PROVEEDOR</label> <i class="fa-solid fa-plus btn btn-primary" onclick="openMdlNuevoProveedor();"></i>
                <select required name="proveedor" id="proveedor" data-placeholder="Seleccionar" class="select2_form">
                    @foreach ($suppliers as $supplier)
                        <option value="{{$supplier->id}}">{{$supplier->type_document_abbreviation.':'.$supplier->document_number.'-'.$supplier->name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-3">
                <label class="required_field" for="tipo_doc" style="font-weight: bold;">TIPO DOC</label>
                <select required name="tipo_doc" id="tipo_doc" data-placeholder="Seleccionar" class="select2_form">
                    <option value=""></option>
                    <option value="FACTURA">FACTURA</option>
                    <option value="BOLETA">BOLETA</option>
                </select>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                <label for="igv_chk" style="font-weight: bold;">IGV</label>
                <div class="form-check">
                    <input checked id="igv_chk" name="igv_chk" class="form-check-input"  type="checkbox" value="{{$igv}}">
                    <label class="form-check-label" for="flexCheckDefault">
                        {{ number_format($igv, 2) }}%
                    </label>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                <label class="required_field" for="serie" style="font-weight: bold;">SERIE</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fa-solid fa-envelopes-bulk"></i>
                    </span>
                    <input required id="serie" name="serie" type="text" class="form-control" placeholder="Serie" aria-label="Username" aria-describedby="basic-addon1">
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-3">
                <label class="required_field" for="numero" style="font-weight: bold;">N°</label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fa-solid fa-hashtag"></i>
                    </span>
                    <input required id="numero" name="numero" type="text" class="form-control inputEnteroPositivo" placeholder="Número" aria-label="Username" aria-describedby="basic-addon1">
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-3">
                <label for="observation" style="font-weight: bold;">OBSERVACIÓN</label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-text-width"></i>
                    </span>
                    <div class="form-floating">
                        <textarea class="form-control" placeholder="Escribir..." id="observation" name="observation"></textarea>
                        <label for="observation">Máximo 200 caracteres</label>
                    </div>                
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-3" style="display: none;">
                <label class="required_field" for="moneda" style="font-weight: bold;">MONEDA</label>
                <select name="moneda" id="moneda" data-placeholder="Seleccionar" class="select2_form">
                    <option value=""></option>
                    <option value="PEN" selected >SOLES</option>
                    <option value="USD">DÓLARES</option>
                </select>
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

                            {{-- <div class="col-lg-3 col-md-5 col-sm-12 col-xs-12">
                                <label for="categoria" style="font-weight: bold;">UNIDAD</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </span>
                                    <input id="unidad" name="unidad" readonly type="text" class="form-control" placeholder="Unidad" aria-label="Username" aria-describedby="basic-addon1">
                                  </div>
                            </div> --}}

                            <div class="col-lg-3 col-md-5 col-sm-12 col-xs-12">
                                <label for="precio" style="font-weight: bold;">PRECIO</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <i class="fa-solid fa-money-bill-1-wave"></i>                                    
                                    </span>
                                    <input id="precio" name="precio" type="text" class="form-control inputDecimalPositivo" placeholder="Precio" aria-label="Username" aria-describedby="basic-addon1">
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
                    </div>
                </div>
            </div>
        </div>  
        
        <div class="row mt-3">
            <div class="col-12 mt-3 mb-3">
                <div class="card">
                    <div class="card-header" style="background-color: rgb(0, 102, 255);font-weight:bold;color:white;">
                    DETALLE DE LA COMPRA
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    @include('purchases.purchase_document.tables.tbl_purchase_document_detail')
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-end">
                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                                
                                @include('purchases.purchase_document.tables.tbl_purchase_document_amounts')
                                   
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


           
    </div>
</form> 