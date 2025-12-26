@php
    // Шаблон для активации подписки
@endphp

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center" style="padding-bottom: 20px;">
            <div class="badge badge-success">✓ ПОДПИСКА АКТИВИРОВАНА</div>
        </td>
    </tr>
</table>

<p>Поздравляем! Ваша подписка на сервис успешно активирована. Теперь вы можете использовать все премиум-возможности.</p>

<table border="0" cellpadding="0" cellspacing="0" class="info-card">
    <tr>
        <td class="info-body">
            <div class="info-title">ДЕТАЛИ ПОДПИСКИ</div>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr class="info-row">
                    <td class="info-label" style="padding: 10px 0;">СЕРВИС</td>
                    <td class="info-value" style="padding: 10px 0;">{{ $service_name ?? 'ChatGPT' }}</td>
                </tr>
                <tr class="info-row">
                    <td class="info-label" style="padding: 10px 0;">СТАТУС</td>
                    <td class="info-value" style="padding: 10px 0;">АКТИВНА</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p>Если у вас возникнут вопросы, наша служба поддержки всегда на связи.</p>
