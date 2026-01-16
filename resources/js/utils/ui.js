export function setVehiclesClient(vehicles) {
    window.vehicleSelect.clear();
    window.vehicleSelect.clearOptions();
    console.log(vehicles);
    vehicles.forEach(v => {
        window.vehicleSelect.addOption({
            id: v.id,
            text: v.text,
            subtext: v.subtext
        });
    });
}
