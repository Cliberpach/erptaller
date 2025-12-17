
document.addEventListener('DOMContentLoaded', () => {
    eventsUtils();
})

function eventsUtils() {

    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('inputDecimalPositivo')) {

            const input = e.target;

            // Reemplaza cualquier carácter que no sea un dígito o un punto decimal
            let value = input.value.replace(/[^0-9.]/g, '');

            // Asegúrate de que el punto decimal no esté al inicio
            if (value.startsWith('.')) {
                value = value.slice(1);
            }

            // Permite solo un punto decimal y limita a dos decimales
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            if (parts.length === 2) {
                parts[1] = parts[1].slice(0, 2); // Limita a dos decimales
                value = parts.join('.');
            }

            // Actualiza el valor del input
            input.value = value;
        }

        if (e.target.classList.contains('inputDecimalPositivoLibre')) {
            const input = e.target;

            // Reemplaza cualquier carácter que no sea un dígito o un punto decimal
            let value = input.value.replace(/[^0-9.]/g, '');

            // Asegúrate de que el punto decimal no esté al inicio
            if (value.startsWith('.')) {
                value = value.slice(1);
            }

            // Permite solo un punto decimal
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            // Actualiza el valor del input
            input.value = value;
        }

        if (e.target.classList.contains('inputDecimal')) {
            const input = e.target;

            // Reemplaza cualquier carácter que no sea un dígito, un punto decimal o un signo negativo al inicio
            let value = input.value.replace(/[^0-9.-]/g, '');

            // Asegúrate de que el signo negativo esté al inicio si existe
            if (value.includes('-')) {
                value = '-' + value.replace(/-/g, ''); // Mueve el signo negativo al inicio y remueve los demás
            }

            // Asegúrate de que el punto decimal no esté al inicio, a menos que sea después del signo negativo
            if (value.startsWith('.') || value.startsWith('-.')) {
                value = value.slice(1);
            }

            // Permite solo un punto decimal y limita a dos decimales
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            if (parts.length === 2) {
                parts[1] = parts[1].slice(0, 2); // Limita a dos decimales
                value = parts.join('.');
            }

            // Actualiza el valor del input
            input.value = value;
        }

        if (e.target.classList.contains('inputEnteroPositivo')) {
            const input = e.target;

            // Reemplaza cualquier carácter que no sea un dígito
            let value = input.value.replace(/[^0-9]/g, '');

            // Asegúrate de que no empiece con ceros
            if (value.startsWith('0')) {
                value = value.replace(/^0+/, ''); // Elimina los ceros iniciales
            }

            // Si el campo se vacía por completo, mantenerlo vacío
            if (value === '') {
                value = '';
            }

            // Actualiza el valor del input
            input.value = value;
        }

        if (e.target.classList.contains('inputNroTelefono')) {
            const input = e.target;

            // Reemplaza cualquier carácter que no sea un dígito, sin tocar los ceros iniciales
            input.value = input.value.replace(/[^0-9]/g, '');
        }



    })

}

//============== LIMPIAR UNA TABLA ========
function clearTable(idTabla) {
    const tbody = document.querySelector(`#${idTabla} tbody`);
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }
}

function destroyDataTable(dtTable) {
    if (dtTable) {
        dtTable.destroy();
        dtTable = null;
    }
    return dtTable;
}

function mostrarAnimacion1() {
    document.getElementById('overlay_1').style.display = 'flex';
}

function ocultarAnimacion1() {
    document.getElementById('overlay_1').style.display = 'none';
}

//=========== OBTENER FILA POR EL ID DE UN DATATABLE =========
function getRowById(dtTabla, registro_id) {
    let data = dtTabla.rows().data();
    let rowData = null;

    for (let i = 0; i < data.length; i++) {
        if (data[i].id == registro_id) {
            rowData = data[i];
            break;
        }
    }

    return rowData;
}

//======== OBTENER FILA POR EL INDEX DEL DATATABLE ========
function getRowByIndex(dtTabla, index) {

    if (index < 0 || index >= dtTabla.rows().count()) {
        return null;
    }

    let data = dtTabla.rows().data();

    return data[index];
}

//======= LIMPIAR ERRORES DE VALIDACIÓN ========
function clearValidationErrors(error_class) {
    const lstTagErrors = document.querySelectorAll(`.${error_class}`);
    lstTagErrors.forEach((tag) => {
        tag.textContent = '';
    })
}


function paintValidationErrors(objValidationErrors, suffix) {

    for (let clave in objValidationErrors) {
        const pError = document.querySelector(`.${clave}_${suffix}`);
        pError.textContent = objValidationErrors[clave][0];
    }

}

function loadDataTableSimple(id) {
    const dtTable = new DataTable(`#${id}`, {
        language: {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "emptyTable": "No hay datos disponibles en la tabla",
            "aria": {
                "sortAscending": ": activar para ordenar la columna de manera ascendente",
                "sortDescending": ": activar para ordenar la columna de manera descendente"
            }
        }
    });

    return dtTable;
}

function loadDataTableResponsive(id) {
    const dtTable = new DataTable(`#${id}`, {
        responsive: true,
        language: {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "emptyTable": "No hay datos disponibles en la tabla",
            "aria": {
                "sortAscending": ": activar para ordenar la columna de manera ascendente",
                "sortDescending": ": activar para ordenar la columna de manera descendente"
            }
        }
    });

    return dtTable;
}

function formatQuantity(valor) {
    if (valor === null || valor === undefined || valor === '') return '0';

    let numero = parseFloat(valor);

    if (isNaN(numero)) return '0';

    return numero.toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}


function formatMoney(valor) {
    return parseFloat(valor).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatSoles(valor) {
    const num = parseFloat(valor);

    if (isNaN(num)) return valor;

    return num.toLocaleString('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function redirect(routeName) {
    window.location.href = route(routeName);
}

function redirectParams(routeName, id) {
    window.location.href = route(routeName, id);
}

function isNumeric(value) {
    return Number.isInteger(Number(value));
}

function setText(selectInstance, text) {
    const option = selectInstance.options;
    for (let key in option) {
        if (option[key].description.trim() === text.trim()) {
            selectInstance.setValue(option[key].id);
            break;
        }
    }
}
