{{-- Cierre de caja: confirmación simple (sin modal consolidado). El consolidado vive en la
     acción PDF del menú (pestaña nueva, tenant.movimientos_caja.pdf). closePettyCash recomputa
     el cuadre server-side (Capa 6, restando NC) y guarda closing_amount; acá solo se confirma
     y se manda el id -> el cambio es puramente de UI. --}}
<script>
    const paramsMdlCloseCash = {
        id: null
    };

    // Acción "Cerrar caja" del menú ⋮: confirma y cierra, sin abrir el consolidado.
    function cerrarCaja(id) {
        paramsMdlCloseCash.id = id;
        closeCash();
    }

    function closeCash() {

        Swal.fire({
            title: "¿Desea cerrar caja?",
            text: "Esta acción la cierra definitivamente.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí",
            cancelButtonText: "No",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: "Cerrando caja...",
                    text: "Por favor, espere",
                    icon: "info",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    toastr.clear();

                    const formData = new FormData();
                    formData.append('id', paramsMdlCloseCash.id);

                    const res = await axios.post(route('tenant.movimientos_caja.closePettyCash',
                            paramsMdlCloseCash.id),
                        formData);

                    if (res.data.success) {
                        toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                        dtCash.ajax.reload();
                    } else {
                        Swal.close();
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    if (error.response) {
                        if (error.response.status === 422) {
                            const errors = error.response.data.errors;
                            paintValidationErrors(errors, 'error');
                            Swal.close();
                            toastr.error('Errores de validación encontrados.', 'ERROR DE VALIDACIÓN');
                        } else {
                            Swal.close();
                            toastr.error(error.response.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } else if (error.request) {
                        Swal.close();
                        toastr.error('No se pudo contactar al servidor. Revisa tu conexión a internet.',
                            'ERROR DE CONEXIÓN');
                    } else {
                        Swal.close();
                        toastr.error(error.message, 'ERROR DESCONOCIDO');
                    }
                } finally {
                    Swal.close();
                }

            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire({
                    title: "Operación cancelada",
                    text: "No se realizaron acciones",
                    icon: "error"
                });
            }
        });
    }
</script>
