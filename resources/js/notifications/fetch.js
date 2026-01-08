import { route } from "ziggy-js";
import { routes } from "./routes";
import { notificationState } from "./states";

export async function getNotificationCount() {
    try {
        const {
            data
        } = await axios.get(route(routes.count));
        const count = data.count;

        return count;

    } catch (error) {
        toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER CANTIDAD DE NOTIFICACIONES');
        return null;
    }
}

export async function getNotifications(page) {
    try {
        const { data } = await axios.get(route(routes.index), {
            params: {
                page: page
            }
        });

        return data;

    } catch (error) {
        toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER CANTIDAD DE NOTIFICACIONES');
        return null;
    }
}

export async function setNotified(alertId) {
    try {

        const formData = new FormData();
        formData.append('alert_id', alertId);
        const res = await axios.post(route(routes.notified), formData);

        if (res.data.success) {
            return res.data;
        } else {
            toastr.error(res.data.message, 'ERROR AL MARCAR NOTIFICADO');
            return null;
        }

    } catch (error) {
        toastr.error(error, 'ERROR EN LA PETICIÓN MARCAR NOTIFICADO');
        return null;
    }
}

