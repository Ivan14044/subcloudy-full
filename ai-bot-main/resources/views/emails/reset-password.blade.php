@php
    $message = $translation['message'] ?? '';
    $buttonHtml = '
        <div class="button-container">
            <a href="' . $url . '" class="button" target="_blank" style="
                display: inline-block;
                background: linear-gradient(135deg, #0047ff 0%, #007bff 100%);
                color: #ffffff !important;
                text-decoration: none;
                padding: 14px 32px;
                border-radius: 8px;
                font-weight: 600;
                font-size: 16px;
                box-shadow: 0 4px 12px rgba(0, 71, 255, 0.3);
            ">
                ' . (trans('email.reset_password') ?: 'Сбросить пароль') . '
            </a>
        </div>
    ';
    $message = str_replace('{{button}}', $buttonHtml, $message);
    
    // Добавляем badge если его нет
    if (strpos($message, 'badge') === false) {
        $badgeHtml = '<div class="badge badge-info">🔐 Сброс пароля</div>';
        $message = $badgeHtml . $message;
    }
@endphp

{!! $message !!}
