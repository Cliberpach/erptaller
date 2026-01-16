export const lstSale = [];

export let dtProducts = [];

export const amounts = {
    subtotal: 0,
    monto_igv: 0,
    total: 0
};

export const lstPays = [{
    method_pay: 1,
    amount: 0
}];

export let debounceTimer;

export const app = window.app;


export function setDtProducts(instance) {
    dtProducts = instance;
}

