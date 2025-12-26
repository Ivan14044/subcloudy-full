@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    <script>
        $(document).ready(function() {
            console.log('Support Badge: script initialized');
            const supportAudio = new Audio('/sounds/notification.mp3');
            let lastOpenCount = null;
            let lastClientMessageId = null;

            function updateSupportBadge() {
                $.get('{{ route('admin.support.stats') }}')
                    .done(function(data) {
                        const openCount = data.open || 0;
                        const currentLastClientMessageId = data.last_client_message_id;
                        
                        // Ищем только родительский элемент "Обращения клиентов"
                        const sidebar = $('.nav-sidebar, .main-sidebar, .sidebar');
                        let supportLink = sidebar.find('a').filter(function() {
                            const text = $(this).text().trim();
                            return text === 'Обращения клиентов';
                        });

                        if (supportLink.length) {
                            let badge = supportLink.find('.badge-support-count, .badge');
                            
                            if (openCount > 0) {
                                if (badge.length) {
                                    badge.text(openCount);
                                } else {
                                    supportLink.append(`<span class="badge badge-warning right badge-support-count">${openCount}</span>`);
                                }
                                
                                // Уведомление о НОВОМ ТИКЕТЕ (количество открытых увеличилось)
                                if (lastOpenCount !== null && openCount > lastOpenCount) {
                                    console.log('Support Badge: New ticket detected!');
                                    supportAudio.play().catch(e => console.warn('Audio play failed', e));
                                    if (typeof toastr !== 'undefined') {
                                        toastr.info('Новое обращение в поддержку!', 'Поддержка');
                                    }
                                }
                            } else if (badge.length) {
                                badge.remove();
                            }
                            
                            // Убеждаемся, что badge удален у "Все обращения"
                            const allTicketsLink = sidebar.find('a').filter(function() {
                                return $(this).text().trim() === 'Все обращения';
                            });
                            if (allTicketsLink.length) {
                                allTicketsLink.find('.badge, .badge-support-count').remove();
                            }
                            
                            // Убеждаемся, что badge удален у "Шаблоны ответов"
                            const templatesLink = sidebar.find('a').filter(function() {
                                return $(this).text().trim() === 'Шаблоны ответов';
                            });
                            if (templatesLink.length) {
                                templatesLink.find('.badge, .badge-support-count').remove();
                            }

                            // Уведомление о НОВОМ СООБЩЕНИИ от любого клиента
                            // Если мы не на странице чата (чтобы не дублировать звук)
                            const isOnChatPage = window.location.href.includes('/admin/support/');
                            if (lastClientMessageId !== null && currentLastClientMessageId > lastClientMessageId) {
                                console.log('Support Badge: New message detected globally!');
                                
                                if (!isOnChatPage) {
                                    supportAudio.play().catch(e => console.warn('Audio play failed', e));
                                    if (typeof toastr !== 'undefined') {
                                        toastr.success('Новое сообщение в чате поддержки!', 'Поддержка');
                                    }
                                }
                            }
                            
                            lastOpenCount = openCount;
                            lastClientMessageId = currentLastClientMessageId;
                        }
                    })
                    .fail(function(err) {
                        console.error('Support Badge: polling failed', err);
                    });
            }

            updateSupportBadge();
            setInterval(updateSupportBadge, 10000);
        });
    </script>
    @stack('js')
    @yield('js')
@stop
