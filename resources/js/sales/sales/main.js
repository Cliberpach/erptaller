import { iniciarDataTableProductos } from "./datatables"
import { events } from "./events";
import { iniciarSelect2 } from "./select2";
import { loadTomSelect } from "./tomselect";

document.addEventListener('DOMContentLoaded', () => {
    iniciarDataTableProductos();
    iniciarSelect2();
    loadTomSelect();
    events();
})
