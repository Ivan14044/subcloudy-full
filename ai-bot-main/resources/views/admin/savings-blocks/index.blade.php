@extends('adminlte::page')

@section('title', 'Блоки экономии')

@section('content_header')
    <h1>Блоки экономии</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Список блоков</h3>
                    <a href="{{ route('admin.savings-blocks.create') }}" class="btn btn-primary float-right">+ Добавить</a>
                </div>
                <div class="card-body">
                    <table id="blocks-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px">ID</th>
                            <th>Название</th>
                            <th>Сервис</th>
                            <th>Порядок</th>
                            <th>Активен</th>
                            <th style="width: 90px">Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($blocks as $block)
                            <tr>
                                <td>{{ $block->id }}</td>
                                <td>
                                    {{ $block->getTranslation('title', 'ru') }}
                                </td>
                                <td>
                                    {{ $block->service ? $block->service->admin_name : '-' }}
                                </td>
                                <td>{{ $block->order }}</td>
                                <td>
                                    @if ($block->is_active)
                                        <span class="badge badge-success">Да</span>
                                    @else
                                        <span class="badge badge-danger">Нет</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.savings-blocks.edit', $block) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#deleteModal{{ $block->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="deleteModal{{ $block->id }}" tabindex="-1"
                                         role="dialog" aria-labelledby="deleteModalLabel{{ $block->id }}"
                                         aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $block->id }}">
                                                        Подтверждение удаления</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Вы уверены, что хотите удалить этот блок?
                                                </div>
                                                <div class="modal-footer">
                                                    <form
                                                        action="{{ route('admin.savings-blocks.destroy', $block->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Да, удалить
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Отмена
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
        $(document).ready(function() {
            $('#blocks-table').DataTable({
                'order': [[3, 'asc'], [0, 'desc']],
                'columnDefs': [
                    { 'orderable': false, 'targets': 5 }
                ]
            });
        });
    </script>
@endsection

