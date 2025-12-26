@extends('emails.base')

@section('content_badge')
<div align="center">
    <div class="badge badge-info">🔐 {{ trans('email.reset_password_badge') ?: 'Сброс пароля' }}</div>
</div>
@endsection

@section('body')
<p>{{ trans('email.reset_password_text') ?: 'Вы запросили смену пароля для вашего аккаунта.' }}</p>

<div class="btn-container">
    <a href="{{ $url ?? '#' }}" class="btn">{{ trans('email.reset_password_button') ?: 'Сбросить пароль' }}</a>
</div>

<p style="color: #9ca3af; font-size: 14px;">{{ trans('email.reset_password_ignore') ?: 'Если вы не запрашивали смену пароля, просто проигнорируйте это письмо.' }}</p>
<p style="color: #9ca3af; font-size: 12px;">{{ trans('email.reset_password_expiry') ?: 'Ссылка действительна в течение 60 минут.' }}</p>
@endsection
