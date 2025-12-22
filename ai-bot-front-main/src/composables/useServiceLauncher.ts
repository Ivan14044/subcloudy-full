import { ref, onUnmounted } from 'vue';

type ServiceWindow = {
    window: Window | null;
    url: string;
};

export function useServiceLauncher() {
    const activeWindows = ref<Record<string, ServiceWindow>>({});

    const launchService = (url: string, serviceName: string) => {
        try {
            const existing = activeWindows.value[serviceName]?.window;
            if (existing && !existing.closed) existing.close();

            const features = [
                'width=1200',
                'height=800',
                'left=50',
                'top=50',
                'menubar=no',
                'toolbar=no',
                'location=yes',
                'status=no',
                'resizable=yes',
                'scrollbars=yes',
                'noopener=yes',
                'noreferrer=yes'
            ].join(',');

            const newWindow = window.open(url, `service_${serviceName}`, features);
            if (!newWindow) return;

            newWindow.opener = null;
            activeWindows.value = {
                ...activeWindows.value,
                [serviceName]: { window: newWindow, url }
            };

            const checkWindow = setInterval(() => {
                if (newWindow.closed) {
                    clearInterval(checkWindow);
                    const updated = { ...activeWindows.value };
                    delete updated[serviceName];
                    activeWindows.value = updated;
                }
            }, 1000);

            newWindow.addEventListener('load', () => {
                try {
                    if (!newWindow.document) return;

                    const metas = [
                        {
                            httpEquiv: 'Content-Security-Policy',
                            content:
                                "default-src 'self' * 'unsafe-inline' 'unsafe-eval'; connect-src *; script-src 'self' * 'unsafe-inline' 'unsafe-eval'; style-src 'self' * 'unsafe-inline'; img-src 'self' * data: blob:; frame-ancestors 'none'; form-action 'self'"
                        },
                        { name: 'referrer', content: 'no-referrer' }
                    ];

                    metas.forEach(attrs => {
                        const m = newWindow.document.createElement('meta');
                        Object.entries(attrs).forEach(([k, v]) => m.setAttribute(k, v));
                        newWindow.document.head.appendChild(m);
                    });

                    // ПОЛНАЯ ЗАЩИТА ОТ ВЫХОДА ИЗ АККАУНТА
                    const logoutProtectionScript = newWindow.document.createElement('script');
                    logoutProtectionScript.textContent = `
            (function(){
                'use strict';
                
                console.log('[SubCloudy] Полная защита от выхода из аккаунта активирована');
                
                // Список защищенных куков
                const protectedCookieNames = [
                    'session', 'sessionid', 'auth', 'authentication', 
                    'token', 'access_token', 'refresh_token',
                    '__Secure-auth', '__Host-auth',
                    'chatgpt_session', 'openai_session',
                    'midjourney_session',
                    'auth_token', 'authToken', 'csrf_token', 'csrftoken'
                ];
                
                function isProtectedCookie(cookieString) {
                    if (!cookieString) return false;
                    const name = cookieString.split('=')[0].trim().toLowerCase();
                    return protectedCookieNames.some(protected => 
                        name.includes(protected.toLowerCase()) || 
                        name === protected.toLowerCase()
                    );
                }
                
                // 1. БЛОКИРОВКА УДАЛЕНИЯ КУКОВ
                const originalCookieDescriptor = Object.getOwnPropertyDescriptor(Document.prototype, 'cookie') || 
                                                 Object.getOwnPropertyDescriptor(HTMLDocument.prototype, 'cookie');
                
                if (originalCookieDescriptor && originalCookieDescriptor.set) {
                    Object.defineProperty(document, 'cookie', {
                        get: function() {
                            return originalCookieDescriptor.get.call(this);
                        },
                        set: function(cookieString) {
                            // Блокируем ВСЕ попытки удаления куков
                            if (cookieString.includes('expires=Thu, 01 Jan 1970') || 
                                cookieString.includes('Max-Age=0') ||
                                cookieString.includes('Max-Age=-1') ||
                                (cookieString.includes('expires=') && cookieString.includes('1970'))) {
                                if (isProtectedCookie(cookieString)) {
                                    console.warn('[SubCloudy] Удаление защищенной куки заблокировано');
                                    return false;
                                }
                            }
                            return originalCookieDescriptor.set.call(this, cookieString);
                        },
                        configurable: true
                    });
                }
                
                // 2. КРИТИЧНО: ПЕРЕХВАТ ВСЕХ API ЗАПРОСОВ, КОТОРЫЕ МОГУТ ИНВАЛИДИРОВАТЬ СЕССИЮ
                const logoutApiPatterns = [
                    '/logout', '/signout', '/sign-out', '/log-out',
                    '/auth/logout', '/api/logout', '/api/auth/logout',
                    '/api/v1/logout', '/api/v1/auth/logout',
                    '/session/revoke', '/session/invalidate', '/session/delete', '/session/terminate', '/session/destroy',
                    '/token/revoke', '/token/invalidate', '/token/delete', '/token/terminate',
                    '/account/logout', '/account/signout', '/user/logout', '/users/logout',
                    '/auth/signout', '/auth/sign-out', '/auth/revoke',
                    '/oauth/logout', '/oauth/revoke', '/oauth/invalidate',
                    '/sessions/revoke', '/sessions/invalidate', '/sessions/delete',
                    '/tokens/revoke', '/tokens/invalidate', '/tokens/delete'
                ];
                
                // Перехватываем fetch - КРИТИЧНО для блокировки инвалидации сессии
                const originalFetch = window.fetch;
                window.fetch = function(...args) {
                    const url = typeof args[0] === 'string' ? args[0] : args[0]?.url || '';
                    const method = (args[1]?.method || 'GET').toUpperCase();
                    const body = args[1]?.body;
                    
                    // Блокируем запросы на logout/invalidate по URL
                    if (logoutApiPatterns.some(pattern => url.toLowerCase().includes(pattern.toLowerCase()))) {
                        console.warn('[SubCloudy] API запрос на выход/инвалидацию сессии заблокирован:', url);
                        return Promise.reject(new Error('Logout API request blocked by SubCloudy'));
                    }
                    
                    // Блокируем DELETE/POST/PUT запросы на сессии/токены/авторизацию
                    if ((method === 'DELETE' || method === 'POST' || method === 'PUT') && 
                        (url.toLowerCase().includes('/session') || 
                         url.toLowerCase().includes('/token') || 
                         url.toLowerCase().includes('/auth') ||
                         url.toLowerCase().includes('/account'))) {
                        
                        const urlLower = url.toLowerCase();
                        
                        // Безопасная проверка body на признаки инвалидации
                        let bodyStr = '';
                        if (body) {
                            if (typeof body === 'string') {
                                bodyStr = body.toLowerCase();
                            } else if (body instanceof FormData) {
                                // Для FormData проверяем значения
                                try {
                                    for (const [key, value] of body.entries()) {
                                        const valueStr = typeof value === 'string' ? value : value.toString();
                                        bodyStr += key.toLowerCase() + '=' + valueStr.toLowerCase() + '&';
                                    }
                                } catch (e) {
                                    // Игнорируем ошибки чтения FormData
                                }
                            } else if (body instanceof URLSearchParams) {
                                bodyStr = body.toString().toLowerCase();
                            } else if (body instanceof Blob || body instanceof ArrayBuffer) {
                                // Для Blob/ArrayBuffer не можем проверить содержимое без чтения
                                // Блокируем по URL если он содержит опасные паттерны
                            } else {
                                // Пытаемся сериализовать объект
                                try {
                                    bodyStr = JSON.stringify(body).toLowerCase();
                                } catch (e) {
                                    // Если не удалось сериализовать, проверяем только URL
                                }
                            }
                        }
                        
                        // Проверяем URL и body на признаки инвалидации
                        if (urlLower.includes('revoke') || 
                            urlLower.includes('invalidate') || 
                            urlLower.includes('logout') || 
                            urlLower.includes('signout') ||
                            urlLower.includes('delete') ||
                            urlLower.includes('terminate') ||
                            urlLower.includes('destroy') ||
                            bodyStr.includes('logout') ||
                            bodyStr.includes('signout') ||
                            bodyStr.includes('revoke') ||
                            bodyStr.includes('invalidate') ||
                            bodyStr.includes('terminate') ||
                            bodyStr.includes('destroy')) {
                            console.warn('[SubCloudy] API запрос на инвалидацию сессии заблокирован:', url, method);
                            return Promise.reject(new Error('Session invalidation blocked by SubCloudy'));
                        }
                    }
                    
                    return originalFetch.apply(this, args);
                };
                
                // Перехватываем XMLHttpRequest - КРИТИЧНО
                const originalXHROpen = XMLHttpRequest.prototype.open;
                const originalXHRSend = XMLHttpRequest.prototype.send;
                
                XMLHttpRequest.prototype.open = function(...args) {
                    this._method = (args[0] || 'GET').toUpperCase();
                    this._url = args[1] || '';
                    return originalXHROpen.apply(this, args);
                };
                
                XMLHttpRequest.prototype.send = function(...args) {
                    const url = (this._url || '').toLowerCase();
                    const method = (this._method || 'GET').toUpperCase();
                    
                    // Блокируем запросы на logout/invalidate по URL
                    if (logoutApiPatterns.some(pattern => url.includes(pattern.toLowerCase()))) {
                        console.warn('[SubCloudy] XHR запрос на выход заблокирован:', this._url);
                        this.abort();
                        return;
                    }
                    
                    // Блокируем DELETE/POST/PUT запросы на сессии/токены/авторизацию
                    if ((method === 'DELETE' || method === 'POST' || method === 'PUT') && 
                        (url.includes('/session') || url.includes('/token') || url.includes('/auth') || url.includes('/account'))) {
                        
                        // Безопасная проверка body
                        let bodyStr = '';
                        const body = args[0];
                        if (body) {
                            if (typeof body === 'string') {
                                bodyStr = body.toLowerCase();
                            } else if (body instanceof FormData) {
                                try {
                                    for (const [key, value] of body.entries()) {
                                        const valueStr = typeof value === 'string' ? value : value.toString();
                                        bodyStr += key.toLowerCase() + '=' + valueStr.toLowerCase() + '&';
                                    }
                                } catch (e) {
                                    // Игнорируем ошибки чтения FormData
                                }
                            } else if (body instanceof URLSearchParams) {
                                bodyStr = body.toString().toLowerCase();
                            } else {
                                try {
                                    bodyStr = JSON.stringify(body).toLowerCase();
                                } catch (e) {
                                    // Если не удалось сериализовать, проверяем только URL
                                }
                            }
                        }
                        
                        if (url.includes('revoke') || 
                            url.includes('invalidate') || 
                            url.includes('logout') || 
                            url.includes('signout') ||
                            url.includes('delete') ||
                            url.includes('terminate') ||
                            url.includes('destroy') ||
                            bodyStr.includes('logout') ||
                            bodyStr.includes('signout') ||
                            bodyStr.includes('revoke') ||
                            bodyStr.includes('invalidate') ||
                            bodyStr.includes('terminate') ||
                            bodyStr.includes('destroy')) {
                            console.warn('[SubCloudy] XHR запрос на инвалидацию сессии заблокирован:', this._url, method);
                            this.abort();
                            return;
                        }
                    }
                    
                    return originalXHRSend.apply(this, args);
                };
                
                // 3. БЛОКИРОВКА КНОПОК ВЫХОДА - максимально агрессивно
                function blockLogoutButtons() {
                    const logoutTexts = [
                        'logout', 'log out', 'sign out', 'signout', 
                        'выйти', 'выход', 'выйти из аккаунта',
                        'déconnexion', 'cerrar sesión', 'abmelden',
                        'disconnect', 'log off', 'sign off'
                    ];
                    
                    // Ищем все возможные элементы
                    const selectors = [
                        'button', 'a', 'div[role="button"]', 'span[role="button"]',
                        '[onclick]', '[data-testid]', '[aria-label]',
                        'input[type="submit"]', 'input[type="button"]'
                    ];
                    
                    const allElements = document.querySelectorAll(selectors.join(', '));
                    
                    allElements.forEach(el => {
                        const text = (el.textContent || el.innerText || '').toLowerCase().trim();
                        const href = (el.getAttribute('href') || '').toLowerCase();
                        const onclick = (el.getAttribute('onclick') || '').toLowerCase();
                        const ariaLabel = (el.getAttribute('aria-label') || '').toLowerCase();
                        const dataTestId = (el.getAttribute('data-testid') || '').toLowerCase();
                        const className = (el.getAttribute('class') || '').toLowerCase();
                        const id = (el.getAttribute('id') || '').toLowerCase();
                        
                        // Проверяем все признаки кнопки выхода
                        const isLogout = logoutTexts.some(logoutText => 
                            text.includes(logoutText) || 
                            href.includes('/logout') ||
                            href.includes('/signout') ||
                            onclick.includes('logout') ||
                            onclick.includes('signout') ||
                            ariaLabel.includes('logout') ||
                            ariaLabel.includes('sign out') ||
                            dataTestId.includes('logout') ||
                            className.includes('logout') ||
                            id.includes('logout')
                        );
                        
                        if (isLogout) {
                            // Полностью удаляем/скрываем элемент
                            el.style.display = 'none !important';
                            el.style.visibility = 'hidden !important';
                            el.style.opacity = '0 !important';
                            el.style.pointerEvents = 'none !important';
                            el.style.position = 'absolute !important';
                            el.style.left = '-9999px !important';
                            el.style.width = '0 !important';
                            el.style.height = '0 !important';
                            el.setAttribute('disabled', 'true');
                            el.setAttribute('aria-hidden', 'true');
                            el.setAttribute('tabindex', '-1');
                            
                            // Блокируем все возможные события
                            const events = ['click', 'mousedown', 'mouseup', 'touchstart', 'touchend', 'keydown', 'keyup'];
                            events.forEach(eventType => {
                                el.addEventListener(eventType, function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    e.stopImmediatePropagation();
                                    return false;
                                }, true);
                            });
                            
                            // Удаляем onclick
                            el.removeAttribute('onclick');
                            el.onclick = null;
                        }
                    });
                }
                
                // 4. БЛОКИРОВКА НАВИГАЦИИ
                const logoutPatterns = [
                    '/logout', '/signout', '/sign-out', '/log-out',
                    '/auth/logout', '/account/logout', '/user/logout',
                    '/api/logout', '/api/auth/logout'
                ];
                
                const originalPushState = history.pushState;
                const originalReplaceState = history.replaceState;
                
                history.pushState = function(...args) {
                    const url = args[2];
                    if (typeof url === 'string' && logoutPatterns.some(pattern => url.toLowerCase().includes(pattern.toLowerCase()))) {
                        console.warn('[SubCloudy] Попытка перехода на страницу выхода заблокирована');
                        return;
                    }
                    return originalPushState.apply(this, args);
                };
                
                history.replaceState = function(...args) {
                    const url = args[2];
                    if (typeof url === 'string' && logoutPatterns.some(pattern => url.toLowerCase().includes(pattern.toLowerCase()))) {
                        console.warn('[SubCloudy] Попытка замены на страницу выхода заблокирована');
                        return;
                    }
                    return originalReplaceState.apply(this, args);
                };
                
                const originalLocationHref = Object.getOwnPropertyDescriptor(window.location, 'href');
                if (originalLocationHref && originalLocationHref.set) {
                    Object.defineProperty(window.location, 'href', {
                        get: function() {
                            return originalLocationHref.get.call(this);
                        },
                        set: function(url) {
                            if (typeof url === 'string' && logoutPatterns.some(pattern => url.toLowerCase().includes(pattern.toLowerCase()))) {
                                console.warn('[SubCloudy] Попытка выхода через location.href заблокирована');
                                return false;
                            }
                            return originalLocationHref.set.call(this, url);
                        },
                        configurable: true
                    });
                }
                
                const originalLocationAssign = window.location.assign;
                const originalLocationReplace = window.location.replace;
                
                window.location.assign = function(url) {
                    if (typeof url === 'string' && logoutPatterns.some(pattern => url.toLowerCase().includes(pattern.toLowerCase()))) {
                        console.warn('[SubCloudy] Попытка выхода через location.assign заблокирована');
                        return;
                    }
                    return originalLocationAssign.call(this, url);
                };
                
                window.location.replace = function(url) {
                    if (typeof url === 'string' && logoutPatterns.some(pattern => url.toLowerCase().includes(pattern.toLowerCase()))) {
                        console.warn('[SubCloudy] Попытка выхода через location.replace заблокирована');
                        return;
                    }
                    return originalLocationReplace.call(this, url);
                };
                
                // 5. БЛОКИРОВКА ФОРМ
                document.addEventListener('submit', function(e) {
                    const form = e.target;
                    if (form && form.tagName === 'FORM') {
                        const action = (form.getAttribute('action') || '').toLowerCase();
                        
                        if (logoutPatterns.some(pattern => action.includes(pattern.toLowerCase()))) {
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                            console.warn('[SubCloudy] Попытка выхода через форму заблокирована');
                            return false;
                        }
                    }
                }, true);
                
                // 6. БЛОКИРОВКА COOKIESTORE
                if (window.cookieStore) {
                    const originalDelete = window.cookieStore.delete;
                    window.cookieStore.delete = function(...args) {
                        const cookieName = args[0]?.name || (typeof args[0] === 'string' ? args[0] : '');
                        if (isProtectedCookie(cookieName)) {
                            console.warn('[SubCloudy] Удаление защищенной куки через cookieStore заблокировано');
                            return Promise.resolve(false);
                        }
                        return originalDelete.apply(this, args);
                    };
                }
                
                // 7. ПОСТОЯННЫЙ МОНИТОРИНГ И БЛОКИРОВКА
                blockLogoutButtons();
                setInterval(blockLogoutButtons, 500); // Каждые 500мс
                
                const observer = new MutationObserver(function(mutations) {
                    blockLogoutButtons();
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['href', 'onclick', 'aria-label', 'data-testid', 'class', 'id']
                });
                
                // Блокируем при загрузке
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', blockLogoutButtons);
                } else {
                    blockLogoutButtons();
                }
                
                // Дополнительная защита: перехватываем все клики на уровне document
                document.addEventListener('click', function(e) {
                    const target = e.target;
                    if (!target) return;
                    
                    const text = (target.textContent || target.innerText || '').toLowerCase();
                    const href = (target.getAttribute('href') || '').toLowerCase();
                    
                    if (text.includes('logout') || text.includes('sign out') || text.includes('выйти') ||
                        href.includes('/logout') || href.includes('/signout')) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        console.warn('[SubCloudy] Клик на элемент выхода заблокирован');
                        return false;
                    }
                }, true); // Используем capture phase для раннего перехвата
            })();
          `;
                    newWindow.document.head.appendChild(logoutProtectionScript);

                    // Существующий код защиты от копирования и т.д.
                    const existingScript = newWindow.document.createElement('script');
                    existingScript.textContent = `
            (function(){
              if (window.cookieStore) { try { window.cookieStore = undefined; } catch(_){} }
              setInterval(() => {
                const devtools = /./;
                devtools.toString = function(){ this.opened = true; }
                console.log('%c', devtools);
                if (devtools.opened) window.location.href = 'about:blank';
              }, 1000);
              document.addEventListener('copy', e => e.preventDefault());
              document.addEventListener('cut', e => e.preventDefault());
              document.addEventListener('contextmenu', e => e.preventDefault());
              document.addEventListener('keydown', function(e){
                if ((e.ctrlKey && ['c','v','u','i'].includes(e.key.toLowerCase())) || e.key === 'F12') {
                  e.preventDefault();
                }
              });
            })();
          `;
                    newWindow.document.head.appendChild(existingScript);
                } catch (e) {
                    // cross-origin — игнорируем
                    console.log('Window loaded with security measures', e);
                }
            });
        } catch (error) {
            console.error('Failed to launch service:', error);
        }
    };

    const closeAllWindows = () => {
        Object.values(activeWindows.value).forEach(({ window }) => {
            if (window && !window.closed) window.close();
        });
        activeWindows.value = {};
    };

    onUnmounted(closeAllWindows);

    return { launchService, closeAllWindows, activeWindows };
}
