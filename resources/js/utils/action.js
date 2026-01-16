import { fSearchVehicle } from "./fetch";
import { setVehiclesClient } from "./ui";

export async function actionChangeClient(value) {
    if (!value) return;
    mostrarAnimacion1();
    const data = await fSearchVehicle(null,value);
    setVehiclesClient(data);
}



