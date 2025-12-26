@extends('adminlte::page')

@section('title', __('admin.edit_user') . ' #' . $user->id)

@section('content_header')
    <h1>{{ __('admin.edit_user') }} #{{ $user->id }}</h1>
@stop

@section('content')
    <div class="row">
        @if(session('success'))
            <div class="col-12">
                <div class="alert alert-success">{{ session('success') }}</div>
            </div>
        @endif

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.user_data') }}</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">{{ __('admin.name') }}</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">{{ __('admin.email') }}</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="is_blocked">{{ __('admin.status') }}</label>
                            <select name="is_blocked" id="is_blocked" class="form-control @error('is_blocked') is-invalid @enderror">
                                <option value="0" {{ old('is_blocked', $user->is_blocked) == 0 ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                                <option value="1" {{ old('is_blocked', $user->is_blocked) == 1 ? 'selected' : '' }}>{{ __('admin.blocked') }}</option>
                                <option value="2" {{ old('is_blocked', $user->is_pending && !$user->is_blocked ? 2 : 0) == 2 ? 'selected' : '' }}>{{ __('admin.pending') }}</option>
                            </select>
                            @error('is_blocked')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">{{ __('admin.new_password') }}</label>
                            <small>{{ __('admin.leave_empty_to_keep_current') }}</small>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">{{ __('admin.confirm_password') }}</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        </div>

                        <button type="submit" name="save" class="btn btn-primary">{{ __('admin.save_continue') }}</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">{{ __('admin.cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.subscriptions_list') }}</h3>
                    <a href="{{ route('admin.subscriptions.create', ['user' => $user->id, 'return_url' => url()->current()]) }}"
                       class="btn btn-primary float-right">+ {{ __('admin.add') }}</a>
                </div>
                <div class="card-body">
                    <table id="subscriptions-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 30px">{{ __('admin.id') }}</th>
                            <th>{{ __('admin.service_label') }}</th>
                            <th>{{ __('admin.status') }}</th>
                            <th>{{ __('admin.amount') }}</th>
                            <th>{{ __('admin.payment_info') }}</th>
                            <th>{{ __('admin.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($subscriptions as $subscription)
                        <tr>
                            <td>{{ $subscription->id }}</td>
                            <td>
                                <div class="d-flex" style="gap: 5px">
                                    <img src="{{ url($subscription->service->logo) }}"
                                         title="{{ $subscription->service->code }}"
                                         class="img-fluid img-bordered" style="width: 35px;">
                                </div>
                            </td>

                            <td>
                                @if($subscription->status != \App\Models\Subscription::STATUS_ACTIVE)
                                    <span class="badge badge-danger">{{ __('admin.canceled') }}</span>
                                @else
                                    <span class="badge badge-success">{{ __('admin.active') }}</span>
                                @endif
                                @if($subscription->is_trial)
                                    <br>
                                    <span class="badge badge-primary">{{ __('admin.trial') }}</span>
                                @endif
                            </td>
                            @php
                                $last = $subscription->transactions->sortByDesc('created_at')->first();
                            @endphp
                            <td>
                                {{ $last?->amount ?? '-' }} {{ strtoupper($last?->currency ?? '') }}
                                <br>
                                <small>{{ $subscription->payment_method_label }}</small>
                            </td>
                            <td data-order="{{ strtotime($subscription->next_payment_at) }}">
                                <i class="fas fa-calendar-plus text-secondary mr-1" title="Next payment at"></i> {{ \Carbon\Carbon::parse($subscription->next_payment_at)->format('Y-m-d H:i') }} <br>
                                <i class="fas fa-receipt text-secondary mr-1" title="Last payment at"></i> {{ $last?->created_at?->format('Y-m-d H:i') ?? '-' }}
                            </td>
                            <td class="d-flex flex-wrap align-items-center" style="gap: 5px; max-width: 110px; overflow: hidden;">
                                <a href="{{ route('admin.subscriptions.edit', $subscription) . (!empty($user) ? '?back_url=' . url()->current() : '') }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="{{ route('admin.subscriptions.transactions', $subscription) . (!empty($user) ? '?back_url=' . url()->current() : '') }}"
                                   class="btn btn-sm btn-{{ $subscription->transactions()->count() ? 'success' : 'secondary' }}">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </a>

                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#nextPaymentModal{{ $subscription->id }}">
                                    <i class="far fa-clock"></i>
                                </button>

                                @if ($subscription->status == \App\Models\Subscription::STATUS_ACTIVE)
                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#toggleStatusModal{{ $subscription->id }}" title="{{ __('admin.cancel_subscription') }}">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @elseif ($subscription->status == \App\Models\Subscription::STATUS_CANCELED)
                                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#toggleStatusModal{{ $subscription->id }}" title="{{ __('admin.activate_subscription') }}">
                                        <i class="fas fa-play"></i>
                                    </button>
                                @endif

                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $subscription->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <div class="modal fade" id="toggleStatusModal{{ $subscription->id }}" tabindex="-1" role="dialog" aria-labelledby="toggleStatusModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <form action="{{ route('admin.subscriptions.toggle-status', $subscription) }}" method="POST" class="modal-content">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header">
                                                <h5 class="modal-title" id="toggleStatusModalLabel">
                                                    {{ $subscription->status === 'active' ? __('admin.cancel_subscription') : __('admin.activate_subscription') }}
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body">
                                                {{ $subscription->status === 'active' ? __('admin.are_you_sure_cancel_subscription') : __('admin.are_you_sure_activate_subscription') }}
                                            </div>

                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-{{ $subscription->status === 'active' ? 'danger' : 'success' }}">
                                                    {{ $subscription->status === 'active' ? __('admin.cancel_subscription') : __('admin.activate_subscription') }}
                                                </button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin.cancel') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="modal fade" id="nextPaymentModal{{ $subscription->id }}" tabindex="-1" role="dialog" aria-labelledby="nextPaymentModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <form action="{{ route('admin.subscriptions.update-next-payment', $subscription) }}" method="POST" class="modal-content">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header">
                                                <h5 class="modal-title" id="nextPaymentModalLabel">{{ __('admin.set_next_payment_date') }}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="next_payment_at_{{ $subscription->id }}">{{ __('admin.next_payment_at') }}</label>
                                                    <input type="datetime-local" name="next_payment_at" id="next_payment_at_{{ $subscription->id }}"
                                                           class="form-control"
                                                           value="{{ \Carbon\Carbon::parse($subscription->next_payment_at)->format('Y-m-d\TH:i') }}">
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin.cancel') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="modal fade" id="deleteModal{{ $subscription->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel">{{ __('admin.confirm_deletion') }}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                {{ __('admin.are_you_sure_delete_subscription') }}
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ route('admin.subscriptions.destroy', $subscription) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">{{ __('admin.yes_delete') }}</button>

                                                    @if(!empty($user))
                                                    <input type="hidden" name="return_url" value="{{ url()->current() }}">
                                                    @endif
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin.cancel') }}</button>
                                            </div>
                                        </div>
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

    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="activity-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="login-history-tab" data-toggle="pill" href="#login-history" role="tab" aria-controls="login-history" aria-selected="true">{{ __('admin.login_history') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="subscription-history-tab" data-toggle="pill" href="#subscription-history" role="tab" aria-controls="subscription-history" aria-selected="false">{{ __('admin.subscription_history') }}</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="activity-tabs-content">
                        <div class="tab-pane fade show active" id="login-history" role="tabpanel" aria-labelledby="login-history-tab">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin.date_time') }}</th>
                                        <th>{{ __('admin.ip_address') }}</th>
                                        <th>{{ __('admin.user_agent') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loginHistory as $login)
                                        <tr>
                                            <td>{{ $login->created_at->format('Y-m-d H:i:s') }}</td>
                                            <td><code>{{ $login->ip_address }}</code></td>
                                            <td><small class="text-muted" style="word-break: break-all;">{{ $login->user_agent }}</small></td>
                                        </tr>
                                    @endforeach
                                    @if($loginHistory->isEmpty())
                                        <tr>
                                            <td colspan="3" class="text-center">{{ __('admin.no_records') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="subscription-history" role="tabpanel" aria-labelledby="subscription-history-tab">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin.date_time') }}</th>
                                        <th>{{ __('admin.subscription_label') }}</th>
                                        <th>{{ __('admin.event') }}</th>
                                        <th>{{ __('admin.details') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subscriptionLogs as $log)
                                        <tr>
                                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                            <td>
                                                @if($log->subscription)
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ url($log->subscription->service->logo) }}" class="mr-2" style="width: 20px;">
                                                        <span>#{{ $log->subscription_id }} ({{ $log->subscription->service->code }})</span>
                                                    </div>
                                                @else
                                                    #{{ $log->subscription_id }} ({{ __('admin.deleted') }})
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = match($log->event) {
                                                        'created' => 'success',
                                                        'updated' => 'info',
                                                        'deleted' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge badge-{{ $badgeClass }}">{{ __('admin.' . $log->event) }}</span>
                                            </td>
                                            <td>
                                                @if($log->event === 'updated' && $log->old_data && $log->new_data)
                                                    <ul class="mb-0 pl-3 small">
                                                        @foreach($log->new_data as $key => $value)
                                                            <li>
                                                                <strong>{{ $key }}:</strong> 
                                                                <span class="text-muted">{{ is_array($log->old_data[$key] ?? null) ? json_encode($log->old_data[$key]) : ($log->old_data[$key] ?? '-') }}</span> 
                                                                &rarr; 
                                                                <span class="text-success font-weight-bold">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    @php
                                                        $data = $log->old_data ?: $log->new_data;
                                                        // Filter only important fields for display to avoid clutter
                                                        $displayData = array_intersect_key($data, array_flip(['status', 'next_payment_at', 'is_auto_renew', 'payment_method']));
                                                    @endphp
                                                    <ul class="mb-0 pl-3 small">
                                                        @foreach($displayData as $key => $value)
                                                            <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if($subscriptionLogs->isEmpty())
                                        <tr>
                                            <td colspan="4" class="text-center">{{ __('admin.no_records') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

