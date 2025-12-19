@extends('adminlte::page')

@section('title', 'Контент')

@section('content_header')
    <h1>Контент</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Список контента</h3>
                </div>
                <div class="card-body">
                    <table id="contents-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px">ID</th>
                            <th>Код</th>
                            <th>Название</th>
                            <th style="width: 90px">Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($contents as $content)
                            <tr>
                                <td>{{ $content->id }}</td>
                                <td>{{ $content->code }}</td>
                                <td>{{ $content->name }}</td>
                                <td>
                                    <a href="{{ route('admin.contents.edit', $content) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
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
        $(function () {
            $('#contents-table').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "pageLength": 25,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"
                }
            });
        });
    </script>
@endsection

