<div class="modal fade" id="createSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="createSupplierForm" action="{{ route('tenant.supplier.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Registrar proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 col-12 mb-3">
                            <label for="document_number" class="form-label">Tipo de documento</label>
                            <select name="identity_document" id="identity_document" class="form-control text-center">
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                            </select>
                        </div>
                        <div class="col-md-8 col-12 mb-3">
                            <label for="document_number" class="form-label">DNI/RUC (*)</label>
                            <input type="text" id="document_number" name="document_number" class="form-control"
                                placeholder="Ingrese DNI/RUC">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-12 mb-0">
                            <label for="name" class="form-label">Nombre (*)</label>
                            <input type="text" id="name" name="name" class="form-control"
                                placeholder="Ingrese nombre">
                        </div>
                        <div class="col-12 mb-0">
                            <label for="address" class="form-label">Dirección</label>
                            <input type="text" id="address" name="address" class="form-control"
                                placeholder="Ingrese dirección">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
