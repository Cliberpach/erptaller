<style>
 .highcharts-figure,
.highcharts-data-table table {
    min-width: 320px;
    max-width: 800px;
    margin: 1em auto;
}

#container {
    height: 450px;
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


<figure class="highcharts-figure">
    <div id="container_ventas_mes"></div>
    <p class="highcharts-description">
       
    </p>
</figure>

<script>

    //=== data: [10,20,30,40,50,60,70,80,90,100,110,120] ====;
    function setVentasVsComprasAnio(titulo,subtitulo,datos){
        Highcharts.chart('container_ventas_mes', {
            chart: {
                type: 'area'
            },
            accessibility: {
                description: 'Image description: An area chart compares the nuclear ' +
                    'stockpiles of the USA and the USSR/Russia between 1945 and ' +
                    '2024. The number of nuclear weapons is plotted on the Y-axis ' +
                    'and the years on the X-axis. The chart is interactive, and the ' +
                    'year-on-year stockpile levels can be traced for each country. ' +
                    'The US has a stockpile of 2 nuclear weapons at the dawn of the ' +
                    'nuclear age in 1945. This number has gradually increased to 170 ' +
                    'by 1949 when the USSR enters the arms race with one weapon. At ' +
                    'this point, the US starts to rapidly build its stockpile ' +
                    'culminating in 31,255 warheads by 1966 compared to the USSR’s 8,' +
                    '400. From this peak in 1967, the US stockpile gradually ' +
                    'decreases as the USSR’s stockpile expands. By 1978 the USSR has ' +
                    'closed the nuclear gap at 25,393. The USSR stockpile continues ' +
                    'to grow until it reaches a peak of 40,159 in 1986 compared to ' +
                    'the US arsenal of 24,401. From 1986, the nuclear stockpiles of ' +
                    'both countries start to fall. By 2000, the numbers have fallen ' +
                    'to 10,577 and 12,188 for the US and Russia, respectively. The ' +
                    'decreases continue slowly after plateauing in the 2010s, and in ' +
                    '2024 the US has 3,708 weapons compared to Russia’s 4,380.'
            },
            title: {
                text: titulo
            },
            subtitle: {
                text: subtitulo
            },
            xAxis: {
                categories:["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"],
                allowDecimals: false,
                accessibility: {
                    rangeDescription: 'Range: 1 to 12.'
                }
            },
            yAxis: {
                title: {
                    text: 'MONTO'
                }
            },
            tooltip: {
                formatter: function () {
                    let accion = this.series.name.includes('VENTA') ? 'vendieron' : 'compraron';
                    return `Se ${accion} <b>${Highcharts.numberFormat(this.y, 2, '.', ',')}</b> soles en ${this.key}`;
                }
            },
            plotOptions: {
                area: {
                    marker: {
                        enabled: false,
                        symbol: 'circle',
                        radius: 2,
                        states: {
                            hover: {
                                enabled: true
                            }
                        }
                    }
                }
            },
            series: [
                {
                    name: 'MONTO VENTA',
                    data: datos.ventas
                },
                {
                    name: 'MONTO COMPRA',
                    data: datos.compras
                }
            ]
        });

        removeCreditos();
    }
</script>