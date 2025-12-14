@extends('adminlte::page')

@section('title', __('admin.transactions') . ' для подписки #' . $subscription->id)

@section('content_header')
    <h1>{{ __('admin.transactions') }} для подписки #{{ $subscription->id }} <small>{{ $user->email }}</small></h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.transactions') }}</h3>
                    <a href="{{ request()->back_url ? request()->back_url : route('admin.subscriptions.index') }}" class="btn btn-secondary float-right">{{ __('admin.back') }}</a>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="status">{{ __('admin.status') }}:</label>
                                <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                                    <option value="">{{ __('admin.all') }}</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('admin.transaction_status.pending') }}</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('admin.transaction_status.completed') }}</option>
                                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('admin.transaction_status.failed') }}</option>
                                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>{{ __('admin.transaction_status.refunded') }}</option>
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table id="transactions-table" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th style="width: 40px">{{ __('admin.id') }}</th>
                                <th>{{ __('admin.amount') }}</th>
                                <th>{{ __('admin.payment_method') }}</th>
                                <th>{{ __('admin.status') }}</th>
                                <th>{{ __('admin.created_at') }}</th>
                                <th style="width: 100px">{{ __('admin.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->id }}</td>
                                    <td>{{ $transaction->amount }} {{ strtoupper($transaction->currency ?? '') }}</td>
                                    <td>
                                        @php($methodLabels = [
                                            'credit_card' => 'Банковская карта',
                                            'crypto' => 'Криптовалюта',
                                            'free' => 'Бесплатно',
                                        ])
                                        {{ $methodLabels[$transaction->payment_method] ?? ucfirst(str_replace('_',' ', $transaction->payment_method ?? '')) }}
                                    </td>
                                    <td>
                                        @php
                                            $status = $transaction->status ?? 'pending';
                                            $badgeClass = match($status) {
                                                'completed' => 'badge-success',
                                                'pending' => 'badge-warning',
                                                'failed' => 'badge-danger',
                                                'refunded' => 'badge-info',
                                                default => 'badge-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $transaction->status_label }}</span>
                                    </td>
                                    <td data-order="{{ strtotime($transaction->created_at) }}">
                                        {{ \Carbon\Carbon::parse($transaction->created_at)->format('Y-m-d H:i') }}
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editStatusModal{{ $transaction->id }}" title="{{ __('admin.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <div class="modal fade" id="editStatusModal{{ $transaction->id }}" tabindex="-1" role="dialog" aria-labelledby="editStatusModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <form action="{{ route('admin.transactions.update-status', $transaction) }}" method="POST" class="modal-content">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editStatusModalLabel">{{ __('admin.edit_transaction_status') }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="status_{{ $transaction->id }}">{{ __('admin.status') }}</label>
                                                            <select name="status" id="status_{{ $transaction->id }}" class="form-control">
                                                                <option value="pending" {{ ($transaction->status ?? 'pending') === 'pending' ? 'selected' : '' }}>{{ __('admin.transaction_status.pending') }}</option>
                                                                <option value="completed" {{ ($transaction->status ?? 'pending') === 'completed' ? 'selected' : '' }}>{{ __('admin.transaction_status.completed') }}</option>
                                                                <option value="failed" {{ ($transaction->status ?? 'pending') === 'failed' ? 'selected' : '' }}>{{ __('admin.transaction_status.failed') }}</option>
                                                                <option value="refunded" {{ ($transaction->status ?? 'pending') === 'refunded' ? 'selected' : '' }}>{{ __('admin.transaction_status.refunded') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin.cancel') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#transactions-table').DataTable({
                "order": [[0, "desc"]]
            });
        });
    </script>
@endsection
