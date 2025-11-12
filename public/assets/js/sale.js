const bodyCar= document.querySelector('.body-car');
const paymentAmount= document.querySelector('.payment__amount');
const pOpAmount= document.querySelector('.op-amount');
const pIgvAmount= document.querySelector('.igv-amount');
const pTotalAmount=document.querySelector('.total-amount');


let dataTable;
let productCar=[];
const baseIGV=0.18;
let totalAmount=0;
let opgrav=0;
let igv=0;


import { Product } from "../../assets/js/classes.js";
import {runDataTable,buildBtnAdd,simpleAlert,reloadDataTable,getRowDataTable} from "../../assets/js/functions.js";

document.addEventListener('DOMContentLoaded',()=>{
    dataTable= runDataTable('#table-products',dataTable,productList,columns);
    buildBtnAdd();
    runClassProducts();
    events();
});



function runClassProducts(){
    const product = new Product();
    product.btnSave.removeAttribute("data-bs-dismiss");
    product.dataTable=dataTable;
    product.events();
}

function events(){
    document.addEventListener('click',(e)=>{
           if(e.target.classList.contains('btnAdd')){
               const product= getProduct(e.target.dataset.id);
                addProductToCar(product);
                calculatePrices(product,"add");
                paintCar(productCar);
           }
           if(e.target.classList.contains('remove-product')){

                const productId= e.target.dataset.id;
                removeProductFromCar(productId);
                paintCar(productCar);
           }
    })
    document.addEventListener('input',(e)=>{
        if(e.target.classList.contains('cant-car')){
            console.log(e.target.dataset.id)
        }
    })
}

const getProduct=(id)=>{
    const product={};
    const extractedProduct= getRowDataTable(id,dataTable);
    product.id= extractedProduct.id;
    product.name= extractedProduct.name;
    product.cant=1;
    product.sale_price= extractedProduct.sale_price;
    return product;
}

const addProductToCar=(product)=>{
   const productExisting= productCar.findIndex((p)=> product.id == p.id);

   if(productExisting == -1){
    productCar.push(product);
   }

   if(productExisting !=-1){
    productCar[productExisting].cant++;
    productCar[productExisting].sale_price= product.sale_price * productCar[productExisting].cant;
   }
}
const removeProductFromCar=(productId)=>{
    productCar.forEach((p,index)=>{
        if(p.id==productId){
            calculatePrices(p,"remove");
            productCar.splice(index,1);
        }
    })
}

const calculatePrices=(product,type)=>{
    calculateTotalAmount(product,type);
    calculateIgv();
    calculateOp();
}

const calculateTotalAmount=(product,type)=>{
    type=="add"?totalAmount=  totalAmount+parseFloat(product.sale_price):null;
    type=="remove"?totalAmount=  totalAmount-parseFloat(product.sale_price):null;
}

const calculateIgv=()=>{
    igv=Math.round((baseIGV*totalAmount) * 100) / 100;
}

const calculateOp=()=>{
    opgrav= Math.round((totalAmount-igv)*100)/100;
    totalAmount= Math.round((igv+opgrav)*100)/100;
}

const paintCar=(list)=>{
    clearCar();
    let content='';
    list.forEach((p)=>{
        content+=`<tr>
            <td>
                <input data-id="${p.id}" value="${p.cant}" type="number" class="form-control cant-car">
            </td>
            <td>
              <p>${p.name}</p>
            </td>
            <td>
              <div class="d-flex">
                <span style="font-weight:bold;">s/</span><input readonly="readonly" value="${p.sale_price}" type="number" class="form-control">
              </div>
            </td>
            <td>
                <i class="fas fa-trash-alt remove-product" data-id=${p.id}></i>
            </td>
        </tr>`
    })
    bodyCar.innerHTML=content;
    pTotalAmount.textContent=totalAmount;
    pOpAmount.textContent= opgrav;
    pIgvAmount.textContent=igv;
}

const clearCar=()=>{
    while(bodyCar.firstChild){
        bodyCar.removeChild(bodyCar.firstChild);
    }
}


