@extends('adminlte::page')

@section('title', __('admin.services'))

@section('content_header')
    <h1>{{ __('admin.services') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.services') }}</h3>
                    <a href="{{ route('admin.services.create') }}" class="btn btn-primary float-right">+ {{ __('admin.add') }}</a>
                </div>
                <div class="card-body">
                    <table id="services-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px">{{ __('admin.position') }}</th>
                            <th style="width: 40px">{{ __('admin.id') }}</th>
                            <th>{{ __('admin.name') }}</th>
                            <th>{{ __('admin.status') }}</th>
                            <th>{{ __('admin.created_at') }}</th>
                            <th style="width: 90px">{{ __('admin.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($services as $service)
                            <tr>
                                <td>{{ $service->position }}</td>
                                <td>{{ $service->id }}</td>
                                <td>
                                    @if($service->logo)
                                        <img src="{{ asset($service->logo) }}"
                                             alt="Logo"
                                             class="mr-1 rounded"
                                             width="36" height="36">
                                    @endif
                                    {{ $service->admin_name }}
                                </td>
                                <td>
                                    @if(!$service->is_active)
                                        <span class="badge badge-danger">{{ __('admin.inactive') }}</span>
                                    @else
                                        <span class="badge badge-success">{{ __('admin.active') }}</span>
                                    @endif
                                </td>
                                <td data-order="{{ strtotime($service->created_at) }}">
                                    {{ \Carbon\Carbon::parse($service->created_at)->format('Y-m-d H:i') }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $service->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="deleteModal{{ $service->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel">{{ __('admin.confirm_deletion') }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    {{ __('admin.are_you_sure_delete') }}
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">{{ __('admin.yes_delete') }}</button>
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
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#services-table').DataTable({
                "order": [[0, "asc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 5 }
                ]
            });
        });
    </script>
@endsection
