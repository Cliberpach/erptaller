const btnGuardar= document.querySelector('.btnGuardar');
const fragment = document.createDocumentFragment();
const contenidoTabla= document.querySelector('.body-table');
const selectCajas= document.querySelector('.selectCajas');
const selectTurnos= document.querySelector('.selectTurnos');
const inputSaldo= document.querySelector('.inputSaldo');
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const btnAbrirCaja= document.querySelector('.btnAbrirCaja');
const errorCaja= document.querySelector('.errorCaja');
const errorTurno= document.querySelector('.errorTurno');
const errorSaldo= document.querySelector('.errorSaldo');
const btnClose= document.querySelector('.btn-close');
const btnCancelar= document.querySelector('.btnCancelar');

let dataTable;
let temporizador; 
let modo;
let cuadre={};

import {fetchRequest,runDataTable,simpleAlert,insertDataTable,getRowDataTable} from "../../assets/js/functions.js";

document.addEventListener('DOMContentLoaded',()=>{
  dataTable= runDataTable('#miTabla',dataTable,cashBookList,columns);
  document.querySelector('#miTabla').hidden=false;
  events();
 
})

function events(){
  btnAbrirCaja.addEventListener('click',(e)=>{
    modo="open";
    btnGuardar.removeAttribute("data-bs-dismiss");
    console.log(modo);
  })
  btnGuardar.addEventListener('click',async(e)=>{
    await callFetch('post','abrirCaja',validarFormulario());
    
  })

  
}



const validarFormulario= ()=>{
  const idCaja= selectCajas.value;
  const idTurno= document.querySelector('.selectTurnos').value;
  const cantidadInicial= document.querySelector('.inputSaldo').value;
  const nombreCaja= selectCajas.selectedOptions[0].textContent;
  cuadre={idCaja,idTurno,cantidadInicial,nombreCaja};
  return cuadre;
}

function callFetch(method,url,data){
  fetchRequest(method,url, data,csrfToken) 
  .then((respuesta) => {
    console.log(respuesta);
    if(respuesta.tipo==="error"){
      pintarError(respuesta.errors);
    }
    if(respuesta.tipo==="success"){
      btnClose.click();
      simpleAlert('center','success','Caja aperturada',2000);
      insertDataTable(respuesta.data,dataTable);
      
    }
  });
}

const pintarError=(messages)=>{
  clearTimeout(temporizador);
  errorCaja.textContent="";
  errorTurno.textContent="";
  errorSaldo.textContent="";
  errorCaja.hidden=false;
  errorTurno.hidden=false;
  errorSaldo.hidden=false;

 const messagesKeys= Object.keys(messages);

 messagesKeys.forEach((msg)=>{
      messages[msg]=='El campo Caja es obligatorio.'? errorCaja.textContent=messages[msg]:errorCaja.hidden=true;
      messages[msg]=='Por favor, selecciona una opción válida para el campo Caja.'? errorCaja.textContent=messages[msg]:errorCaja.hidden=true;
      messages[msg]=='El campo Turno es obligatorio.'? errorTurno.textContent=messages[msg]:errorTurno.hidden==true;
      messages[msg]=='Por favor, selecciona una opción válida para el campo Turno.'? errorTurno.textContent=messages[msg]:errorTurno.hidden=true;
      messages[msg]=='El campo Saldo inicial es obligatorio.'? errorSaldo.textContent=messages[msg]:errorSaldo.hidden=true;
      messages[msg]=='El campo Saldo inicial debe ser un número.'? errorSaldo.textContent=messages[msg]:errorSaldo.hidden=true;
      messages[msg]=='El campo Saldo inicial debe ser mayor o igual a 0.'? errorSaldo.textContent=messages[msg]:errorSaldo.hidden=true;
  })

    temporizador= setTimeout(() => {
      errorCaja.hidden=true;
      errorTurno.hidden=true;
      errorSaldo.hidden=true;
    }, 3000);
}

document.addEventListener('click', function(e) {
  if (e.target && e.target.classList.contains('eventCerrar')) {
      const modo = "close";
      const eventElement = e.target.closest('.eventCerrar');
      const id = eventElement.getAttribute('data-id');
      const pettyid = eventElement.getAttribute('data-petty-cash-id');

      // Mostrar mensaje de confirmación con SweetAlert2
      Swal.fire({
          title: '¿Estás seguro?',
          text: "¡No podrás revertir esta acción!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, cerrar la caja',
          cancelButtonText: 'Cancelar'
      }).then((result) => {
          if (result.isConfirmed) {
              // Si el usuario confirma, procede con el cierre de caja
              console.log(modo, id);

              fetch('/closeCashBook', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': csrfToken
                  },
                  body: JSON.stringify({ id, status: modo , pettyid })
              })
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      Swal.fire(
                          'Cerrado!',
                          'La caja ha sido cerrada exitosamente.',
                          'success'
                      ).then(() => {
                          window.location.reload(); // Recargar la página después de la confirmación
                      });
                  } else {
                      Swal.fire(
                          'Error',
                          'Hubo un problema al cerrar la caja: ' + data.message,
                          'error'
                      );
                  }
              })
              .catch(error => {
                  Swal.fire(
                      'Error',
                      'Hubo un problema al realizar la solicitud: ' + error,
                      'error'
                  );
              });
          }
      });
  }
});


