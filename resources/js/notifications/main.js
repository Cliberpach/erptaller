import { initScrollListener, updateNotificationCount } from "./actions";
import { eventsNotify } from "./events"

document.addEventListener('DOMContentLoaded', () => {

    updateNotificationCount();
    initScrollListener();
    eventsNotify();
})





