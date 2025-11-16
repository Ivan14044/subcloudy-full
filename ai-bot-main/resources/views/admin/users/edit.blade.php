@extends('adminlte::page')

@section('title', 'Edit user #' . $user->id)

@section('content_header')
    <h1>Edit user #{{ $user->id }}</h1>
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
                    <h3 class="card-title">User data</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="is_blocked">Status</label>
                            <select name="is_blocked" id="is_blocked" class="form-control @error('is_blocked') is-invalid @enderror">
                                <option value="0" {{ old('is_blocked', $user->is_blocked) == 0 ? 'selected' : '' }}>Active</option>
                                <option value="1" {{ old('is_blocked', $user->is_blocked) == 1 ? 'selected' : '' }}>Blocked</option>
                                <option value="2" {{ old('is_blocked', $user->is_pending && !$user->is_blocked ? 2 : 0) == 2 ? 'selected' : '' }}>Pending</option>
                            </select>
                            @error('is_blocked')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">New password</label>
                            <small>Leave empty to keep current</small>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="submit" name="save" class="btn btn-primary">Save & Continue</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Subscriptions list</h3>
                    <a href="{{ route('admin.subscriptions.create', ['user' => $user->id, 'return_url' => url()->current()]) }}"
                       class="btn btn-primary float-right">+ Add</a>
                </div>
                <div class="card-body">
                    <table id="subscriptions-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 30px">ID</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Payment Info</th>
                            <th>Action</th>
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
                                    <span class="badge badge-danger">Canceled</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                                @if($subscription->is_trial)
                                    <br>
                                    <span class="badge badge-primary">Trial</span>
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
                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#toggleStatusModal{{ $subscription->id }}" title="Cancel Subscription">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @elseif ($subscription->status == \App\Models\Subscription::STATUS_CANCELED)
                                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#toggleStatusModal{{ $subscription->id }}" title="Activate Subscription">
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
                                                    {{ $subscription->status === 'active' ? 'Cancel Subscription' : 'Activate Subscription' }}
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body">
                                                Are you sure you want to
                                                {{ $subscription->status === 'active' ? 'cancel' : 'activate' }}
                                                this subscription?
                                            </div>

                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-{{ $subscription->status === 'active' ? 'danger' : 'success' }}">
                                                    Yes, {{ $subscription->status === 'active' ? 'Cancel' : 'Activate' }}
                                                </button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
                                                <h5 class="modal-title" id="nextPaymentModalLabel">Set Next Payment Date</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="next_payment_at_{{ $subscription->id }}">Next Payment At</label>
                                                    <input type="datetime-local" name="next_payment_at" id="next_payment_at_{{ $subscription->id }}"
                                                           class="form-control"
                                                           value="{{ \Carbon\Carbon::parse($subscription->next_payment_at)->format('Y-m-d\TH:i') }}">
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Save</button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="modal fade" id="deleteModal{{ $subscription->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete this subscription?
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ route('admin.subscriptions.destroy', $subscription) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>

                                                    @if(!empty($user))
                                                    <input type="hidden" name="return_url" value="{{ url()->current() }}">
                                                    @endif
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
@endsection
