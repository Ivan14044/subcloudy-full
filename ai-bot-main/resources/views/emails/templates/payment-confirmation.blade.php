@php
    // Шаблон подтверждения оплаты
@endphp

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center" style="padding-bottom: 20px;">
            <div class="badge badge-info">💰 ОПЛАТА ПОДТВЕРЖДЕНА</div>
        </td>
    </tr>
</table>

<p>Мы получили ваш платеж. Благодарим за доверие к SubCloudy!</p>

<table border="0" cellpadding="0" cellspacing="0" class="info-card">
    <tr>
        <td class="info-body">
            <div class="info-title">ДЕТАЛИ ТРАНЗАКЦИИ</div>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr class="info-row">
                    <td class="info-label" style="padding: 10px 0;">СУММА</td>
                    <td class="info-value" style="padding: 10px 0;">{{ $amount ?? '0.00 USD' }}</td>
                </tr>
                <tr class="info-row">
                    <td class="info-label" style="padding: 10px 0;">МЕТОД</td>
                    <td class="info-value" style="padding: 10px 0;">ОПЛАТА КАРТОЙ</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p>Чек об оплате доступен в вашем личном кабинете.</p>
