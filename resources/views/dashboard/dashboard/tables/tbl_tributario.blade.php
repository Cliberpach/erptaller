<table class="table table-hover table-striped" id="tbl_tributario">
    <thead>
      <tr>
        <th style="text-align: center;vertical-align:middle;" rowspan="2">-</th>
        <th style="text-align: center;vertical-align:middle;" rowspan="2" style="text-align:right;">VENTAS</th>
        <th style="text-align: center;vertical-align:middle;" colspan="2" style="text-align:center;">COMPRAS</th>
      </tr>
      <tr>
        <th style="text-align:right;">AFECTAS</th>
        <th style="text-align:right;">INAFECTAS</th>
      </tr>
    </thead>
    <tbody>

    </tbody>
    <tfoot>
        <tr style="font-weight:bold; background-color: #e0eaff;">
          <td>IGV X PAGAR</td>
          <td style="text-align:right;" class="igv_pagar"></td>
          <td>RENTA</td>
          <td style="text-align:right;" class="renta"></td>
        </tr>
    </tfoot>
</table>



<script>
    function pintarTblTributario(datos,renta){

        const tbody     =   document.querySelector('#tbl_tributario tbody');
        const tfoot     =   document.querySelector('#tbl_tributario tfoot');
        let filas       =   '';

        datos.forEach((item, index) => {
            const esUltimo = index === datos.length - 1;
            filas += `
                <tr style="${esUltimo ? 'font-weight:bold;' : ''}">
                    <td style="font-weight:bold;">${item.descripcion}</td>
                    <td style="text-align:right;">${formatSoles(item.ventas)}</td>
                    <td style="text-align:right;">${formatSoles(item.compras_afectas)}</td>
                    <td style="text-align:right;">${formatSoles(item.compras_inafectas)}</td>
                </tr>
            `;
        });

        document.querySelector('.igv_pagar').textContent    =   formatSoles(renta.igv_pagar);
        document.querySelector('.renta').textContent        =   formatSoles(renta.renta);

        tbody.innerHTML =   filas;

    }
</script>
