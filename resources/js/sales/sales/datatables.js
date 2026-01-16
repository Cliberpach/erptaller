import { routes } from "./routes";
import { setDtProducts } from "./states";

export function iniciarDataTableProductos() {
    const url = routes.getProducts;

    const dtProducts = new DataTable('#tbl_products', {
        serverSide: true,
        processing: true,
        ajax: {
            url: url,
            type: 'GET',
            data: function (d) {
                d.categoria_id = $('#categoria').val();
                d.marca_id = $('#marca').val();
            }
        },
        columns: [{
            data: null,
            render: function (data, type, row) {

                return `
                            <i data-id="${data.id}" class="fas fa-plus btnAdd btn btn-primary" ></i>
                        `;
            },
            name: 'actions',
            orderable: false,
            searchable: false
        },
        {
            data: 'id',
            name: 'id'
        },
        {
            data: 'name',
            name: 'name'
        },
        {
            data: 'category_name',
            name: 'category_name'
        },
        {
            data: 'brand_name',
            name: 'brand_name'
        },
        {
            data: 'sale_price',
            name: 'sale_price'
        },
        {
            data: 'stock',
            name: 'stock'
        },
        {
            data: 'code_bar',
            name: 'code_bar'
        },
        ],
        pageLength: 25,
        lengthChange: false,
        dom: '<"row mb-3"<"col-12"f>>t<"row"<"col-6"i><"col-6"p>>',
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

    const inputSearchDataTable = document.querySelector('#dt-search-0');
    const previousSibling = inputSearchDataTable.previousElementSibling;
    inputSearchDataTable.style.width = '100%';
    inputSearchDataTable.placeholder = 'Buscar producto';
    previousSibling.style.display = 'none';

    setDtProducts(dtProducts);

}
