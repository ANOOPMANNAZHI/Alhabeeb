@extends('layouts.plms-app')

@section('css')
<style>
    .dashboard-header {
        background: linear-gradient(135deg, #1e3a5f, #0d1f3c);
        color: #fff;
        padding: 20px 25px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    .dashboard-header h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 600;
    }
    .dashboard-header .subtitle {
        font-size: 13px;
        opacity: 0.85;
        margin-top: 4px;
    }
    .filter-group {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .filter-group select, .filter-group input {
        padding: 6px 10px;
        border: 1px solid rgba(255,255,255,0.4);
        border-radius: 4px;
        background: rgba(255,255,255,0.15);
        color: #fff;
        font-size: 13px;
        min-width: 110px;
    }
    .filter-group select option {
        color: #333;
        background: #fff;
    }
    .filter-group input[type="date"] {
        color-scheme: dark;
    }
    .custom-date-fields {
        display: none;
    }
    .kpi-row {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .kpi-card {
        flex: 1;
        min-width: 180px;
        background: #fff;
        border-radius: 8px;
        padding: 18px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        position: relative;
        overflow: hidden;
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }
    .kpi-card:nth-child(1)::before { background: #2563eb; }
    .kpi-card:nth-child(2)::before { background: #059669; }
    .kpi-card:nth-child(3)::before { background: #d97706; }
    .kpi-card:nth-child(4)::before { background: #7c3aed; }
    .kpi-card:nth-child(5)::before { background: #dc2626; }
    .kpi-card .kpi-label {
        font-size: 12px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .kpi-card .kpi-value {
        font-size: 26px;
        font-weight: 700;
        color: #333;
        line-height: 1.2;
    }
    .kpi-card .kpi-sub {
        font-size: 12px;
        color: #999;
        margin-top: 4px;
    }
    .chart-card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
    .chart-card h4 {
        margin: 0 0 15px 0;
        font-size: 15px;
        font-weight: 600;
        color: #333;
    }
    .chart-container {
        position: relative;
        width: 100%;
    }
    .loading-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        font-size: 18px;
        color: #1e3a5f;
    }
    .loading-overlay.hidden { display: none; }

    /* Employee Summary Table */
    .summary-card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
    .summary-card h4 {
        margin: 0 0 15px 0;
        font-size: 15px;
        font-weight: 600;
        color: #333;
    }
    .summary-table {
        width: 100%;
        border-collapse: collapse;
    }
    .summary-table thead th {
        background: #1e3a5f;
        color: #fff;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 600;
        text-align: left;
    }
    .summary-table thead th:not(:first-child) {
        text-align: center;
    }
    .summary-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
        color: #333;
    }
    .summary-table tbody td:not(:first-child) {
        text-align: center;
    }
    .summary-table tbody tr:hover {
        background: #f8f9fa;
    }
    .summary-table tfoot td,
    .summary-table tfoot td strong {
        background: #1e2a3a;
        color: #fff !important;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 700;
    }
    .summary-table tfoot td:not(:first-child) {
        text-align: center;
    }
    .emp-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: #fff;
        font-weight: 700;
        font-size: 13px;
        margin-right: 10px;
        vertical-align: middle;
    }
    .emp-name-cell {
        display: flex;
        align-items: center;
    }
</style>
@endsection

@section('content')
<div class="loading-overlay" id="loadingOverlay">
    <i class="fa fa-spinner fa-spin fa-2x"></i>&nbsp; Loading dashboard...
</div>

<div class="container-fluid" style="padding: 15px;">
    <!-- Header -->
    <div class="dashboard-header">
        <div>
            <h3>Leasing Consultant Performance</h3>
            <div class="subtitle" id="dateRangeLabel">Loading...</div>
        </div>
        <div class="filter-group">
            <select id="filterPeriod">
                <option value="YTD" selected>Year to Date</option>
                <option value="Q1">Q1 (Jan-Mar)</option>
                <option value="Q2">Q2 (Apr-Jun)</option>
                <option value="Q3">Q3 (Jul-Sep)</option>
                <option value="Q4">Q4 (Oct-Dec)</option>
                <option value="Custom">Custom Range</option>
            </select>
            <select id="filterYear">
                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <div class="custom-date-fields" id="customDates">
                <input type="date" id="filterStartDate" />
                <input type="date" id="filterEndDate" />
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Total Units</div>
            <div class="kpi-value" id="kpiTotalUnits">-</div>
            <div class="kpi-sub">Flats rented</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Rent</div>
            <div class="kpi-value" id="kpiTotalRent">-</div>
            <div class="kpi-sub">OMR</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Avg Rent</div>
            <div class="kpi-value" id="kpiAvgRent">-</div>
            <div class="kpi-sub">OMR per unit</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Top by Units</div>
            <div class="kpi-value" id="kpiTopUnitsName">-</div>
            <div class="kpi-sub" id="kpiTopUnitsCount"></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Top by Amount</div>
            <div class="kpi-value" id="kpiTopAmountName">-</div>
            <div class="kpi-sub" id="kpiTopAmountValue"></div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row">
        <div class="col-md-6">
            <div class="chart-card">
                <h4>Leaderboard - Units Rented (Top 5)</h4>
                <div class="chart-container">
                    <canvas id="chartLeaderboard" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-card">
                <h4>Total Rent by Employee (OMR)</h4>
                <div class="chart-container">
                    <canvas id="chartRentByEmployee" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row">
        <div class="col-md-6">
            <div class="chart-card">
                <h4>Rent Distribution (OMR/month)</h4>
                <div class="chart-container">
                    <canvas id="chartRentDistribution" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-card">
                <h4>Top Buildings by Units</h4>
                <div class="chart-container">
                    <canvas id="chartTopBuildings" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Summary Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="summary-card">
                <h4>Employee Summary</h4>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Units</th>
                            <th>Total Rent</th>
                            <th>Avg Rent</th>
                        </tr>
                    </thead>
                    <tbody id="employeeSummaryBody">
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td id="summaryTotalUnits"></td>
                            <td id="summaryTotalRent"></td>
                            <td id="summaryAvgRent"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var chartLeaderboard = null;
    var chartRentByEmployee = null;
    var chartRentDistribution = null;
    var chartTopBuildings = null;

    var chartColors = ['#2563eb','#059669','#d97706','#7c3aed','#dc2626','#0891b2','#c026d3','#65a30d','#ea580c','#4f46e5'];

    // Period filter toggle custom dates
    $('#filterPeriod').on('change', function() {
        if ($(this).val() === 'Custom') {
            $('#customDates').show();
        } else {
            $('#customDates').hide();
            loadData();
        }
    });

    $('#filterYear').on('change', function() {
        if ($('#filterPeriod').val() !== 'Custom') {
            loadData();
        }
    });

    $('#filterStartDate, #filterEndDate').on('change', function() {
        if ($('#filterStartDate').val() && $('#filterEndDate').val()) {
            loadData();
        }
    });

    function loadData() {
        var params = {
            period: $('#filterPeriod').val(),
            year: $('#filterYear').val()
        };
        if (params.period === 'Custom') {
            params.start_date = $('#filterStartDate').val();
            params.end_date = $('#filterEndDate').val();
            if (!params.start_date || !params.end_date) return;
        }

        $('#loadingOverlay').removeClass('hidden');

        $.ajax({
            url: '{{ route("leasingConsultantPerformanceData") }}',
            data: params,
            dataType: 'json',
            success: function(data) {
                updateKPIs(data.kpi);
                updateCharts(data);
                updateEmployeeSummary(data);
                $('#dateRangeLabel').text(data.dateRange);
                $('#loadingOverlay').addClass('hidden');
            },
            error: function() {
                $('#loadingOverlay').addClass('hidden');
                alert('Error loading dashboard data.');
            }
        });
    }

    function updateKPIs(kpi) {
        $('#kpiTotalUnits').text(kpi.totalUnits);
        $('#kpiTotalRent').text(parseFloat(kpi.totalRent).toLocaleString('en', {minimumFractionDigits: 3, maximumFractionDigits: 3}));
        $('#kpiAvgRent').text(parseFloat(kpi.avgRent).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
        $('#kpiTopUnitsName').text(kpi.topByUnitsName || '-');
        $('#kpiTopUnitsCount').text(kpi.topByUnitsCount ? kpi.topByUnitsCount + ' units' : '');
        $('#kpiTopAmountName').text(kpi.topByAmountName || '-');
        $('#kpiTopAmountValue').text(kpi.topByAmountValue ? 'OMR ' + parseFloat(kpi.topByAmountValue).toLocaleString('en', {minimumFractionDigits: 3}) : '');
    }

    function updateCharts(data) {
        // Leaderboard - horizontal bar
        if (chartLeaderboard) chartLeaderboard.destroy();
        var ctx1 = document.getElementById('chartLeaderboard').getContext('2d');
        chartLeaderboard = new Chart(ctx1, {
            type: 'horizontalBar',
            data: {
                labels: data.leaderboard.map(function(e){ return e.name; }),
                datasets: [{
                    label: 'Units Rented',
                    data: data.leaderboard.map(function(e){ return e.count; }),
                    backgroundColor: chartColors.slice(0, data.leaderboard.length)
                }]
            },
            options: {
                responsive: true,
                legend: { display: false },
                scales: {
                    xAxes: [{ ticks: { beginAtZero: true, precision: 0 } }]
                }
            }
        });

        // Rent by Employee - bar
        if (chartRentByEmployee) chartRentByEmployee.destroy();
        var ctx2 = document.getElementById('chartRentByEmployee').getContext('2d');
        chartRentByEmployee = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: data.rentByEmployee.map(function(e){ return e.name; }),
                datasets: [{
                    label: 'Total Rent (OMR)',
                    data: data.rentByEmployee.map(function(e){ return e.total; }),
                    backgroundColor: chartColors.slice(0, data.rentByEmployee.length)
                }]
            },
            options: {
                responsive: true,
                legend: { display: false },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true } }]
                }
            }
        });

        // Rent Distribution - bar
        if (chartRentDistribution) chartRentDistribution.destroy();
        var ctx3 = document.getElementById('chartRentDistribution').getContext('2d');
        chartRentDistribution = new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: data.rentDistribution.map(function(e){ return e.bucket; }),
                datasets: [{
                    label: 'Number of Contracts',
                    data: data.rentDistribution.map(function(e){ return e.count; }),
                    backgroundColor: ['#2563eb','#059669','#d97706','#7c3aed','#dc2626']
                }]
            },
            options: {
                responsive: true,
                legend: { display: false },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }]
                }
            }
        });

        // Top Buildings - doughnut
        if (chartTopBuildings) chartTopBuildings.destroy();
        var ctx4 = document.getElementById('chartTopBuildings').getContext('2d');
        chartTopBuildings = new Chart(ctx4, {
            type: 'doughnut',
            data: {
                labels: data.topBuildings.map(function(e){ return e.name; }),
                datasets: [{
                    data: data.topBuildings.map(function(e){ return e.count; }),
                    backgroundColor: chartColors.slice(0, data.topBuildings.length)
                }]
            },
            options: {
                responsive: true,
                legend: { position: 'right' }
            }
        });
    }

    var avatarColors = ['#2563eb','#059669','#d97706','#7c3aed','#dc2626','#0891b2','#c026d3','#65a30d'];

    function updateEmployeeSummary(data) {
        var tbody = $('#employeeSummaryBody');
        tbody.empty();

        var summary = data.employeeSummary || [];
        var totalUnits = 0, totalRent = 0;

        summary.forEach(function(emp, idx) {
            var initial = emp.name ? emp.name.charAt(0).toUpperCase() : '?';
            var color = avatarColors[idx % avatarColors.length];
            totalUnits += emp.count;
            totalRent += parseFloat(emp.total);

            tbody.append(
                '<tr>' +
                    '<td><div class="emp-name-cell"><span class="emp-avatar" style="background:' + color + '">' + initial + '</span>' + emp.name + '</div></td>' +
                    '<td>' + emp.count + '</td>' +
                    '<td>' + parseFloat(emp.total).toLocaleString('en', {minimumFractionDigits: 3, maximumFractionDigits: 3}) + '</td>' +
                    '<td>' + parseFloat(emp.avg).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '</td>' +
                '</tr>'
            );
        });

        var totalAvg = totalUnits > 0 ? (totalRent / totalUnits) : 0;
        $('#summaryTotalUnits').text(totalUnits);
        $('#summaryTotalRent').text(totalRent.toLocaleString('en', {minimumFractionDigits: 3, maximumFractionDigits: 3}));
        $('#summaryAvgRent').text(Math.round(totalAvg).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
    }

    // Initial load
    loadData();
});
</script>
@endsection
