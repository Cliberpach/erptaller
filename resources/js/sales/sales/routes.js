import { route } from "ziggy-js";

export const routes = {
    getProducts: route('tenant.ventas.comprobante_venta.getProductos'),
    store: route('tenant.ventas.comprobante_venta.store'),
    index: route('tenant.ventas.comprobante_venta'),
    validateStock:route('tenant.ventas.comprobante_venta.validateStock')
}
