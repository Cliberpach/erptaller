<table class="table-hover table-striped display nowrap table" width="100%" id="tbl_stock_min">
    <thead>
        <tr>
            <th scope="col"></th>
            <th scope="col" data-priority="1">#</th>
            <th scope="col" data-priority="2">PRODUCTO</th>
            <th scope="col" data-priority="3">CATEGORÍA</th>
            <th scope="col">MARCA</th>
            <th scope="col">STOCK</th>
            <th scope="col">STOCK MIN</th>
            <th scope="col">UNIDAD</th>
            <th scope="col">COD</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>

<script>
    let dtProductos = null;

    function iniciarDataTableProductos() {
        const urlGetProductos = '{{ route('tenant.dashboard.dashboard.getStockMin') }}';

        dtProductos = new DataTable('#tbl_stock_min', {
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetProductos,
                type: 'GET',
                data: function(d) {
                    d.categoria_id = $('#categoria').val();
                    d.marca_id = $('#marca').val();
                    d.establecimiento = $('#select_establecimiento').val();
                }
            },
            columns: [{
                    data: null,
                    className: 'dt-control',
                    orderable: false,
                    searchable: false,
                    defaultContent: '',
                    title: '',
                    width: '30px'
                },
                {
                    data: 'id',
                    name: 'p.id',
                    searchable: false,
                    orderable: true
                },
                {
                    data: 'name',
                    name: 'p.name',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'category_name',
                    name: 'c.name'
                },
                {
                    data: 'brand_name',
                    name: 'b.name'
                },
                {
                    data: 'stock',
                    name: 'stock',
                    className: 'text-end'
                },
                {
                    data: 'stock_min',
                    name: 'p.stock_min',
                    className: 'text-end'
                },
                {
                    data: 'unit_symbol',
                    name: 'p.unit_symbol'
                },
                {
                    data: 'code_bar',
                    name: 'p.code_bar',
                    visible: false
                }
            ],

            pageLength: 25,
            lengthChange: false,
            dom: '<"row mb-3"<"col-md-6 d-flex align-items-center"f>>t<"row"<"col-6"i><"col-6"p>>',

            language: {
                "lengthMenu": "Mostrar _MENU_ productos por página",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ productos",
                "infoEmpty": "Mostrando 0 a 0 de 0 productos",
                "infoFiltered": "(filtrado de _MAX_ productos totales)",
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
        if (inputSearchDataTable) {
            inputSearchDataTable.style.width = '500px';
            inputSearchDataTable.style.height = '50px';
            inputSearchDataTable.style.textAlign = 'left';
            inputSearchDataTable.placeholder = 'Buscar producto...';

            const previousSibling = inputSearchDataTable.previousElementSibling;
            if (previousSibling) {
                previousSibling.style.display = 'none';
            }
        }
    }
</script>
