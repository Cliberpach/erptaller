let modal = document.getElementById('exampleModal');
const btnClose= document.querySelector('.btn-close');
const btnAddNew= document.querySelector('.btn-add-new');
const btnSave= document.querySelector('.btn-save');
const inputName= document.querySelector('.inputName');
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const msgError= document.querySelector('.msgError');
let dataTable;
let category={};
let mode;
let timer;


import {fetchRequest,runDataTable,simpleAlert,reloadDataTable,getRowDataTable,dateFormat} from "../../assets/js/functions.js";

document.addEventListener('DOMContentLoaded',()=>{
  dataTable= runDataTable('#miTabla',dataTable,cashList,columns);
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
  })

  btnSave.addEventListener('click', ()=>{
    if(mode=="create"){
      getFormValues();
      callFetch('post','registrarCaja',category);
    }
    if(mode=="update"){
      getFormValues();
      callFetch('post','actualizarCaja',category); 
    }
  })

  document.addEventListener('click',(e)=>{
    if(e.target.classList.contains('btn-edit')){
        mode="update";
        console.log(mode)
        const categoryId=e.target.getAttribute('data-id');
        setFormValues(categoryId);
    }

    if(e.target.classList.contains('btn-delete')){
      mode="delete";
      const cashId=e.target.getAttribute('data-id');
      callFetch('post','eliminarCaja',{id:cashId}); 
    }  
  })

}

const getFormValues=()=>{
  category.name=inputName.value;
}

const setFormValues=(categoryId)=>{
  category= getRowDataTable(categoryId,dataTable);
  inputName.value= category.name;
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
      mode=="create"?simpleAlert("center","success","Caja registrada",2000):null;
      mode=="update"?simpleAlert("center","success","Caja actualizada",2000):null;
      response.data.created_at= dateFormat(response.data.created_at);
      reloadDataTable(mode,response.data,dataTable);
    }
  });
}


