@extends('adminlte::page')

@section('title', 'Add promocode')

@section('content_header')
    <h1>Add promocode</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Promocode data</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.promocodes.store') }}">
                        @csrf

                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" min="1" max="1000" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}">
                            @error('quantity')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group" id="batch_id_group">
                            <label for="batch_id">Batch</label>
                            <input type="text" name="batch_id" id="batch_id" class="form-control @error('batch_id') is-invalid @enderror" value="{{ old('batch_id') }}" placeholder="Leave empty to auto-generate">
                            @error('batch_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="prefix">Prefix</label>
                            <input type="text" name="prefix" id="prefix" class="form-control @error('prefix') is-invalid @enderror" value="{{ old('prefix') }}">
                            @error('prefix')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="code">Code</label>
                            <div class="input-group">
                                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}">
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
                                <option value="discount" {{ old('type', 'discount') == 'discount' ? 'selected' : '' }}>Discount</option>
                                <option value="free_access" {{ old('type') == 'free_access' ? 'selected' : '' }}>Free access</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="percent_discount">Percent discount</label>
                            <input type="number" min="0" max="100" name="percent_discount" id="percent_discount" class="form-control @error('percent_discount') is-invalid @enderror" value="{{ old('percent_discount', 0) }}">
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
                                    <tr class="{{ $service->is_active ? '' : 'table-warning' }}">
                                        <td>
                                            <input type="checkbox" name="services[{{ $service->id }}][selected]" value="1" {{ old('services.'.$service->id.'.selected') ? 'checked' : '' }}>
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
                                            <input type="number" min="0" class="form-control {{ $errors->has($errKey) ? 'is-invalid' : '' }}" name="services[{{ $service->id }}][free_days]" value="{{ old('services.'.$service->id.'.free_days', 0) }}">
                                            @if($errors->has($errKey))
                                                <div class="invalid-feedback d-block">{{ $errors->first($errKey) }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="form-group">
                            <label for="per_user_limit">Per-user usage limit</label>
                            <input type="number" min="0" name="per_user_limit" id="per_user_limit" class="form-control @error('per_user_limit') is-invalid @enderror" value="{{ old('per_user_limit', 1) }}">
                            @error('per_user_limit')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="usage_limit">Usage limit (0 - unlimited)</label>
                            <input type="number" min="0" name="usage_limit" id="usage_limit" class="form-control @error('usage_limit') is-invalid @enderror" value="{{ old('usage_limit', 0) }}">
                            @error('usage_limit')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="starts_at">Starts at</label>
                                <input type="datetime-local" name="starts_at" id="starts_at" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at') }}">
                                @error('starts_at')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="expires_at">Expires at</label>
                                <input type="datetime-local" name="expires_at" id="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}">
                                @error('expires_at')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select name="is_active" id="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Create</button>
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

            function toggleByQuantity() {
                const qty = parseInt($('#quantity').val() || '1', 10);
                const isSingle = qty <= 1;
                $('#code').closest('.form-group').toggle(isSingle);
                $('#prefix').closest('.form-group').toggle(!isSingle);
                $('#batch_id_group').toggle(!isSingle);
            }

            toggleByType();
            toggleByQuantity();
            $('#type').on('change', toggleByType);
            $('#quantity').on('input change', toggleByQuantity);
        });
    </script>
@endsection


