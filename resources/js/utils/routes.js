import { route } from "ziggy-js";

export const routesUtil = {
    searchCustomer: (q, vehicleId) => route('tenant.utils.searchCustomer', { q: q, vehicle_id: vehicleId })
}
