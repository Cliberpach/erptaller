import { simpleAlert, reloadDataTable, getRowDataTable } from "../../assets/js/functions.js";

// CLASS PRODUCT
// --------------------------------------------------------------------
export class Product {
    constructor() {
        this.modal = document.getElementById('productModal');
        this.btnSave = document.querySelector('.btn-save');
        this.btnClose = document.querySelector('.btn-close');
        this.containerImg = document.querySelector('.container-img');
        this.inputName = document.querySelector('.name');
        this.inputDescription = document.querySelector('.description');
        this.inputSalePrice = document.querySelector('.sale-price');
        this.inputPurchasePrice = document.querySelector('.purchase-price');
        this.inputStock = document.querySelector('.stock');
        this.inputStockMin = document.querySelector('.stock_min');
        this.inputFactoryCode = document.querySelector('.factorycode');
        this.inputBarCode = document.querySelector('.barcode');
        this.selectCategory = document.querySelector('.category');
        this.selectBrand = document.querySelector('.brand');
        this.inputImage = document.querySelector('.image');
        this.previewImage = document.querySelector('#preview-image');
        this.form = document.querySelector('#my-form');
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        this.btnAddNew = document.querySelector('.btn-add-new');
        this.pNameImage = document.querySelector('.nameImage');
        this.aDeleteImage = document.querySelector('.delete-image');
        this.mode;
        this.formData;
        this.dataTable;
        this.product = {};
        this.temporizador;
    }

