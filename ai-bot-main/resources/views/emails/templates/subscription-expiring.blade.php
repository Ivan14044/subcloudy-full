@php
    // Шаблон уведомления об истечении срока
@endphp

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center" style="padding-bottom: 20px;">
            <div class="badge badge-warning">⏳ СРОК ИСТЕКАЕТ</div>
        </td>
    </tr>
</table>

<p>Уведомляем вас, что срок действия вашей подписки скоро заканчивается. Продлите её сейчас, чтобы не потерять доступ к сервису.</p>

<table border="0" cellpadding="0" cellspacing="0" class="info-card">
    <tr>
        <td class="info-body">
            <div class="info-title">ИНФОРМАЦИЯ</div>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr class="info-row">
                    <td class="info-label" style="padding: 10px 0;">СЕРВИС</td>
                    <td class="info-value" style="padding: 10px 0;">{{ $service_name ?? 'ChatGPT' }}</td>
                </tr>
                <tr class="info-row">
                    <td class="info-label" style="padding: 10px 0;">СТАТУС</td>
                    <td class="info-value" style="padding: 10px 0;">СКОРО ИСТЕКАЕТ</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p>Для продления перейдите на сайт и выберите удобный тарифный план.</p>
