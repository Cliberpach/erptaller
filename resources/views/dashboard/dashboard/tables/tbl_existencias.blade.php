<table class="table table-hover table-striped" id="tbl_existencias">
    <tbody>
      <tr>
        <td>STOCK VALORIZADO</td>
        <td>0</td>
      </tr>
    </tbody>
</table>


<script>
    function pintarTblExistencias(datos){
        const tbody =   document.querySelector('#tbl_existencias tbody');
        let filas   =   `<tr>
                            <td>STOCK VALORIZADO</td>
                            <td>${datos.stock_valorizado}</td>
                        `;
        tbody.innerHTML =   filas;
    }
</script>
  