<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест SMTP подключения - Subcloudy</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            padding: 0;
            margin: 0;
            line-height: 1.6;
        }
        .wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #0047ff;
            margin-top: 0;
            font-size: 24px;
        }
        .success-badge {
            display: inline-block;
            background-color: #28a745;
            color: #ffffff;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #0047ff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            margin-top: 0;
            color: #0047ff;
            font-size: 16px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
            font-family: 'Courier New', monospace;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
        .timestamp {
            color: #999;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="logo">
        <img src="{{ url('/img/logo_trans.png') }}" alt="Subcloudy" style="max-height: 40px;">
    </div>

    <div class="content">
        <span class="success-badge">✓ Успешно</span>
        <h1>Тест SMTP подключения</h1>
        
        <p>Поздравляем! Если вы получили это письмо, значит настройки SMTP работают корректно.</p>
        
        <div class="info-box">
            <h3>Информация о настройках SMTP</h3>
            <div class="info-row">
                <span class="info-label">SMTP Хост:</span>
                <span class="info-value">{{ $host ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Порт:</span>
                <span class="info-value">{{ $port ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Шифрование:</span>
                <span class="info-value">{{ $encryption ?? 'Без шифрования' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Отправитель:</span>
                <span class="info-value">{{ $from_address ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Имя отправителя:</span>
                <span class="info-value">{{ $from_name ?? 'N/A' }}</span>
            </div>
        </div>

        <p><strong>Что дальше?</strong></p>
        <ul>
            <li>Настройки SMTP сохранены и готовы к использованию</li>
            <li>Система будет использовать эти настройки для отправки всех писем</li>
            <li>Вы можете протестировать отправку писем пользователям через админ-панель</li>
        </ul>

        <div class="timestamp">
            Время отправки: {{ $timestamp ?? now()->format('d.m.Y H:i:s') }}
        </div>
    </div>

    <div class="footer">
        <p>Это автоматическое тестовое письмо от системы Subcloudy</p>
        <p>Если вы не запрашивали это письмо, пожалуйста, проигнорируйте его.</p>
    </div>
</div>
</body>
</html>

