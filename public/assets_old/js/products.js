
import { Product } from "../../assets/js/classes.js";
import {runDataTable} from "../../assets/js/functions.js";

document.addEventListener('DOMContentLoaded',()=>{
 runClassProducts();
})

function runClassProducts(){
    const product = new Product();
    product.dataTable= runDataTable('#table-products',product.dataTable,productList,columns);
    product.btnSave.removeAttribute("data-bs-dismiss");
    product.events();
}



