@extends('adminlte::page')

@section('title', 'Add user')

@section('content_header')
    <h1>Add user</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User data</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="is_blocked">Status</label>
                            <select name="is_blocked" id="is_blocked" class="form-control @error('is_blocked') is-invalid @enderror">
                                <option value="0" {{ old('is_blocked') == 0 ? 'selected' : '' }}>Active</option>
                                <option value="1" {{ old('is_blocked') == 1 ? 'selected' : '' }}>Blocked</option>
                            </select>
                            @error('is_blocked')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">New password</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="days">Subscription Duration (Days)</label>
                            <input type="number" step="1" min="1" name="days" id="days"
                                   class="form-control @error('days') is-invalid @enderror"
                                   value="{{ old('days') }}">
                            @error('days')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Services</label>
                            @foreach($services as $service)
                                <div class="form-check d-flex align-items-center mb-2">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="services[]"
                                           value="{{ $service->id }}"
                                           id="service_{{ $service->id }}">
                                    <label class="form-check-label ms-2" for="service_{{ $service->id }}">
                                        <img src="{{ asset($service->logo) }}" alt="{{ $service->code }}"
                                             style="width:20px; height:20px; object-fit:contain; margin-right:5px;">
                                        {{ $service->code }}
                                    </label>
                                </div>
                            @endforeach
                            @error('services')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Create</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
