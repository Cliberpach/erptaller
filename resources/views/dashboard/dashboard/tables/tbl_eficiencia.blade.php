<table class="table-hover table-striped table" id="tbl_eficiencia">
    <thead>
        <tr>
            <th scope="col">-</th>
            <th scope="col">SALDO</th>
            <th scope="col">CRÉDITOS</th>
            <th scope="col">COBRANZA/PAGADO</th>
            <th scope="col">ACUMULADO</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>CUENTAS POR COBRAR</td>
            <td style="text-align: right;">0.00</td>
            <td style="text-align: right;">0.00</td>
            <td style="text-align: right;">0.00</td>
            <td style="text-align: right;">0.00</td>
        </tr>
        <tr>
            <td>CUENTAS POR PAGAR</td>
            <td style="text-align: right;">0.00</td>
            <td style="text-align: right;">0.00</td>
            <td style="text-align: right;">0.00</td>
            <td style="text-align: right;">0.00</td>
        </tr>
    </tbody>
</table>


<script>
    function pintarTblEficiencia(datos) {

        const tbody = document.querySelector('#tbl_eficiencia tbody');
        let filas = `
                                <tr>
                                    <td>CUENTAS POR COBRAR</td>
                                    <td style="text-align: right;">${formatSoles(datos.saldo_cobranza)}</td>
                                    <td style="text-align: right;">${formatSoles(datos.creditos_cobranza)}</td>
                                    <td style="text-align: right;">${formatSoles(datos.cobranza)}</td>
                                    <td style="text-align: right;">${formatSoles(datos.acumulado_cobranza)}</td>
                                </tr>

                            `;

        /* <tr>
                <td>CUENTAS POR PAGAR</td>
                <td style="text-align: right;">${formatSoles(datos.saldo_pagar)}</td>
                <td style="text-align: right;">${formatSoles(datos.creditos_pagar)}</td>
                <td style="text-align: right;">${formatSoles(datos.pagar)}</td>
                <td style="text-align: right;">${formatSoles(datos.acumulado_pagar)}</td>
            </tr>*/


        tbody.innerHTML = filas;

    }
</script>
