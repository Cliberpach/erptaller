export const elements = {
    button: document.getElementById('notificationButton'),
    count: document.getElementById('notificationCount'),
    countHeader: document.getElementById('notificationCountHeader'),
    list: document.getElementById('notificationList'),
    markAllBtn: document.getElementById('markAllAsRead'),
    scrollContainer: null
};

export let notificationState = {
    currentPage: 1,
    isLoading: false,
    hasMore: true,
    totalCount: 0
};
