// Datatable
    // --------------------------------------------------------------------
export const runDataTable= (tableId,dataTable,data,columns)=>{
  const myTable = document.querySelector(tableId);
  
    dataTable = new DataTable(myTable,{
      data:data,
      columns:columns,
      
       //autoWidth: false,
      createdRow: function (row, data, dataIndex) {
        row.setAttribute('data-id', data.id);
      },
      responsive: true,
      "language":{
        "decimal":        "",
        "emptyTable":     "No hay datos disponibles",
        "info":           "Mostrando _START_ a _END_ de _TOTAL_ registros",
        "infoEmpty":      "Showing 0 to 0 of 0 entries",
        "infoFiltered":   "(filtered from _MAX_ total entries)",
        "infoPostFix":    "",
        "thousands":      ",",
        "lengthMenu":     "Mostrar _MENU_ registros",
        "loadingRecords": "Cargando...",
        "processing":     "",
        "search":         "Buscar:",
        "zeroRecords":    "Ningún registro encontrado",
        "paginate": {
          "first":      "Primero",
          "last":       "Último",
          "next":       "Siguiente",
          "previous":   "Previo"
        },
        "aria": {
          "sortAscending":  ": activate to sort column ascending",
          "sortDescending": ": activate to sort column descending"
        }
      }
    });

    return dataTable;
}

//Build dom DataTable
export const buildBtnAdd= ()=>{
  const tableFilter=document.querySelector('.dataTables_filter');

  tableFilter.classList.add('d-flex','align-items-center');

  const divAdd= document.createElement('DIV');
  const btnAdd= document.createElement('BTN');
  const iAdd= document.createElement('I');

  iAdd.classList.add('fas','fa-plus');
  btnAdd.classList.add('btn','btn-secondary','px-2','py-1','btn-add-new');
  btnAdd.setAttribute('data-bs-toggle', 'modal');
  btnAdd.setAttribute('data-bs-target', '#productModal');
  btnAdd.setAttribute('data-bs-whatever', 'NUEVO PRODUCTO');
  btnAdd.appendChild(iAdd);
  divAdd.appendChild(btnAdd);

  tableFilter.appendChild(divAdd);
}

// Reload table
    // --------------------------------------------------------------------
    export const reloadDataTable = (mode,datum, dataTable) => {
      mode=="create"?insertDataTable(datum,dataTable):null;
      mode=="update"?updateDataTable(datum,dataTable):null;
      mode=="delete"?deleteDataTable(datum,dataTable):null;
    };

// Insert DataTable
    // --------------------------------------------------------------------
    export const insertDataTable= (datum,dataTable)=>{
      dataTable.row.add(datum);
      dataTable.draw(false);
    }

//Update DataTable
    // --------------------------------------------------------------------
    const updateDataTable= (datum,dataTable)=>{
    //datum.created_at= dateFormat(datum.created_at);
    dataTable.row('[data-id="' + datum.id + '"]').data(datum).draw(false);
    }

//Delete DataTable
    // --------------------------------------------------------------------
    const deleteDataTable= (datum,dataTable)=>{
      dataTable.row('[data-id="' + datum.id + '"]').remove().draw();
    }  

//Get Row Datatable
    // --------------------------------------------------------------------
    export  const getRowDataTable = (rowId,dataTable)=>{
      return dataTable.row(`[data-id=${rowId}]`).data();
    }
  

// Single Alert
    // --------------------------------------------------------------------
export const simpleAlert= (pos,icon,titulo,time)=>{
  Swal.fire({
    position: pos,
    icon: icon,
    title: titulo,
    showConfirmButton: false,
    timer: time,
    allowOutsideClick: false
  })
}

// Format Date
    // --------------------------------------------------------------------
export const dateFormat=(date)=>{
  const dateObject = new Date(date);
  const format=`${dateObject.getFullYear()}-${dateObject.getMonth().toString().padStart(2, '0')}-${dateObject.getDate().toString().padStart(2, '0')} ${dateObject.getHours().toString().padStart(2, '0')}:${dateObject.getMinutes().toString().padStart(2, '0')}:${dateObject.getSeconds().toString().padStart(2, '0')}`;
  return format;
}


// Fetch
    // --------------------------------------------------------------------
    export const fetchRequest=async (method,url,data,csrfToken)=>{
      //overlay.style.display = 'block';
       //const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
       let result={};
       const options = {
           method: method,
           headers: {
             'Content-Type': 'application/json',
             'X-CSRF-TOKEN': csrfToken,
           },
           body: JSON.stringify(data),
         };
     await fetch(url, options)
     .then((res) => res.json())
     .catch((error) => console.error("Error:", error))
     .then((response) => {
      result=response;
     });
     return result;
    }


