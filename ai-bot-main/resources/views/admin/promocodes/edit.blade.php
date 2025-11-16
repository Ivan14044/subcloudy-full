@extends('adminlte::page')

@section('title', 'Edit promocode #' . $promocode->id)

@section('content_header')
    <h1>Edit promocode #{{ $promocode->id }}</h1>
@stop

@section('content')
    <div class="row">
        @if(session('success'))
            <div class="col-12">
                <div class="alert alert-success">{{ session('success') }}</div>
            </div>
        @endif

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Promocode data</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.promocodes.update', $promocode) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="code">Code</label>
                            <div class="input-group">
                                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $promocode->code) }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-secondary" id="generate-code" title="Сгенерировать">Сгенерировать</button>
                                </div>
                            </div>
                            @error('code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control @error('type') is-invalid @enderror">
                                <option value="discount" {{ old('type', $promocode->type ?? 'discount') == 'discount' ? 'selected' : '' }}>Discount</option>
                                <option value="free_access" {{ old('type', $promocode->type ?? 'discount') == 'free_access' ? 'selected' : '' }}>Free access</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="percent_discount">Percent discount</label>
                            <input type="number" min="0" max="100" name="percent_discount" id="percent_discount" class="form-control @error('percent_discount') is-invalid @enderror" value="{{ old('percent_discount', $promocode->percent_discount) }}">
                            @error('percent_discount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th style="width: 40px">
                                        <input type="checkbox" id="select-all-services">
                                    </th>
                                    <th>Service</th>
                                    <th style="width: 160px">Free days</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($services as $service)
                                    @php($pivot = $promocode->services->firstWhere('id', $service->id))
                                    <tr class="{{ $service->is_active ? '' : 'table-warning' }}">
                                        <td>
                                            <input type="checkbox" name="services[{{ $service->id }}][selected]" value="1" {{ old('services.'.$service->id.'.selected', $pivot ? 1 : 0) ? 'checked' : '' }}>
                                            <input type="hidden" name="services[{{ $service->id }}][id]" value="{{ $service->id }}">
                                        </td>
                                        <td>
                                            {{ $service->getTranslation('name', 'en') ?? $service->admin_name ?? ('Service #'.$service->id) }}
                                            @unless($service->is_active)
                                                <span class="badge badge-secondary ml-2">Inactive</span>
                                            @endunless
                                        </td>
                                        <td>
                                            @php($errKey = 'services.' . $service->id . '.free_days')
                                            <input type="number" min="0" class="form-control {{ $errors->has($errKey) ? 'is-invalid' : '' }}" name="services[{{ $service->id }}][free_days]" value="{{ old('services.'.$service->id.'.free_days', optional($pivot)->pivot->free_days ?? 0) }}">
                                            @if($errors->has($errKey))
                                                <div class="invalid-feedback d-block">{{ $errors->first($errKey) }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($promocode->batch_id)
                            <div class="form-group">
                                <label for="prefix">Prefix</label>
                                <input type="text" name="prefix" id="prefix" class="form-control @error('prefix') is-invalid @enderror" value="{{ old('prefix', $promocode->prefix) }}">
                                @error('prefix')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="per_user_limit">Per-user usage limit</label>
                            <input type="number" min="0" name="per_user_limit" id="per_user_limit" class="form-control @error('per_user_limit') is-invalid @enderror" value="{{ old('per_user_limit', $promocode->per_user_limit ?? 1) }}">
                            @error('per_user_limit')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="usage_limit">Usage limit (0 - unlimited)</label>
                            <input type="number" min="0" name="usage_limit" id="usage_limit" class="form-control @error('usage_limit') is-invalid @enderror" value="{{ old('usage_limit', $promocode->usage_limit) }}">
                            @error('usage_limit')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="starts_at">Starts at</label>
                                <input type="datetime-local" name="starts_at" id="starts_at" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at', optional($promocode->starts_at)->format('Y-m-d\TH:i')) }}">
                                @error('starts_at')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="expires_at">Expires at</label>
                                <input type="datetime-local" name="expires_at" id="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at', optional($promocode->expires_at)->format('Y-m-d\TH:i')) }}">
                                @error('expires_at')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select name="is_active" id="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                <option value="1" {{ old('is_active', $promocode->is_active) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active', $promocode->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="submit" name="save" class="btn btn-primary">Save & Continue</button>
                            <a href="{{ route('admin.promocodes.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

 @section('js')
     <script>
         $(function () {
             $('#select-all-services').on('change', function () {
                 const checked = this.checked;
                 $('input[type="checkbox"][name$="[selected]"]').prop('checked', checked);
             });

             function generateCode(length) {
                 const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                 let out = '';
                 for (let i = 0; i < length; i++) {
                     out += chars.charAt(Math.floor(Math.random() * chars.length));
                 }
                 return out;
             }

             $('#generate-code').on('click', function () {
                 $('#code').val(generateCode(8));
             });

             function toggleByType() {
                 const type = $('#type').val();
                 const isDiscount = type === 'discount';
                 // Percent field visible only for discount
                 $('#percent_discount').closest('.form-group').toggle(isDiscount);
                 // Services matrix visible only for free_access
                 const showServices = !isDiscount;
                 $('#select-all-services').closest('table').closest('.table-responsive').toggle(showServices);
             }

             toggleByType();
             $('#type').on('change', toggleByType);
         });
     </script>
 @endsection


