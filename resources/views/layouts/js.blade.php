<script src="{{ asset('assets/libs/global/global.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable.js') }}"></script>
<script src="{{ asset('assets/js/appSettings.js') }}"></script>

<script>
    window.lstSearchModules = @json($lst_search_modules);
    const baseUrl = @json($base);

    lstSearchModules.forEach((item) => {
        item.url = route(`${baseUrl}${item.url}`);
    })
</script>

<script src="{{ asset('assets/js/main.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
{{-- <script src="{{asset('assets/js/toast.js')}}"></script> --}}
<script src="{{ asset('assets/js/utils.js') }}"></script>

<script>
    let audioEnabled = false;
    let notificationCount = 0;

    document.addEventListener('DOMContentLoaded', () => {

        notificationAudio = new Audio('/assets/sounds/bell-notification-337658.mp3');
        notificationAudio.volume = 0.5;

        if ("Notification" in window && Notification.permission === "default") {
            Notification.requestPermission();
        }

        showMessages();
        eventsMenu();
        eventAlerts();

    })

    function eventsMenu() {
        document.addEventListener('click', function(e) {

            const menuLink = e.target.closest('.menu-click');
            if (!menuLink) return;
            showLoader();

        });
    }


    function hideLoader() {
        const loader = document.querySelector('.loader-overlay');
        if (loader) {
            loader.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function enableAudio() {
        audioEnabled = true;
    }


    window.onload = function() {
        hideLoader()
    };


    function showMessages() {
        const messageSuccess = "{{ Session::get('message_success') }}";
        const messageError = "{{ Session::get('message_error') }}";

        if (messageSuccess) {
            Swal.fire({
                icon: 'success',
                title: 'OPERACIÓN COMPLETADA',
                text: messageSuccess,
                customClass: {
                    confirmButton: 'btn-primary'
                },
            });
        }

        if (messageError) {
            Swal.fire({
                icon: 'error',
                title: 'ERROR EN LA OPERACIÓN',
                text: messageError,
                customClass: {
                    confirmButton: 'btn-primary'
                },
            });
        }

    }

    function showLoader() {
        document.querySelector('.loader-overlay')?.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function hideLoader() {
        const loader = document.querySelector('.loader-overlay');
        if (loader) {
            loader.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    window.onload = function() {
        hideLoader()
    };

    function eventAlerts() {

        document.body.addEventListener('click', enableAudio, {
            once: true
        });

        // Eventos de conexión
        window.Echo.connector.pusher.connection.bind('connected', () => {
            console.log('✅ CONECTADO');
        });

        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            console.log('❌ DESCONECTADO');
        });

        window.Echo.connector.pusher.connection.bind('error', (err) => {
            console.error('❌ ERROR:', err);
        });

        // Suscripción al canal
        const userId    =   {{ auth()->id() }};
        const channel   =   Echo.private(`user.${userId}`);

        channel.subscribed(() => {
            console.log('✅ SUSCRITO AL CANAL PRIVADO user.' + userId);
        });

        channel.error((error) => {
            console.error('❌ ERROR EN CANAL PRIVADO:', error);
        });

        channel.listen('.alert.created', (data) => {
            console.log('🔥 ALERTA RECIBIDA:', data);
            console.log('📋 Datos completos:', data.alert);
            emitAlert(data.alert);
        });

    }

    function emitAlert(alert) {
        // 1. Reproducir sonido
        playNotificationSound();

        showFacebookStyleToast(alert);

        // 2. Agregar al dropdown de notificaciones
        addNotificationToDropdown(alert);

        // 3. Incrementar contador
        updateNotificationBadge();

        // 4. Animar el icono de campana
        animateBellIcon();

        // NOTIFIED
        window.markAsNotified(alert.id);
    }

    function playNotificationSound() {
        const audio = new Audio('/assets/sounds/bell-notification-337658.mp3');
        audio.play().catch(e => console.log('No se pudo reproducir el sonido'));
    }

    function addNotificationToDropdown(alert) {
        // Obtener el contenedor de notificaciones
        const notificationList = document.querySelector('.list-group-hover');

        if (!notificationList) return;

        // Crear el elemento de notificación
        const notificationItem = document.createElement('li');
        notificationItem.className =
            'list-group-item d-flex justify-content-between align-items-center notification-new';

        // Icono según el tipo de objeto
        const iconConfig = getIconForType(alert.type_object);

        notificationItem.innerHTML = `
            <div class="avatar avatar-xs ${iconConfig.bgClass} rounded-circle text-white">
                <i class="${iconConfig.icon}"></i>
            </div>
            <div class="me-auto ms-2">
                <h6 class="mb-0">${alert.name}</h6>
                <small class="text-body d-block">${alert.description || 'Nueva alerta'}</small>
                <small class="text-muted position-absolute end-0 top-0 me-3 mt-2">Ahora</small>
            </div>
        `;

        // Click para ver detalles
        notificationItem.style.cursor = 'pointer';
        notificationItem.addEventListener('click', () => {
            redirectToObject(alert);
        });

        // Insertar AL INICIO de la lista
        notificationList.insertBefore(notificationItem, notificationList.firstChild);

        // Animar la entrada
        setTimeout(() => {
            notificationItem.classList.add('notification-show');
        }, 10);

        // Quitar la clase "nueva" después de 5 segundos
        setTimeout(() => {
            notificationItem.classList.remove('notification-new');
        }, 5000);
    }

    function updateNotificationBadge() {
        notificationCount++;

        const badge = document.querySelector('.dropdown-menu h6 .badge');
        if (badge) {
            badge.textContent = notificationCount;

            // Animar el badge
            badge.classList.add('badge-pulse');
            setTimeout(() => {
                badge.classList.remove('badge-pulse');
            }, 1000);
        }
    }

    function animateBellIcon() {
        const bellIcon = document.querySelector('.fi-rr-bell');
        if (bellIcon) {
            bellIcon.classList.add('bell-ring');
            setTimeout(() => {
                bellIcon.classList.remove('bell-ring');
            }, 1000);
        }
    }

    function redirectToObject(alert) {
        const routes = {
            'ORDEN_TRABAJO': '/ordenes-trabajo/',
            'COTIZACION': '/cotizaciones/',
            'VENTA': '/ventas/',
            'PRODUCCION': '/produccion/',
            'COMPRA': '/compras/',
        };

        const baseRoute = routes[alert.type_object];
        if (baseRoute) {
            window.location.href = baseRoute + alert.object_id;
        }
    }

    function showFacebookStyleToast(alert) {
        const iconConfig = getIconForType(alert.type_object);

        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: true,
            confirmButtonText: 'Ver',
            showCloseButton: true,
            timer: 8000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            },
            customClass: {
                popup: 'facebook-toast',
                title: 'facebook-toast-title',
                htmlContainer: 'facebook-toast-content',
                confirmButton: 'btn btn-sm btn-primary',
                closeButton: 'facebook-toast-close'
            }
        });

        Toast.fire({
            icon: getAlertIcon(alert.type_object),
            title: alert.name,
            html: `
            <div class="toast-alert-body">
                <p class="mb-1"><strong>${alert.type_object}</strong> #${alert.object_id}</p>
                <p class="mb-0 text-muted">${alert.description || 'Nueva alerta'}</p>
            </div>
        `,
        }).then((result) => {
            if (result.isConfirmed) {
                redirectToObject(alert);
            }
        });
    }

    function getAlertIcon(typeObject) {
        const icons = {
            'ORDEN_TRABAJO': 'info',
            'COTIZACION': 'info',
            'VENTA': 'success',
            'PRODUCCION': 'warning',
            'COMPRA': 'info',
        };

        return icons[typeObject] || 'warning';
    }

    // Marcar como leída
    async function markAsRead(notificationId) {
        try {
            await axios.post(`/notifications/${notificationId}/read`);
            await loadNotifications();
            await updateNotificationCount();
        } catch (error) {
            console.error('Error al marcar como leída:', error);
        }
    }

    // Marcar todas como leídas
    async function markAllAsRead() {
        try {
            await axios.post('{{ route('notifications.readAll') }}');
            await loadNotifications();
            await updateNotificationCount();
        } catch (error) {
            console.error('Error al marcar todas como leídas:', error);
        }
    }

    // Navegar a notificación
    function navigateToNotification(type, objectId) {
        const routes = {
            'ORDEN_TRABAJO': `/orden-trabajo/${objectId}`,
            'COTIZACION': `/cotizacion/${objectId}`,
            'VENTA': `/venta/${objectId}`,
            'PRODUCCION': `/produccion/${objectId}`,
            'COMPRA': `/compra/${objectId}`
        };

        const url = routes[type];
        if (url) window.location.href = url;
    }

    // Obtener iconos
    function getIconForType(typeObject) {
        const icons = {
            'ORDEN_TRABAJO': {
                icon: 'fi fi-rr-tool-box',
                bgClass: 'bg-primary'
            },
            'COTIZACION': {
                icon: 'fi fi-rr-calculator',
                bgClass: 'bg-info'
            },
            'VENTA': {
                icon: 'fi fi-rr-shopping-cart',
                bgClass: 'bg-success'
            },
            'PRODUCCION': {
                icon: 'fi fi-rr-settings',
                bgClass: 'bg-warning'
            },
            'COMPRA': {
                icon: 'fi fi-rr-shopping-bag',
                bgClass: 'bg-secondary'
            },
        };

        return icons[typeObject] || {
            icon: 'fi fi-rr-bell',
            bgClass: 'bg-dark'
        };
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

@yield('js')
@stack('js-script')
