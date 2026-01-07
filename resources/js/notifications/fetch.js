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

