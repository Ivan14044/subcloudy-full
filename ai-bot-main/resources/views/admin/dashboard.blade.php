@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <form method="GET" class="mb-4">
                <div class="form-row">
                    <div class="col-md-3">
                        <select name="period" class="form-control" onchange="this.form.submit()">
                            <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This week</option>
                            <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This month</option>
                            <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This year</option>
                            <option value="all" {{ $period === 'all' ? 'selected' : '' }}>All time</option>
                            <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom range</option>
                        </select>
                    </div>

                    @if($period === 'custom')
                        <div class="col-md-3">
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Apply</button>
                        </div>
                    @endif
                </div>
            </form>
        </div>
        <div class="col-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalUsers }}</h3>

                    <p>Active users</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $activeSubscriptions }}</h3>

                    <p>Active regular subscriptions</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <a href="{{ route('admin.subscriptions.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-4">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $activeTrialSubscriptions }}</h3>

                    <p>Active trial subscriptions</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <a href="{{ route('admin.subscriptions.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-4">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $totalTransactions }}</h3>

                    <p>Transactions</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <a href="{{ route('admin.subscriptions.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalAmount }} {{ \App\Models\Option::get('currency') }}</h3>

                    <p>Profit</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <a href="{{ route('admin.subscriptions.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
@stop
