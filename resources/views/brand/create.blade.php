<!-- Modal -->
<div class="modal fade" id="createBrandModal" tabindex="-1" aria-hidden="true">    
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            
            <form id="createBrandForm" action="{{ route('tenant.inventarios.productos.marca.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Registrar Marca</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group">
                            <label for="description" class="form-label">Nombre de la marca <span>*</span></label>
                            <input id="name" name="name"  type="text" class="form-control inputName" placeholder="Nombre de la categoría"  oninput="this.value = this.value.toUpperCase()">
                            <p hidden class="msgError error">error</p> 
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-info">
                        <i class="fas fa-info-circle"></i>
                        <p style="margin:0">Los campos marcados con asterisco (*) son obligatorios.</p>
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
            
        </div>
    </div>
</div>

