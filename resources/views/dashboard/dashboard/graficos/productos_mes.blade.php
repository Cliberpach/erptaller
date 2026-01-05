
<style>
    * {
    font-family:
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        Roboto,
        Helvetica,
        Arial,
        "Apple Color Emoji",
        "Segoe UI Emoji",
        "Segoe UI Symbol",
        sans-serif;
}

#container {
    height: 400px;
}

.highcharts-figure,
.highcharts-data-table table {
    min-width: 310px;
    max-width: 800px;
    margin: 1em auto;
}

#sliders {
    margin: 0.3rem 10px;
}

#sliders td input[type="range"] {
    display: inline;
}

#sliders td {
    padding-right: 1em;
    white-space: nowrap;
}

.highcharts-description {
    margin: 0.3rem 10px;
}
</style>


<figure class="highcharts-figure">
    <div id="container"></div>
    <p class="highcharts-description">
       
    </p>
    <div id="sliders">
        <table>
            <tr>
                <td><label for="alpha">Alpha Angle</label></td>
                <td><input id="alpha" type="range" min="0" max="45" value="15"/> <span id="alpha-value" class="value"></span></td>
            </tr>
            <tr>
                <td><label for="beta">Beta Angle</label></td>
                <td><input id="beta" type="range" min="-45" max="45" value="15"/> <span id="beta-value" class="value"></span></td>
            </tr>
            <tr>
                <td><label for="depth">Depth</label></td>
                <td><input id="depth" type="range" min="20" max="100" value="50"/> <span id="depth-value" class="value"></span></td>
            </tr>
        </table>
    </div>
</figure>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-3d.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>

    //===== FORMATO DE DATA ======
    /*
    data: [
                    ['Toyota', 1795],
                    ['Volkswagen', 1242],
                    ['Volvo', 1074],
                    ['Tesla', 832],
                    ['Hyundai', 593],
                    ['MG', 509],
                    ['Skoda', 471],
                    ['BMW', 442],
                    ['Ford', 385],
                    ['Nissan', 371]
        ],
    */
    function setProductosMes(titulo,subtitulo,datos){
        const chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container',
                type: 'column',
                options3d: {
                    enabled: true,
                    alpha: 15,
                    beta: 15,
                    depth: 50,
                    viewDistance: 25
                }
            },
            xAxis: {
                type: 'category'
            },
            yAxis: {
                title: {
                    enabled: false
                }
            },
            tooltip: {
                headerFormat: '<b>{point.key}</b><br>',
                pointFormat: 'Cant: {point.y}'
            },
            title: {
                text: titulo
            },
            subtitle: {
                text: subtitulo
            },
            legend: {
                enabled: false
            },
            plotOptions: {
                column: {
                    depth: 25
                }
            },
            series: [{
                data: datos,
                colorByPoint: true
            }],
            lang:getLang()
        });

        document.querySelectorAll(
            '#sliders input'
        ).forEach(input => input.addEventListener('input', e => {
            chart.options.chart.options3d[e.target.id] = parseFloat(e.target.value);
            showValues();
            chart.redraw(false);
        }));

        showValues();

        function showValues() {
            document.getElementById(
                    'alpha-value'
            ).innerHTML = chart.options.chart.options3d.alpha;
            document.getElementById(
                    'beta-value'
            ).innerHTML = chart.options.chart.options3d.beta;
            document.getElementById(
                    'depth-value'
            ).innerHTML = chart.options.chart.options3d.depth;
        }

        removeCreditos();

    }
</script>