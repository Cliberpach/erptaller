export async function fSearchVehicle(query, customerId) {
    try {
        mostrarAnimacion1();

        const res = await axios.get(route('tenant.utils.searchVehicle', {
            q: query,
            customer_id: customerId
        }));

        if (res.data.success) {
            toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
            return res.data.data;
        }else{
            toastr.error(res.data.message,'ERROR EN EL SERVIDOR');
            return null;
        }

    } catch (error) {
        toastr.error(error, 'ERROR AL CARGAR VEHÍCULOS DEL CLIENTE');
        return null;
    } finally {
        ocultarAnimacion1();
    }
}
