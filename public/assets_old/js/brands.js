let modal = document.getElementById('exampleModal');
const btnClose= document.querySelector('.btn-close');
const btnAddNew= document.querySelector('.btn-add-new');
const btnSave= document.querySelector('.btn-save');
const inputName= document.querySelector('.inputName');
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const msgError= document.querySelector('.msgError');
let dataTable;
let brand={};
let mode;
let timer;

import {fetchRequest,runDataTable,dateFormat,simpleAlert,reloadDataTable,getRowDataTable} from "../../assets/js/functions.js";


document.addEventListener('DOMContentLoaded',()=>{
    dataTable= runDataTable('#miTabla',dataTable,brandList,columns);
    document.querySelector('#miTabla').hidden=false;
    btnSave.removeAttribute("data-bs-dismiss");
    events();
})

function events(){
    modal.addEventListener('show.bs.modal', (event)=> {
        var button = event.relatedTarget
        var recipient = button.getAttribute('data-bs-whatever')
        var modalTitle = modal.querySelector('.modal-title')
        var modalBodyInput = modal.querySelector('.modal-body input')
        modalTitle.textContent = recipient
    })

    btnAddNew.addEventListener('click',()=>{
        mode="create";
        console.log(mode)
        inputName.value='';
    })

    btnSave.addEventListener('click', ()=>{
        if(mode=="create"){
          getFormValues();
          callFetch('post','registrarMarca',brand);
        }
        if(mode=="update"){
          getFormValues();
          callFetch('post','actualizarMarca',brand); 
        }
    })

   document.addEventListener('click',(e)=>{
      if(e.target.classList.contains('btn-edit')){
          mode="update";
          const brandId=e.target.getAttribute('data-id');
          setFormValues(brandId);
      }

      if(e.target.classList.contains('btn-delete')){
        mode="delete";
        const brandId=e.target.getAttribute('data-id');
        callFetch('post','eliminarCategoria',{id:brandId}); 
      }  
    })


}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.btn-edit').forEach(button => {
      button.addEventListener('click', function () {
          const brandId = this.dataset.id;
          const brandName = this.dataset.name;

          // Completar el formulario con los valores correspondientes
          const form = document.getElementById('editBrandForm');
          form.action = `actualizar-marca/${brandId}`;
          form.querySelector('input[name="name"]').value = brandName;

          // Abrir el modal de edición
          new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
      });
  });
});


const getFormValues=()=>{
    brand.name=inputName.value;
}

const setFormValues=(brandId)=>{
    brand= getRowDataTable(brandId,dataTable);
    inputName.value= brand.name;
}

const paintError= (mensaje)=>{
    inputName.value="";
    
     clearTimeout(timer);
     msgError.textContent=mensaje;
     msgError.hidden=false;
     inputName.focus();
     inputName.classList.add('inputError');
     timer= setTimeout(() => {
      msgError.hidden=true;
      inputName.classList.remove('inputError');
     }, 2000);
     
}
  
function callFetch(method,url,data){
    inputName.value='';
    fetchRequest(method,url, data,csrfToken) 
    .then((response) => {
      console.log(response)
      if(response.type==="error"){
        paintError(response.errors.name[0]);
      }
      if(response.type==="success"){
        btnClose.click();
        mode=="create"?simpleAlert("center","success","Marca registrada",2000):null;
        mode=="update"?simpleAlert("center","success","Marca actualizada",2000):null;
        response.data.created_at= dateFormat(response.data.created_at);
        reloadDataTable(mode,response.data,dataTable);
      }
    });
}