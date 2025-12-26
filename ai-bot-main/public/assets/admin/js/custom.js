// Admin Support Real-time Notifications
$(document).ready(function() {
    console.log('[SupportBadge] Script initialized');
    let lastTicketCount = 0;
    const notificationAudio = new Audio('/sounds/notification.mp3');

    function checkSupportStats() {
        console.log('[SupportBadge] checkSupportStats called');
        $.get('/admin/support/stats', function(data) {
            console.log('[SupportBadge] Stats received', { 
                data: data,
                open: data.open,
                lastTicketCount: lastTicketCount
            });
            
            if (data.open !== undefined) {
                // Если количество открытых тикетов увеличилось
                if (data.open > lastTicketCount && lastTicketCount !== 0) {
                    console.log('[SupportBadge] New tickets detected, playing notification');
                    notificationAudio.play().catch(e => console.warn('Audio play failed', e));
                }
                
                // Всегда обновляем бейдж при получении данных
                updateSupportBadge(data.open);
                
                lastTicketCount = data.open;
            } else {
                console.warn('[SupportBadge] data.open is undefined', { data });
            }
        }).fail(function(xhr, status, error) {
            console.error('[SupportBadge] Failed to fetch stats', { 
                status: status,
                error: error,
                xhr: xhr
            });
        });
    }

    function updateSupportBadge(count) {
        console.log('[SupportBadge] updateSupportBadge called', { count });
        
        // Ищем родительский элемент "Обращения клиентов" в сайдбаре меню
        const sidebar = $('.main-sidebar, .sidebar, nav.main-sidebar');
        console.log('[SupportBadge] Sidebar found', { sidebarLength: sidebar.length });
        
        const parentLink = sidebar.find('a').filter(function() {
            const text = $(this).text().trim();
            return text === 'Обращения клиентов';
        });
        console.log('[SupportBadge] Parent link "Обращения клиентов" found', { 
            found: parentLink.length,
            href: parentLink.length ? parentLink.attr('href') : null
        });
        
        // Добавляем или обновляем бейдж только у "Обращения клиентов"
        if (parentLink.length) {
            let badge = parentLink.find('.badge');
            console.log('[SupportBadge] Badge check for parent', { 
                badgeExists: badge.length,
                count: count
            });
            
            if (count > 0) {
                if (badge.length) {
                    badge.text(count);
                    console.log('[SupportBadge] Badge updated on parent', { count });
                } else {
                    parentLink.append(`<span class="badge badge-info right">${count}</span>`);
                    console.log('[SupportBadge] Badge added to parent', { count });
                }
            } else {
                badge.remove();
                console.log('[SupportBadge] Badge removed from parent (count is 0)');
            }
        } else {
            console.warn('[SupportBadge] Parent link "Обращения клиентов" not found!');
        }
        
        // Убеждаемся, что badge удален у "Все обращения"
        const supportLink = sidebar.find('a').filter(function() {
            const text = $(this).text().trim();
            return text === 'Все обращения';
        });
        console.log('[SupportBadge] Support link "Все обращения" found', { 
            found: supportLink.length,
            href: supportLink.length ? supportLink.attr('href') : null
        });
        
        if (supportLink.length) {
            const removedBadges = supportLink.find('.badge').remove();
            console.log('[SupportBadge] Removed badges from "Все обращения"', { 
                removedCount: removedBadges.length 
            });
        }
        
        // Убеждаемся, что badge удален у "Шаблоны ответов"
        const templatesLink = sidebar.find('a').filter(function() {
            const text = $(this).text().trim();
            return text === 'Шаблоны ответов';
        });
        console.log('[SupportBadge] Templates link "Шаблоны ответов" found', { 
            found: templatesLink.length,
            href: templatesLink.length ? templatesLink.attr('href') : null
        });
        
        if (templatesLink.length) {
            const removedBadges = templatesLink.find('.badge').remove();
            console.log('[SupportBadge] Removed badges from "Шаблоны ответов"', { 
                removedCount: removedBadges.length 
            });
        }
        
        console.log('[SupportBadge] updateSupportBadge completed');
    }

    // Первичная проверка
    console.log('[SupportBadge] Starting initial check');
    checkSupportStats();
    
    // Интервал проверки (каждые 30 секунд для глобальной проверки)
    console.log('[SupportBadge] Setting up interval (30 seconds)');
    setInterval(checkSupportStats, 30000);
    console.log('[SupportBadge] Setup completed');
});



