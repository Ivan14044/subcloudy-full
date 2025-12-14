@extends('adminlte::page')

@section('title', __('admin.dashboard'))

@section('content_header')
    <h1>{{ __('admin.dashboard') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="dashboard-period-filter">
                <form method="GET">
                    <div class="form-row align-items-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="mb-2">
                                <i class="fas fa-calendar-alt mr-2"></i>{{ __('admin.period') }}
                            </label>
                            <select name="period" class="form-control" onchange="this.form.submit()">
                                <option value="today" {{ $period === 'today' ? 'selected' : '' }}>{{ __('admin.today') }}</option>
                                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>{{ __('admin.this_week') }}</option>
                                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>{{ __('admin.this_month') }}</option>
                                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>{{ __('admin.this_year') }}</option>
                                <option value="all" {{ $period === 'all' ? 'selected' : '' }}>{{ __('admin.all_time') }}</option>
                                <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>{{ __('admin.custom_range') }}</option>
                            </select>
                        </div>

                        @if($period === 'custom')
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label class="mb-2">
                                    <i class="fas fa-calendar-check mr-2"></i>{{ __('admin.start_date') }}
                                </label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label class="mb-2">
                                    <i class="fas fa-calendar-times mr-2"></i>{{ __('admin.end_date') }}
                                </label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="mb-2" style="opacity: 0;">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block" style="border-radius: 6px; font-weight: 500; padding: 9px 15px;">
                                    <i class="fas fa-check mr-2"></i>{{ __('admin.apply') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="small-box dashboard-stats-card users-card">
                <div class="inner">
                    <h3>{{ number_format($totalUsers) }}</h3>
                    <p><i class="fas fa-users mr-2"></i>{{ __('admin.active_users') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                    {{ __('admin.more_info') }} <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="small-box dashboard-stats-card subscriptions-card">
                <div class="inner">
                    <h3>{{ number_format($activeSubscriptions) }}</h3>
                    <p><i class="fas fa-crown mr-2"></i>{{ __('admin.active_regular_subscriptions') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-crown"></i>
                </div>
                <a href="{{ route('admin.subscriptions.index') }}" class="small-box-footer">
                    {{ __('admin.more_info') }} <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="small-box dashboard-stats-card trial-card">
                <div class="inner">
                    <h3>{{ number_format($activeTrialSubscriptions) }}</h3>
                    <p><i class="fas fa-clock mr-2"></i>{{ __('admin.active_trial_subscriptions') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('admin.subscriptions.index') }}" class="small-box-footer">
                    {{ __('admin.more_info') }} <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="small-box dashboard-stats-card transactions-card">
                <div class="inner">
                    <h3>{{ number_format($totalTransactions) }}</h3>
                    <p><i class="fas fa-exchange-alt mr-2"></i>{{ __('admin.transactions') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <a href="{{ route('admin.subscriptions.index') }}" class="small-box-footer">
                    {{ __('admin.more_info') }} <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="small-box dashboard-stats-card profit-card">
                <div class="inner">
                    <h3>{{ number_format($totalAmount, 2) }} {{ strtoupper(\App\Models\Option::get('currency', 'USD')) }}</h3>
                    <p><i class="fas fa-dollar-sign mr-2"></i>{{ __('admin.profit') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="{{ route('admin.subscriptions.index') }}" class="small-box-footer">
                    {{ __('admin.more_info') }} <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="small-box dashboard-stats-card conversion-card">
                <div class="inner">
                    <h3>{{ number_format($conversionData['conversion_rate'] ?? 0, 1) }}%</h3>
                    <p><i class="fas fa-chart-line mr-2"></i>{{ __('admin.trial_to_paid_conversion') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{ route('admin.subscriptions.index') }}" class="small-box-footer">
                    {{ __('admin.more_info') }} <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mt-4">
        <div class="col-12 col-lg-6">
            <div class="card dashboard-chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-area mr-2"></i>{{ __('admin.revenue_chart') }}
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card dashboard-chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-2"></i>{{ __('admin.users_chart') }}
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="usersChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card dashboard-chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2"></i>{{ __('admin.subscriptions_chart') }}
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="subscriptionsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card dashboard-chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-percentage mr-2"></i>{{ __('admin.conversion_chart') }}
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="conversionChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function () {
        // Revenue Chart
        var revenueCtx = document.getElementById('revenueChart').getContext('2d');
        var revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($revenueData['labels'] ?? []),
                datasets: [{
                    label: '{{ __('admin.profit') }}',
                    data: @json($revenueData['data'] ?? []),
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(102, 126, 234)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Users Chart
        var usersCtx = document.getElementById('usersChart').getContext('2d');
        var usersChart = new Chart(usersCtx, {
            type: 'bar',
            data: {
                labels: @json($usersData['labels'] ?? []),
                datasets: [{
                    label: '{{ __('admin.active_users') }}',
                    data: @json($usersData['data'] ?? []),
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Subscriptions Chart
        var subscriptionsCtx = document.getElementById('subscriptionsChart').getContext('2d');
        var subscriptionsChart = new Chart(subscriptionsCtx, {
            type: 'line',
            data: {
                labels: @json($subscriptionsData['labels'] ?? []),
                datasets: [{
                    label: '{{ __('admin.active') }}',
                    data: @json($subscriptionsData['active'] ?? []),
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(16, 185, 129)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }, {
                    label: '{{ __('admin.canceled') }}',
                    data: @json($subscriptionsData['canceled'] ?? []),
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(239, 68, 68)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Conversion Chart
        var conversionCtx = document.getElementById('conversionChart').getContext('2d');
        var conversionChart = new Chart(conversionCtx, {
            type: 'line',
            data: {
                labels: @json($conversionChartData['labels'] ?? []),
                datasets: [{
                    label: '{{ __('admin.conversion_percentage') }} (%)',
                    data: @json($conversionChartData['data'] ?? []),
                    borderColor: 'rgb(6, 182, 212)',
                    backgroundColor: 'rgba(6, 182, 212, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(6, 182, 212)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@stop
