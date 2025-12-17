let modal = document.getElementById('exampleModal');
const btnClose= document.querySelector('.btn-close');
const btnAddNew= document.querySelector('.btn-add-new');
const btnSave= document.querySelector('.btn-save');
const inputName= document.querySelector('.inputName');
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const msgError= document.querySelector('.msgError');
let dataTable;
let customers={};
let mode;
let timer;

import {dateFormat,fetchRequest,runDataTable,simpleAlert,reloadDataTable,getRowDataTable} from "../../assets/js/functions.js";

document.addEventListener('DOMContentLoaded',()=>{
    dataTable= runDataTable('#miTabla',dataTable,customersList,columns);
    document.querySelector('#miTabla').hidden=false;
    btnSave.removeAttribute("data-bs-dismiss");
    events();
});


 

  