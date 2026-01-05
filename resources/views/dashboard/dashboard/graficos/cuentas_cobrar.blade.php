<style>
    #container_cuentas_cobrar {
    height: 400px;
}

.highcharts-figure,
.highcharts-data-table table {
    min-width: 310px;
    max-width: 800px;
    margin: 1em auto;
}

.highcharts-data-table table {
    font-family: Verdana, sans-serif;
    border-collapse: collapse;
    border: 1px solid #ebebeb;
    margin: 10px auto;
    text-align: center;
    width: 100%;
    max-width: 500px;
}

.highcharts-data-table caption {
    padding: 1em 0;
    font-size: 1.2em;
    color: #555;
}

.highcharts-data-table th {
    font-weight: 600;
    padding: 0.5em;
}

.highcharts-data-table td,
.highcharts-data-table th,
.highcharts-data-table caption {
    padding: 0.5em;
}

.highcharts-data-table thead tr,
.highcharts-data-table tbody tr:nth-child(even) {
    background: #f8f8f8;
}

.highcharts-data-table tr:hover {
    background: #f1f7ff;
}

.highcharts-description {
    margin: 0.3rem 10px;
}
</style>


<div class="row">
    <div class="col-12">
        <figure class="highcharts-figure">
            <div id="container_cuentas_cobrar"></div>
            <p class="highcharts-description">

            </p>
        </figure>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
        <label for="cuentas_cobrar_pendiente" style="font-weight: bold;">PENDIENTE</label>
        <div class="input-group">
            <span class="input-group-text" id="basic-addon1">
                <i class="fas fa-money-bill-wave"></i>
            </span>
            <input readonly id="cuentas_cobrar_pendiente" type="text" class="form-control colorVerde" placeholder="00.00" aria-label="Username" aria-describedby="basic-addon1">
        </div>
    </div>
    <div class="col-12">
        <div class="table-responsive">
            @include('dashboard.dashboard.tables.tbl_cuentas_cobrar')
        </div>
    </div>
</div>


<script>

/*
FORMATO DATOS:
[
    ['Norway', 16],
    ['Germany', 12],
    ['USA', 8],
    ['Sweden', 8],
    ['Netherlands', 8],
    ['ROC', 6],
    ['Austria', 7],
    ['Canada', 4],
    ['Japan', 3]
]
*/
    function setCuentasCobrar(titulo,subtitulo,datos,totales){
        Highcharts.chart('container_cuentas_cobrar', {
            chart: {
                type: 'pie',
                options3d: {
                    enabled: true,
                    alpha: 45
                }
            },
            title: {
                text: titulo
            },
            subtitle: {
                text: subtitulo
            },
            plotOptions: {
                pie: {
                    innerSize: 100,
                    size: '100%',
                    depth: 45,
                    dataLabels: {
                        enabled: true,
                        distance: 30,
                        formatter: function() {
                            return `${this.point.name}:  S/${Highcharts.numberFormat(this.point.y, 2)}`;
                        },
                        style: {
                            fontSize: '11px',
                            textOutline: 'none',
                            color: '#000000'
                        },
                        useHTML: false
                    }
                }
            },
            series: [{
                name: 'Monto',
                data: datos
            }],
            exporting: {
                enabled: true,
                //showTable: true
            },
            lang:getLang()
        });

        removeCreditos();

        destroyDataTable(dtCuentasCobrar);
        clearTable('tbl_cuentas_cobrar')
        pintarTblCuentasCobrar(datos,totales);
        dtCuentasCobrar  =   loadDataTableSimple('tbl_cuentas_cobrar');

        setSaldoCuentasCobrar(totales);
    }

    function setSaldoCuentasCobrar(totales){
        document.querySelector('#cuentas_cobrar_pendiente').value   =   totales.total;
    }

    function pintarTblCuentasCobrar(datos,totales){
        const tbody         =   document.querySelector('#tbl_cuentas_cobrar tbody');
        let filas           =   '';

        datos.forEach(cuenta => {
           filas    +=  `
                        <tr>
                            <td>${cuenta[0]}</td>
                            <td>${cuenta[1]}</td>
                        </tr>
                        `;
        });

        tbody.innerHTML =   filas;
    }
</script>
