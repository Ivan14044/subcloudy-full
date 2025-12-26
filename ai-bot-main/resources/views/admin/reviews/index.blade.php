@extends('adminlte::page')

@section('title', 'Отзывы')

@section('content_header')
    <h1>Отзывы</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Список отзывов</h3>
                    <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary float-right">+ Добавить</a>
                </div>
                <div class="card-body">
                    <table id="reviews-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px">ID</th>
                            <th>Имя</th>
                            <th>Рейтинг</th>
                            <th>Порядок</th>
                            <th>Активен</th>
                            <th style="width: 90px">Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($reviews as $review)
                            <tr>
                                <td>{{ $review->id }}</td>
                                <td>
                                    {{ $review->getTranslation('name', 'ru') }}
                                </td>
                                <td>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa{{ $i <= $review->rating ? 's' : 'r' }} fa-star text-warning"></i>
                                    @endfor
                                </td>
                                <td>{{ $review->order }}</td>
                                <td>
                                    @if ($review->is_active)
                                        <span class="badge badge-success">Да</span>
                                    @else
                                        <span class="badge badge-danger">Нет</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.reviews.edit', $review) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#deleteModal{{ $review->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="deleteModal{{ $review->id }}" tabindex="-1"
                                         role="dialog" aria-labelledby="deleteModalLabel{{ $review->id }}"
                                         aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $review->id }}">
                                                        Подтверждение удаления</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Вы уверены, что хотите удалить этот отзыв?
                                                </div>
                                                <div class="modal-footer">
                                                    <form
                                                        action="{{ route('admin.reviews.destroy', $review->id) }}"
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
            $('#reviews-table').DataTable({
                'order': [[3, 'asc'], [0, 'desc']],
                'columnDefs': [
                    { 'orderable': false, 'targets': 5 }
                ]
            });
        });
    </script>
@endsection

