// Admin Support Real-time Notifications
$(document).ready(function() {
    let lastTicketCount = 0;
    const notificationAudio = new Audio('/sounds/notification.mp3');

    function checkSupportStats() {
        $.get('/api/support/stats', function(data) {
            if (data.open !== undefined) {
                // Если количество открытых тикетов увеличилось
                if (data.open > lastTicketCount && lastTicketCount !== 0) {
                    notificationAudio.play().catch(e => console.warn('Audio play failed', e));
                    
                    // Можно также обновить бейдж в меню, если он есть
                    updateSupportBadge(data.open);
                }
                lastTicketCount = data.open;
            }
        });
    }

    function updateSupportBadge(count) {
        const supportLink = $('a[href*="support"]');
        if (supportLink.length) {
            let badge = supportLink.find('.badge');
            if (count > 0) {
                if (badge.length) {
                    badge.text(count);
                } else {
                    supportLink.append(`<span class="badge badge-info right">${count}</span>`);
                }
            } else {
                badge.remove();
            }
        }
    }

    // Первичная проверка
    checkSupportStats();
    
    // Интервал проверки (каждые 30 секунд для глобальной проверки)
    setInterval(checkSupportStats, 30000);
});

