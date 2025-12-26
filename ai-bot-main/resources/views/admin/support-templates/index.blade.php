@extends('adminlte::page')

@section('title', 'Шаблоны ответов')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Шаблоны ответов</h1>
        <a href="{{ route('admin.support-templates.create') }}" class="btn btn-primary">+ Добавить шаблон</a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 50px">ID</th>
                        <th>Название (RU)</th>
                        <th>Название (EN)</th>
                        <th style="width: 100px">Активен</th>
                        <th style="width: 100px">Сортировка</th>
                        <th style="width: 150px">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                        <tr>
                            <td>{{ $template->id }}</td>
                            <td>{{ $template->getTranslation('title', 'ru') ?? '-' }}</td>
                            <td>{{ $template->getTranslation('title', 'en') ?? '-' }}</td>
                            <td>
                                @if($template->is_active)
                                    <span class="badge badge-success">Да</span>
                                @else
                                    <span class="badge badge-danger">Нет</span>
                                @endif
                            </td>
                            <td>{{ $template->sort_order }}</td>
                            <td>
                                <a href="{{ route('admin.support-templates.edit', $template->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.support-templates.destroy', $template->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить этот шаблон?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">
                {{ $templates->links() }}
            </div>
        </div>
    </div>
@stop