    events() {
        this.modal.addEventListener('show.bs.modal', (event) => {
            var button = event.relatedTarget;
            var recipient = button.getAttribute('data-bs-whatever');
            var modalTitle = this.modal.querySelector('.modal-title');
            var modalBodyInput = this.modal.querySelector('.modal-body input');
            modalTitle.textContent = recipient;
            this.inputImage.value = '';
        });

        this.containerImg.addEventListener('click', (e) => {
            this.inputImage.click();
        });

        this.inputImage.addEventListener('change', () => {
            console.log(this.inputImage.value);
            this.readImage(this.inputImage, this.previewImage);
        });

        this.btnAddNew.addEventListener('click', () => {
            this.mode = 'create';
            document.querySelector('.colStock').style.display       =   'block';
            document.querySelector('.colStockMin').style.display    =   'block';
            this.resetForm();
        });

        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.getFormValues();
            if (this.mode == 'create') {
                this.fetchRequest('post', '/inventarios/productos/registrar-producto', this.formData, this.csrfToken);
            }
            if (this.mode == 'update') {
                this.fetchRequest('post', '/inventarios/productos/actualizar-producto', this.formData, this.csrfToken);
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-edit')) {
                this.mode               = "update";
                const product_id        = e.target.dataset.id;

                //======== OCULTAR CAMPOS STOCK ======
                document.querySelector('.colStock').style.display       =   'none';
                document.querySelector('.colStockMin').style.display    =   'none';

                this.setFormValues(product_id);
            }
        });

        this.aDeleteImage.addEventListener('click', () => {
            this.pNameImage.textContent = 'img_default.png';
            this.previewImage.src = urlImageDefault;
            this.inputImage.value = '';
        });
    }
    

    readImage(inputRead, elementPreview) {
        if (inputRead.files && inputRead.files[0]) {
            const reader = new FileReader();
            reader.onload = function (r) {
                elementPreview.src = r.target.result;
                elementPreview.style.display = 'block';
            };

            reader.readAsDataURL(inputRead.files[0]);
            this.pNameImage.textContent = inputRead.value.replace(/^.*[\\\/]/, '');
        } else {
            elementPreview.src = urlImageDefault;
            this.pNameImage.textContent = 'img_default.png';
        }
    }

    getFormValues() {
        this.formData = new FormData();
        this.product.name = this.inputName.value;
        this.product.category_id = this.selectCategory.value;
        this.product.brand_id = this.selectBrand.value;
        this.product.sale_price = this.inputSalePrice.value;
        this.product.purchase_price = this.inputPurchasePrice.value;
        this.product.stock = this.inputStock.value;
        this.product.stock_min = this.inputStockMin.value;
        this.product.image = this.pNameImage.textContent;
        this.product.description = this.inputDescription.value;
        this.product.code_factory = this.inputFactoryCode.value;
        this.product.code_bar = this.inputBarCode.value;
        if (this.mode == "create") {
            this.product.id ? delete this.product.id : null;
        }
        this.formData.append('productData', JSON.stringify(this.product));
        if (this.inputImage.files.length > 0) {
            this.formData.append('image', this.inputImage.files[0]);
        }
    }

    setFormValues(product_id) {
        const link_img = document.querySelector('tr[data-id="' + product_id + '"]').children[11].children[0].src;
        const product_edit = getRowDataTable(product_id, this.dataTable);
        this.product.id = product_id;

        this.inputName.value = product_edit.name;
        this.inputDescription.value = product_edit.description;
        this.inputSalePrice.value = product_edit.sale_price;
        this.inputPurchasePrice.value = product_edit.purchase_price;
        this.inputStock.value = product_edit.stock;
        this.inputStockMin.value = product_edit.stock_min;
        this.inputFactoryCode.value = product_edit.code_factory;
        this.inputBarCode.value = product_edit.code_bar;
        this.setSelectValue(this.selectCategory, product_edit.category_name);
        this.setSelectValue(this.selectBrand, product_edit.brand_name);
        this.previewImage.src = link_img;
        this.pNameImage.textContent = product_edit.image;
    }

    setSelectValue(select, attribute) {
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].textContent == attribute) {
                select.options[i].selected = true;
                break;
            }
        }
    }

    resetForm() {
        this.form.reset();
        this.previewImage.src       = urlImageDefault;
        this.inputImage.value       = '';
        this.pNameImage.textContent = 'img_default.png';
        this.inputStock.value       =   0;
        this.inputStockMin.value    =   1;
    }

    pintarError(errors) {
        clearTimeout(this.temporizador);
        const elementsError = document.querySelectorAll('.msgError');
        elementsError.forEach(element => {
            element.textContent = '';
        });

        for (const field in errors) {
            const errorMessage = errors[field][0];
            field == 'productData.name' ? document.querySelector('.productData\\.name').textContent = errorMessage : null;
            field === 'productData.sale_price' ? document.querySelector('.productData\\.sale_price').textContent = errorMessage : null
            field === 'productData.purchase_price' ? document.querySelector('.productData\\.purchase_price').textContent = errorMessage : null
            field === 'productData.stock' ? document.querySelector('.productData\\.stock').textContent = errorMessage : null
            field === 'productData.stock_min' ? document.querySelector('.productData\\.stock_min').textContent = errorMessage : null
            field === 'productData.brand_id' ? document.querySelector('.productData\\.brand_id').textContent = errorMessage : null
            field === 'productData.category_id' ? document.querySelector('.productData\\.category_id').textContent = errorMessage : null
        }

        this.temporizador = setTimeout(() => {
            elementsError.forEach(element => {
                element.textContent = '';
            });
        }, 3000);
    }

    async fetchRequest(method, url, data, csrfToken) {
        const options = {
            method: method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            body: data,
        };

        await fetch(url, options)
            .then((res) => res.json())
            .catch((error) => console.error("Error:", error))
            .then((response) => {
                console.log(response);
                if (response.type == "error") {
                    this.pintarError(response.errors);
                }
                if (response.type == "success") {
                    this.btnClose.click();
                    this.resetForm();
                    if (this.mode == "create") {
                        simpleAlert("center", "success", "Producto registrado", 2000);
                    }
                    if (this.mode == "update") {
                        simpleAlert("center", "success", "Producto actualizado", 2000);
                    }
                    reloadDataTable(this.mode, response.data, this.dataTable);
                }
            });
    }
}
