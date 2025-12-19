@extends('adminlte::page')

@section('title', 'Отзывы')

@section('content_header')
    <h1>Отзывы</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Список отзывов</h3>
                    <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary float-right">
                        <i class="fas fa-plus"></i> Добавить отзыв
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя (RU)</th>
                            <th>Рейтинг</th>
                            <th>Локализации</th>
                            <th>Порядок</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($reviews as $review)
                            <tr>
                                <td>{{ $review->id }}</td>
                                <td>{{ $review->getTranslation('name', 'ru') ?? '—' }}</td>
                                <td>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-secondary' }}"></i>
                                    @endfor
                                </td>
                                <td>
                                    @foreach(config('langs') as $code => $flag)
                                        @if($review->getTranslation('name', $code))
                                            <span class="badge badge-info mr-1">
                                                <span class="flag-icon flag-icon-{{ $flag }}"></span> {{ strtoupper($code) }}
                                            </span>
                                        @endif
                                    @endforeach
                                </td>
                                <td>{{ $review->order }}</td>
                                <td>
                                    <span class="badge {{ $review->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $review->is_active ? 'Активен' : 'Неактивен' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить отзыв?')">
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

