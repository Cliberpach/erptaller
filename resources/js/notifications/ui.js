import { elements, notificationState } from "./states";

export function paintNotificationCount(count) {
    elements.count.textContent = count;
    elements.countHeader.textContent = count;
    elements.count.style.display = count > 0 ? 'block' : 'none';
}


export function removeLoadingIndicator() {
    const indicator = document.getElementById('loadingMoreIndicator');
    if (indicator) indicator.remove();
}

export function appendLoadingIndicator() {
    const loadingLi = document.createElement('li');
    loadingLi.className = 'list-group-item text-center text-muted';
    loadingLi.id = 'loadingMoreIndicator';
    loadingLi.innerHTML = `
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Cargando más...</span>
            </div>
        `;
    elements.list.appendChild(loadingLi);
}


export function showLoadNotify() {
    elements.list.innerHTML = `
        <li class="list-group-item text-center text-muted">
            <div class="spinner-border spinner-border-sm mb-2" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <div>Cargando notificaciones...</div>
        </li>
    `;
}


export function updateNotificationUI(data, isAppend = false) {
    const {
        count,
        notifications
    } = data;

    // Actualizar contador
    elements.count.textContent = count;
    elements.countHeader.textContent = count;
    elements.count.style.display = count > 0 ? 'block' : 'none';

    // Si no es append, limpiar lista
    if (!isAppend) {
        elements.list.innerHTML = '';
    }

    if (notifications.length === 0 && !isAppend) {
        elements.list.innerHTML = `
            <li class="list-group-item text-center text-muted">
                <i class="fi fi-rr-bell fs-3 d-block mb-2"></i>
                No hay notificaciones
            </li>
        `;
        return;
    }

    // Agregar notificaciones
    notifications.forEach(notification => {
        const iconData = getIconForType(notification.type_object);

        const li = document.createElement('li');
        li.className =
            'list-group-item d-flex justify-content-between align-items-start position-relative notification-item';
        li.dataset.id = notification.id;
        li.dataset.type = notification.type_object;
        li.dataset.objectId = notification.object_id;

        li.innerHTML = `
            <div class="avatar avatar-xs ${iconData.bgClass} rounded-circle text-white">
                <i class="${iconData.icon}"></i>
            </div>
            <div class="flex-grow-1 ms-2">
                <h6 class="mb-1">${escapeHtml(notification.name)}</h6>
                <small class="text-body d-block">${escapeHtml(notification.description || '')}</small>
                <small class="text-muted">${escapeHtml(notification.time_ago)}</small>
            </div>
            <button class="btn btn-sm btn-link text-muted p-0 mark-as-read"
                    data-id="${notification.id}"
                    title="Marcar como leída">
                <i class="bi bi-x-lg"></i>
            </button>
        `;

        elements.list.appendChild(li);
    });

    // Mostrar mensaje si no hay más
    if (!notificationState.hasMore && isAppend && notifications.length > 0) {
        const noMoreLi = document.createElement('li');
        noMoreLi.className = 'list-group-item text-center text-muted small';
        noMoreLi.innerHTML = `
            <i class="fi fi-rr-check-circle"></i> No hay más notificaciones
        `;
        elements.list.appendChild(noMoreLi);
    }
}


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


export function showErrorInList() {
    elements.list.innerHTML = `
        <li class="list-group-item text-center text-danger">
            <i class="fi fi-rr-cross-circle fs-3 d-block mb-2"></i>
            Error al cargar notificaciones
        </li>
    `;
}

