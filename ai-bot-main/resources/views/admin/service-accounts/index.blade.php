@extends('adminlte::page')

@section('title', __('admin.service_accounts'))

@section('content_header')
    <h1>{{ __('admin.service_accounts') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.service_accounts_list') }}</h3>
                    <a href="{{ route('admin.service-accounts.create') }}" class="btn btn-primary float-right">+ {{ __('admin.add') }}</a>
                </div>
                <div class="card-body">
                    <table id="service-accounts-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px">{{ __('admin.id') }}</th>
                            <th>{{ __('admin.service') }}</th>
                            <th>{{ __('admin.login') }}</th>
                            <th>{{ __('admin.status') }}</th>
                            <th>{{ __('admin.users') }}</th>
                            <th>{{ __('admin.used') }}</th>
                            <th>{{ __('admin.last_used_at') }}</th>
                            <th>{{ __('admin.created_at') }}</th>
                            <th>{{ __('admin.expiring_at') }}</th>
                            <th style="width: 70px">{{ __('admin.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($serviceAccounts as $serviceAccount)
                            <tr>
                                <td>{{ $serviceAccount->id }}</td>
                                <td>
                                    @if($serviceAccount->service->logo)
                                        <img src="{{ asset($serviceAccount->service->logo) }}"
                                             alt="Logo"
                                             class="mr-1 rounded"
                                             width="36" height="36">
                                    @endif
                                    {{ $serviceAccount->service->admin_name }}
                                </td>
                                <td>
                                    @if(!empty($serviceAccount->credentials['email']))
                                        <span class="text-muted">{{ $serviceAccount->credentials['email'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$serviceAccount->is_active)
                                        <span class="badge badge-danger">{{ __('admin.inactive') }}</span>
                                    @else
                                        <span class="badge badge-success">{{ __('admin.active') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $usersCount = $serviceAccount->users_count ?? 0;
                                        $maxUsers = $serviceAccount->max_users;
                                    @endphp
                                    <span class="text-muted">
                                        <strong>{{ $usersCount }}</strong>
                                        @if($maxUsers !== null)
                                            <span>/ {{ $maxUsers }}</span>
                                            @if($usersCount >= $maxUsers)
                                                <span class="badge badge-warning ml-1" title="{{ __('admin.limit_reached') }}">!</span>
                                            @elseif($usersCount >= ($maxUsers * 0.8))
                                                <span class="badge badge-info ml-1" title="{{ __('admin.near_limit') }}">~</span>
                                            @endif
                                        @else
                                            <span>/ ∞</span>
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $serviceAccount->used }}</td>
                                <td data-order="{{ strtotime($serviceAccount->last_used_at) }}">
                                    {{ \Carbon\Carbon::parse($serviceAccount->last_used_at)->format('Y-m-d H:i') }}
                                </td>
                                <td data-order="{{ strtotime($serviceAccount->created_at) }}">
                                    {{ \Carbon\Carbon::parse($serviceAccount->created_at)->format('Y-m-d H:i') }}
                                </td>
                                <td data-order="{{ $serviceAccount->expiring_at ? strtotime($serviceAccount->expiring_at) : 0 }}">
                                    {{ $serviceAccount->expiring_at ? \Carbon\Carbon::parse($serviceAccount->expiring_at)->format('Y-m-d H:i') : null }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.service-accounts.edit', $serviceAccount) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#deleteModal{{ $serviceAccount->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="deleteModal{{ $serviceAccount->id }}" tabindex="-1"
                                         role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel">{{ __('admin.confirm_deletion') }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    {{ __('admin.are_you_sure_delete_service_account') }}
                                                </div>
                                                <div class="modal-footer">
                                                    <form
                                                        action="{{ route('admin.service-accounts.destroy', $serviceAccount) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">{{ __('admin.yes_delete') }}
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">{{ __('admin.cancel') }}
                                                    </button>
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

@section('js')
    <script>
        $(document).ready(function () {
            $('#service-accounts-table').DataTable({
                "order": [[0, "desc"]],
                "columnDefs": [
                    {"orderable": false, "targets": 8}
                ]
            });
        });
    </script>
@endsection
