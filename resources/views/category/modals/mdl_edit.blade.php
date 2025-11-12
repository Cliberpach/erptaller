<!-- Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Editar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('category.forms.form_edit')
            </div>
            <div class="modal-footer">
                <div class="col-info">
                    <i class="fas fa-info-circle"></i>
                    <p style="margin:0">Los campos marcados con asterisco (*) son obligatorios.</p>
                </div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="editCategoryForm" class="btn btn-primary">Guardar</button>
            </div>

        </div>
    </div>
</div>


<script>
    const parameters = {
        id: null,
        row: null
    };

    function openMdlEdit(id) {
        if (!id) {
            toastr.error('FALTA EL PARÁMETRO ID CATEGORY');
            return;
        }
        const row = getRowById(dtCategories, id);
        if (!row) {
            toastr.error('CATEGORÍA NO ENCONTRADA');
            return;
        }
        parameters.id   =   id;
        parameters.row  =   row;
        document.querySelector('#name_edit').value = row.name;
        $('#editCategoryModal').modal('show');
    }

    function eventsMdlEdit() {
        document.querySelector('#editCategoryModal').addEventListener('submit', (e) => {
            e.preventDefault();
            updateCategory(e.target);
        })

        $('#editCategoryForm').on('hidden.bs.modal', function(e) {
            const editCategoryForm = document.querySelector('#editCategoryForm');
            editCategoryForm.reset();
            clearValidationErrors('msgError');
            parameters.id   =   null;
            parameters.row  =   null;
        });
    }

    function updateCategory(form) {

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA ACTUALIZAR LA CATEGORÍA?",
            text: `Categoría: ${parameters.row.name}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, ACTUALIZAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {


                Swal.fire({
                    title: 'Cargando...',
                    html: 'Actualizando categoría...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {

                    clearValidationErrors('msgError_edit');
                    const token             =   document.querySelector('input[name="_token"]').value;
                    const formData          =   new FormData(form);
                    let url                 =   `{{ route('tenant.inventarios.productos.categoria.update', ['id' => ':id']) }}`;
                    url                     =   url.replace(':id', parameters.id);

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-HTTP-Method-Override': 'PUT'
                        },
                        body: formData
                    });

                    const res = await response.json();

                    console.log(res);

                    if (response.status === 422) {
                        if ('errors' in res) {
                            paintValidationErrors(res.errors, 'error');
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {
                        dtCategories.draw();
                        $('#editCategoryModal').modal('hide');
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        Swal.close();
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ACTUALIZAR CATEGORÍA');
                    Swal.close();
                }


            } else if (result.dismiss === Swal.DismissReason.cancel) {
                swalWithBootstrapButtons.fire({
                    title: "OPERACIÓN CANCELADA",
                    text: "NO SE REALIZARON ACCIONES",
                    icon: "error"
                });
            }
        });
    }
</script>
