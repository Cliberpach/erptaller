<!-- Modal -->
<div class="modal fade" id="editBrandModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <form id="editBrandForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Editar Marca</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group">
                            <label for="name" class="form-label">Nombre de la marca <span>*</span></label>
                            <input id="name" name="name" type="text" class="form-control inputName" placeholder="Nombre de la categoría" oninput="this.value = this.value.toUpperCase()">
                            <span class="text-danger" id="nameError"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-info">
                        <i class="fas fa-info-circle"></i>
                        <p style="margin:0">Los campos marcados con asterisco (*) son obligatorios.</p>
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>

        </div>
    </div>
</div>


<script>
document.getElementById('editBrandForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Evitar el envío tradicional del formulario
    const form = event.target;
    const formData = new FormData(form);

    // Limpiar errores anteriores
    document.getElementById('nameError').textContent = '';

    fetch(form.action, {
        method: 'POST', // Asegúrate de que el método sea 'POST' o 'PUT' según sea necesario
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.type === 'error') {
            // Mostrar errores de validación en tiempo real dentro del modal
            if (data.errors && data.errors.name) {
                document.getElementById('nameError').textContent = data.errors.name[0];
            }
        } else {
            // Almacenar el mensaje de éxito en localStorage
            localStorage.setItem('successMessage', 'Categoría actualizada exitosamente.');

            // Cerrar el modal y recargar la página para mostrar los cambios
            $('#editCategoryModal').modal('hide');
            window.location.reload(); // Recargar la página
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

// Mostrar el mensaje de éxito después de la recarga si existe
document.addEventListener('DOMContentLoaded', function() {
    const successMessage = localStorage.getItem('successMessage');
    if (successMessage) {
        const messagesContainer = document.getElementById('messages');
        messagesContainer.innerHTML = `
            <div class="alert alert-success">
                ${successMessage}
                <div class="progress-bar"></div>
            </div>
        `;

        // Iniciar la barra de progreso para desaparecer el mensaje
        const progressBar = messagesContainer.querySelector('.progress-bar');
        setTimeout(() => {
            progressBar.style.width = '0%';
        }, 100);

        setTimeout(() => {
            messagesContainer.innerHTML = '';
        }, 5000);

        // Limpiar el mensaje de localStorage
        localStorage.removeItem('successMessage');
    }
});


</script>

