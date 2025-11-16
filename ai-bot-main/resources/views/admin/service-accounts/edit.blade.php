@extends('adminlte::page')

@section('title', 'Edit service account #' . $serviceAccount->id)

@section('content_header')
    <h1>Edit service account #{{ $serviceAccount->id }}</h1>
@stop

@section('content')
    <div class="row">
        @if(session('success'))
            <div class="col-12">
                <div class="alert alert-success">{{ session('success') }}</div>
            </div>
        @endif

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Service account data</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.service-accounts.update', $serviceAccount) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="type">Service</label>
                            <select name="service_id" id="service_id"
                                    class="form-control @error('service_id') is-invalid @enderror">
                                @foreach($services as $service)
                                    <option
                                        value="{{ $service->id }}" {{ old('service_id', $serviceAccount->service_id) == $service->id ? 'selected' : '' }}>
                                        {{ $service->admin_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select name="is_active" id="is_active"
                                    class="form-control @error('is_active') is-invalid @enderror">
                                <option
                                    value="1" {{ old('is_active', $serviceAccount->is_active) == 1 ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option
                                    value="0" {{ old('is_active', $serviceAccount->is_active) == 0 ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                            @error('is_active')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="expiring_at">Expiring at</label>
                            <input type="datetime-local" name="expiring_at" id="expiring_at"
                                   class="form-control @error('expiring_at') is-invalid @enderror"
                                   value="{{ old('expiring_at', $serviceAccount->expiring_at) }}">
                            @error('expiring_at')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="profile_id">Profile ID</label>
                            <div class="input-group">
                                <input type="text" name="profile_id" id="profile_id" readonly
                                       class="form-control @error('profile_id') is-invalid @enderror"
                                       value="{{ old('profile_id', $serviceAccount->profile_id) }}">
                                <input type="hidden" name="session_pid" id="session_pid" value="{{ old('session_pid') }}">
                                <input type="hidden" name="session_port" id="session_port" value="{{ old('session_port') }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" id="start-browser-session">
                                        Start Browser
                                    </button>
                                </div>
                            </div>
                            @error('profile_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group">
                            <label style="font-size: 125%">Credentials</label>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="text" name="credentials[email]" id="email"
                                       class="form-control @error('credentials.email') is-invalid @enderror"
                                       value="{{ old('credentials.email', $serviceAccount->credentials['email'] ?? '') }}">
                                @error('credentials.email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="text" name="credentials[password]" id="password"
                                       class="form-control @error('credentials.password') is-invalid @enderror"
                                       value="{{ old('credentials.password', $serviceAccount->credentials['password'] ?? '') }}">
                                @error('credentials.password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="submit" name="save" class="btn btn-primary">Save & Continue</button>
                        <a href="{{ route('admin.service-accounts.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        (function(){
            const btn = document.getElementById('start-browser-session');
            const form = document.querySelector('form[action="{{ route('admin.service-accounts.update', $serviceAccount) }}"]');
            const imageInput = document.getElementById('profile_id');
            const pidInput = document.getElementById('session_pid');
            const portInput = document.getElementById('session_port');

            function setFormDisabled(disabled) {
                if (!form) return;
                form.style.opacity = disabled ? '0.5' : '1';
                if (disabled) form.classList.add('pe-none'); else form.classList.remove('pe-none');
                const controls = form.querySelectorAll('input, select, textarea, button');
                controls.forEach(el => {
                    if (disabled) {
                        el.setAttribute('disabled', 'disabled');
                    } else {
                        el.removeAttribute('disabled');
                    }
                });
                const links = form.querySelectorAll('a');
                links.forEach(a => {
                    if (disabled) {
                        a.classList.add('disabled');
                        a.style.pointerEvents = 'none';
                    } else {
                        a.classList.remove('disabled');
                        a.style.pointerEvents = '';
                    }
                });
            }

            btn && btn.addEventListener('click', async function() {
                setFormDisabled(true);

                try {
                    function generateProfileId() {
                        if (window.crypto && window.crypto.getRandomValues) {
                            const bytes = new Uint8Array(16);
                            window.crypto.getRandomValues(bytes);
                            return Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join('');
                        }
                        const chars = 'abcdef0123456789';
                        let s = '';
                        for (let i = 0; i < 32; i++) s += chars[Math.floor(Math.random() * chars.length)];
                        return s;
                    }

                    const profile = generateProfileId();
                    if (imageInput) imageInput.value = profile;

                    const res = await fetch("{{ route('admin.browser-sessions.start-json') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ profile })
                    });
                    if (!res.ok) throw new Error('Failed to start');

                    const data = await res.json();
                    if (!data.ok) throw new Error(data.error || 'Failed to start');

                    if (pidInput && data.pid) pidInput.value = String(data.pid);
                    if (portInput && data.port) portInput.value = String(data.port);

                    if (data.url) {
                        setTimeout(() => {
                            const w = window.open(data.url, '_blank', 'noopener');
                            setFormDisabled(false);
                        }, 5000);
                    } else {
                        setFormDisabled(false);
                    }
                } catch (e) {
                    setFormDisabled(false);
                    alert('Failed to start browser session');
                }
            });
        })();
    </script>
    <style>
        .pe-none { pointer-events: none; }
    </style>
@endpush
