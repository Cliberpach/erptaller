<table class="table table-hover table-striped" id="tbl_rentabilidad">
    <thead>
      <tr>
        <th scope="col">-</th>
        <th scope="col" style="text-align:right;">OPER</th>
        <th scope="col" style="text-align:right;">VENTAS</th>
        <th scope="col" style="text-align:right;">COSTO VENTAS</th>
        <th scope="col" style="text-align:right;">UTILIDAD BRUTA</th>
      </tr>
    </thead>
    <tbody>

    </tbody>
    <tfoot>

    </tfoot>
</table>

<script>
    function pintarTblRentabilidad(datos){
        const info      =   datos.datos;
        const totales   =   datos.totales;
        const tbody     =   document.querySelector('#tbl_rentabilidad tbody');
        const tfoot     =   document.querySelector('#tbl_rentabilidad tfoot');
        let filas       =   '';

        info.forEach((item)=>{
            filas   +=  `
                            <tr>
                                <td style="font-weight:bold;">${item.documento}</td>
                                <td style="text-align:right;">${item.operaciones}</td>
                                <td style="text-align:right;">${formatSoles(item.ventas)}</td>
                                <td style="text-align:right;">${formatSoles(item.costos)}</td>
                                <td style="text-align:right;">${formatSoles(item.utilidad_bruta)}</td>
                            </tr>
                        `;
        })

        const totalesFila = `
                                <tr>
                                    <td><strong>${totales.documento}</strong></td>
                                    <td style="text-align:right;"><strong>${totales.operaciones}</strong></td>
                                    <td style="text-align:right;"><strong>${formatSoles(totales.ventas)}</strong></td>
                                    <td style="text-align:right;"><strong>${formatSoles(totales.costos)}</strong></td>
                                    <td style="text-align:right;"><strong>${formatSoles(totales.utilidad_bruta)}</strong></td>
                                </tr>
                            `;

        tbody.innerHTML =   filas;
        tfoot.innerHTML =   totalesFila;

    }
</script>
