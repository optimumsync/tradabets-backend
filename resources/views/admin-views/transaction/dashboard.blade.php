@extends('_layouts.master')

@section('main-title', 'Admin Dashboard')

{{-- Add Litepicker CSS for the date range filter --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css" />
@endpush

@section('main-content')

{{-- Include Chart.js and Litepicker from CDNs --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js">
</script>
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>

{{-- START: Dedicated CSS for the Admin Dashboard --}}
<style>
.dashboard-stat-card {
    background-color: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.dashboard-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.dashboard-stat-card .stat-icon {
    font-size: 2.5rem;
    width: 60px;
    height: 60px;
    line-height: 60px;
    text-align: center;
    border-radius: 50%;
    background-color: rgba(0, 123, 255, 0.1);
    color: #007bff;
    margin-right: 1rem;
}

.dashboard-stat-card .stat-content .stat-title {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
}

.dashboard-stat-card .stat-content .stat-number {
    font-size: 2rem;
    color: #343a40;
    font-weight: 700;
}

.chart-card {
    background-color: #ffffff;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    padding: 1.5rem;
}

.chart-card h5 {
    font-weight: 700 !important;
    color: #343a40 !important;
}
</style>
{{-- END: Dedicated CSS --}}

<section>
    <div class="row align-items-center">
        <div class="col-md-5">
            <h2 class="card-title">Player Registration Overview</h2>
        </div>
        {{-- START: Date Range Filter Form --}}
        <div class="col-md-7">
            <form action="{{ route('admin.dashboard') }}" method="GET" class="form-inline float-md-right">
                <div class="form-group mb-2">
                    <label for="start_date" class="sr-only">Start Date</label>
                    <input type="text" class="form-control" id="start_date" name="start_date"
                        value="{{ $filterStartDate }}">
                </div>
                <div class="form-group mx-sm-3 mb-2">
                    <label for="end_date" class="sr-only">End Date</label>
                    <input type="text" class="form-control" id="end_date" name="end_date" value="{{ $filterEndDate }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-filter mr-1"></i> Filter</button>
            </form>
        </div>
    </div>
    <hr class="mb-4">

    {{-- Stat Cards Section --}}
    <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="dashboard-stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                <div class="stat-content">
                    <div class="stat-title">Registrations Today</div>
                    <div class="stat-number">{{ $todayCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="dashboard-stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
                <div class="stat-content">
                    <div class="stat-title">This Week</div>
                    <div class="stat-number">{{ $thisWeekCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="dashboard-stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-content">
                    <div class="stat-title">This Month</div>
                    <div class="stat-number">{{ $thisMonthCount }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart for Daily Registrations --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="chart-card">
                <h5 class="mb-3">Daily Registrations</h5>
                <div style="height: 350px;">
                    <canvas id="dailyRegistrationsChart" data-labels="{{ $registrationLabels->toJson() }}"
                        data-data="{{ $registrationData->toJson() }}"></canvas>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize the Date Range Picker
    const picker = new Litepicker({
        element: document.getElementById('start_date'),
        elementEnd: document.getElementById('end_date'),
        singleMode: false,
        allowRepick: true,
        format: 'YYYY-MM-DD',
        buttonText: {
            previousMonth: `<i class="fas fa-chevron-left"></i>`,
            nextMonth: `<i class="fas fa-chevron-right"></i>`,
            apply: 'Apply',
            cancel: 'Cancel',
        },
    });

    // Chart.js implementation
    const dailyChartCanvas = document.getElementById('dailyRegistrationsChart');
    const registrationLabels = JSON.parse(dailyChartCanvas.dataset.labels);
    const registrationData = JSON.parse(dailyChartCanvas.dataset.data);

    new Chart(dailyChartCanvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: registrationLabels,
            datasets: [{
                label: 'New Users',
                data: registrationData,
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(0, 123, 255, 1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'day',
                        tooltipFormat: 'MMM d, yyyy'
                    },
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    // MODIFIED: This ensures the y-axis always starts at 0
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    suggestedMax: Math.max(...registrationData) > 0 ? Math.max(...registrationData) +
                        2 : 5
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end'
                }
            }
        }
    });
});
</script>
@endpush