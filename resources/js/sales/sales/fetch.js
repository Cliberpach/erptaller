import { routes } from "./routes";

export async function validateStock(product) {
    try {

        const token = document.querySelector('input[name="_token"]').value;
        const url = routes.validateStock(product.product_id, product.cant);

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': token
            },
        });

        const res = await response.json();

        return res;

    } catch (error) {
        toastr.error(error, 'ERROR EN LA PETICIÓN VALIDAR STOCK');
        return {
            success: false,
            message: error
        };
    }
}
