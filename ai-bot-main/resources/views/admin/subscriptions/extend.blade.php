@extends('adminlte::page')

@section('title', 'Extend Subscriptions')

@section('content_header')
    <h1>Extend Subscriptions</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Mass Extension Form</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.subscriptions.extend') }}">
                        @csrf

                        <div class="form-group">
                            <label for="service_id">Service</label>
                            <select name="service_id" id="service_id" class="form-control @error('service_id') is-invalid @enderror">
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                        {{ $service->code }} (ID: {{ $service->id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="hours">Hours to extend</label>
                            <input type="number"
                                   name="hours"
                                   id="hours"
                                   min="1"
                                   class="form-control @error('hours') is-invalid @enderror"
                                   value="{{ old('hours') }}">
                            @error('hours')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Extend</button>
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
