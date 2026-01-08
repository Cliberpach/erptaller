/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import TomSelect from "tom-select";
import "tom-select/dist/css/tom-select.bootstrap5.css";
window.TomSelect = TomSelect;

import * as FilePond from "filepond";
import "filepond/dist/filepond.min.css";
import FilePondPluginImagePreview from "filepond-plugin-image-preview";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css";
import FilePondPluginFileValidateSize from "filepond-plugin-file-validate-size";
import es_ES from "filepond/locale/es-es.js";

import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";

FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateSize,
    FilePondPluginFileValidateType
);

FilePond.setOptions(es_ES);

window.FilePond = FilePond;


import lightGallery from 'lightgallery';

// Estilos principales
import 'lightgallery/css/lightgallery.css';

// Plugins
import lgThumbnail from 'lightgallery/plugins/thumbnail';
import 'lightgallery/css/lg-thumbnail.css';

import lgZoom from 'lightgallery/plugins/zoom';
import 'lightgallery/css/lg-zoom.css';


window.lightGallery = lightGallery;
window.lgThumbnail = lgThumbnail;
window.lgZoom = lgZoom;


/*======== HOVERCSS =========*/
import 'hover.css/css/hover.css';

/*======= TOASTR ==========*/
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 3000,
};

window.toastr = toastr;


/*======== SWAL =========*/
import Swal from 'sweetalert2';
window.Swal = Swal;



/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
Pusher.logToConsole = true;

/*const echoConfig = {
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: 'mt1',
    wsHost: '127.0.0.1',
    wsPort: 6001,
    forceTLS: false,
    encrypted: false,
    disableStats: true,
    enabledTransports: ['ws'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }
};*/

console.log('🔧 Variables de entorno cargadas:', {
    VITE_PUSHER_APP_KEY: import.meta.env.VITE_PUSHER_APP_KEY,
    VITE_PUSHER_HOST: import.meta.env.VITE_PUSHER_HOST,
    VITE_PUSHER_PORT: import.meta.env.VITE_PUSHER_PORT,
    VITE_PUSHER_SCHEME: import.meta.env.VITE_PUSHER_SCHEME,
    VITE_PUSHER_APP_CLUSTER: import.meta.env.VITE_PUSHER_APP_CLUSTER,
});

/*const echoConfig = {
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? window.location.hostname,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    encrypted: true,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
};*/

const echoConfig = {
    broadcaster: 'pusher',
    key: 'app-key',
    cluster: 'mt1',
    //wsHost: 'tallersuite.store',
    wsHost: window.location.hostname,
    wsPort: 443,
    wssPort: 443,
    forceTLS: true,
    encrypted: true,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }
};

console.log('🔧 Configuración de Echo:', echoConfig);

window.Echo = new Echo(echoConfig);

// Debug de conexión
window.Echo.connector.pusher.connection.bind('state_change', (states) => {
    console.log('🔌 WebSocket state:', states);
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('❌ WebSocket error:', err);
});

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ WebSocket conectado exitosamente!');
});
