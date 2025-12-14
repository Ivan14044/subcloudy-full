@extends('adminlte::page')

@section('title', __('admin.add_service_account'))

@section('content_header')
    <h1>{{ __('admin.add_service_account') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.service_account_data') }}</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.service-accounts.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="type">{{ __('admin.service') }}</label>
                            <select name="service_id" id="service_id"
                                    class="form-control @error('service_id') is-invalid @enderror">
                                @foreach($services as $service)
                                    <option
                                            value="{{ $service->id }}"
                                            data-link="{{ e(data_get($service->params, 'link', 'https://google.com/')) }}"
                                            data-title="{{ e(data_get($service->params, 'title', 'SubCloudy')) }}"
                                            {{ old('service_id') == $service->id ? 'selected' : '' }}
                                    >
                                        {{ $service->admin_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="is_active">{{ __('admin.status') }}</label>
                            <select name="is_active" id="is_active"
                                    class="form-control @error('is_active') is-invalid @enderror">
                                <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                                <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>{{ __('admin.inactive') }}</option>
                            </select>
                            @error('is_active')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="expiring_at">Expiring at</label>
                            <input type="datetime-local" name="expiring_at" id="expiring_at"
                                   class="form-control @error('expiring_at') is-invalid @enderror"
                                   value="{{ old('expiring_at') }}">
                            @error('expiring_at')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="max_users">{{ __('admin.max_users_per_account') }}</label>
                            <input type="number" name="max_users" id="max_users" min="1" max="1000"
                                   class="form-control @error('max_users') is-invalid @enderror"
                                   value="{{ old('max_users') }}"
                                   placeholder="{{ __('admin.unlimited') }}">
                            <small class="form-text text-muted">
                                {{ __('admin.max_users_per_account_hint') }}
                            </small>
                            @error('max_users')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group">
                            <label style="font-size: 125%">Credentials</label>
                            
                            <!-- Импорт cookies для Desktop App автологина -->
                            <div class="alert alert-info">
                                <strong>💡 Для автоматического входа в Desktop App:</strong>
                                <ol class="mb-0 pl-3">
                                    <li>Установите расширение <a href="https://chrome.google.com/webstore/detail/editthiscookie/fngmhnnpilhplaeedifhccceomclgfbg" target="_blank">EditThisCookie</a></li>
                                    <li>Откройте сервис и войдите в premium аккаунт</li>
                                    <li>Кликните на иконку расширения → Export</li>
                                    <li>Скопируйте JSON и вставьте ниже</li>
                                </ol>
                            </div>

                            <div class="form-group">
                                <label for="cookies_import">
                                    Cookies Import (JSON)
                                    <small class="text-muted">- для автологина в Desktop приложении</small>
                                </label>
                                <textarea 
                                    name="cookies_import" 
                                    id="cookies_import"
                                    class="form-control @error('cookies_import') is-invalid @enderror"
                                    rows="8"
                                    placeholder='[{"name":"__Secure-next-auth.session-token","value":"...","domain":".chatgpt.com","path":"/","secure":true,"httpOnly":true}]'
                                >{{ old('cookies_import') }}</textarea>
                                <small class="form-text text-muted">
                                    Вставьте экспортированные cookies в формате JSON. Пример:
                                    <code>[{"name":"cookie_name","value":"cookie_value","domain":".example.com"}]</code>
                                </small>
                                @error('cookies_import')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div id="cookies-status" class="mt-2"></div>
                            </div>

                            <hr class="my-3">

                            <div class="form-group">
                                <label for="email">Email <small class="text-muted">(опционально, для справки)</small></label>
                                <input type="text" name="credentials[email]" id="email"
                                       class="form-control @error('credentials.email') is-invalid @enderror"
                                       value="{{ old('credentials.email') }}">
                                @error('credentials.email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="password">Password <small class="text-muted">(опционально, для справки)</small></label>
                                <input type="text" name="credentials[password]" id="password"
                                       class="form-control @error('credentials.password') is-invalid @enderror"
                                       value="{{ old('credentials.password') }}">
                                @error('credentials.password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('admin.create') }}</button>
                        <a href="{{ route('admin.service-accounts.index') }}" class="btn btn-secondary">{{ __('admin.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
      // Валидация и обработка импорта cookies
      (function() {
        const cookiesTextarea = document.getElementById('cookies_import');
        const statusDiv = document.getElementById('cookies-status');
        const form = document.querySelector('form');

        if (cookiesTextarea) {
          // Валидация при вводе
          cookiesTextarea.addEventListener('blur', function() {
            const value = this.value.trim();
            if (!value) {
              statusDiv.innerHTML = '';
              return;
            }

            try {
              const cookies = JSON.parse(value);
              
              if (!Array.isArray(cookies)) {
                throw new Error('Cookies должны быть массивом');
              }

              const count = cookies.length;
              statusDiv.innerHTML = `<div class="alert alert-success">✅ Найдено ${count} cookie(s). Формат корректен!</div>`;
              
              // Показать первые несколько для проверки
              const preview = cookies.slice(0, 3).map(c => `<li><strong>${c.name}</strong> для домена <code>${c.domain || 'N/A'}</code></li>`).join('');
              if (count > 0) {
                statusDiv.innerHTML += `<small class="text-muted">Примеры:<ul class="mb-0">${preview}</ul></small>`;
              }
            } catch (e) {
              statusDiv.innerHTML = `<div class="alert alert-danger">❌ Ошибка формата: ${e.message}</div>`;
            }
          });

          // При отправке формы - объединяем cookies с credentials
          form && form.addEventListener('submit', function(e) {
            const cookiesValue = cookiesTextarea.value.trim();
            
            if (cookiesValue) {
              try {
                const cookies = JSON.parse(cookiesValue);
                
                // Создаем скрытое поле с cookies для отправки
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'credentials[cookies]';
                input.value = JSON.stringify(cookies);
                form.appendChild(input);
                
                console.log('Cookies will be saved:', cookies.length);
              } catch (e) {
                e.preventDefault();
                alert('Ошибка в формате cookies! Проверьте JSON.');
                return false;
              }
            }
          });
        }
      })();

    </script>
@endpush

