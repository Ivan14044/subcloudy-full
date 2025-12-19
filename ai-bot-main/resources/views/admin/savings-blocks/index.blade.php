@extends('adminlte::page')

@section('title', 'Блоки экономии')

@section('content_header')
    <h1>Блоки экономии</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Список блоков экономии</h3>
                    <a href="{{ route('admin.savings-blocks.create') }}" class="btn btn-primary float-right">
                        <i class="fas fa-plus"></i> Добавить блок
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Заголовок</th>
                            <th>Сервис</th>
                            <th>Язык</th>
                            <th>Порядок</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($blocks as $block)
                            <tr>
                                <td>{{ $block->id }}</td>
                                <td>{{ $block->title }}</td>
                                <td>{{ $block->service ? $block->service->name : '-' }}</td>
                                <td>{{ $block->locale }}</td>
                                <td>{{ $block->order }}</td>
                                <td>
                                    <span class="badge {{ $block->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $block->is_active ? 'Активен' : 'Неактивен' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.savings-blocks.edit', $block) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.savings-blocks.destroy', $block) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить блок?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

