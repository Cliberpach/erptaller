import { loadNotifications } from "./actions";
import { elements } from "./states";

export function eventsNotify() {
    elements.button?.addEventListener('click', () => {
        loadNotifications();
    });

    elements.markAllBtn?.addEventListener('click', markAllAsRead);

    elements.list?.addEventListener('click', (e) => {

        if (e.target.closest('.mark-as-read')) {
            e.stopPropagation();
            const button = e.target.closest('.mark-as-read');
            markAsRead(button.dataset.id);
            return;
        }

        // Navegar
        const item = e.target.closest('.notification-item');
        if (item) {
            navigateToNotification(item.dataset.type, item.dataset.objectId);
        }
    });
}

