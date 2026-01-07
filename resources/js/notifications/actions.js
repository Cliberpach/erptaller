import { route } from "ziggy-js";
import { elements, notificationState } from "./states";
import { appendLoadingIndicator, paintNotificationCount, removeLoadingIndicator, showErrorInList, showLoadNotify, updateNotificationUI } from "./ui";
import { routes } from "./routes";
import { getNotificationCount, getNotifications } from "./fetch";

export async function loadNotifications() {

    notificationState.currentPage = 1;
    notificationState.hasMore = true;

    // Mostrar loading
    showLoadNotify();

    try {
        console.log('🔔 Cargando notificaciones del servidor...');
        const data = await getNotifications(1);
        console.log('✅ Notificaciones recibidas:', data);

        // Actualizar estado
        notificationState.hasMore = data.has_more;
        notificationState.totalCount = data.count;

        updateNotificationUI(data, false); // false = no es append
    } catch (error) {
        console.error('❌ Error al cargar notificaciones:', error);
        showErrorInList();
    }
}


export function initScrollListener() {
    // Buscar el contenedor con scroll de múltiples formas
    const container = document.querySelector('[data-simplebar]') ||
        document.querySelector('.simplebar-content-wrapper') ||
        document.querySelector('.p-2[style*="height"]');

    if (!container) {
        console.error('❌ No se encontró el contenedor de scroll');
        return;
    }

    elements.scrollContainer = container;

    console.log('✅ Contenedor de scroll encontrado:', container);

    // Si usa SimpleBar, obtener el elemento interno
    if (container.classList.contains('simplebar-content-wrapper')) {
        elements.scrollContainer.addEventListener('scroll', handleScroll);
        console.log('📜 Listener agregado a SimpleBar');
    }
    // Si es el contenedor directo con data-simplebar
    else if (container.hasAttribute('data-simplebar')) {
        // Esperar a que SimpleBar se inicialice
        setTimeout(() => {
            const simpleBarContent = container.querySelector('.simplebar-content-wrapper');
            if (simpleBarContent) {
                elements.scrollContainer = simpleBarContent;
                simpleBarContent.addEventListener('scroll', handleScroll);
                console.log('📜 Listener agregado a SimpleBar (con delay)');
            } else {
                container.addEventListener('scroll', handleScroll);
                console.log('📜 Listener agregado al contenedor directo');
            }
        }, 500);
    }
    // Fallback: scroll normal
    else {
        container.addEventListener('scroll', handleScroll);
        console.log('📜 Listener agregado (scroll normal)');
    }
}


export async function updateNotificationCount() {
    try {
        const count = getNotificationCount();

        paintNotificationCount(count);
    } catch (error) {
        console.error('Error al actualizar contador:', error);
    }
}


function handleScroll(e) {
    const element = e.target;
    const scrollTop = element.scrollTop;
    const scrollHeight = element.scrollHeight;
    const clientHeight = element.clientHeight;

    // Calcular porcentaje de scroll
    const scrollPercentage = (scrollTop + clientHeight) / scrollHeight;

    // 🔍 DEBUGGING - Comentar después de probar
    console.log('Scroll:', {
        scrollTop: scrollTop.toFixed(0),
        scrollHeight: scrollHeight.toFixed(0),
        clientHeight: clientHeight.toFixed(0),
        porcentaje: (scrollPercentage * 100).toFixed(2) + '%',
        hasMore: notificationState.hasMore,
        isLoading: notificationState.isLoading
    });

    // Si está cerca del final (80% del scroll)
    if (scrollPercentage > 0.8 && notificationState.hasMore && !notificationState.isLoading) {
        console.log('🚀 Activando carga de más notificaciones...');
        loadMoreNotifications();
    }
}

async function loadMoreNotifications() {
    if (notificationState.isLoading || !notificationState.hasMore) return;

    notificationState.isLoading = true;
    notificationState.currentPage++;

    // Agregar loading al final de la lista
    appendLoadingIndicator();

    try {

        const data = await getNotifications(notificationState.currentPage);

        console.log(`✅ Página ${notificationState.currentPage} cargada:`, data);

        // Actualizar estado
        notificationState.hasMore = data.has_more;

        // Remover loading indicator
        removeLoadingIndicator();

        // Agregar nuevas notificaciones
        updateNotificationUI(data, true); // true = append

    } catch (error) {
        console.error('❌ Error al cargar más notificaciones:', error);
        removeLoadingIndicator();
    } finally {
        notificationState.isLoading = false;
    }
}

