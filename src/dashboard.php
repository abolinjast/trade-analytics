<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="/assets/stock.png">
    <title>Trading Journal Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            width: 250px;
            padding: 20px;
            z-index: 1000;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }

        .nav-link {
            color: #bdc3c7;
            padding: 10px 15px;
            border-radius: 5px;
            margin: 5px 0;
            transition: all 0.3s;
        }

        .nav-link:hover {
            background-color: #34495e;
            color: white;
            text-decoration: none;
        }

        .nav-link.active {
            background-color: #3498db;
            color: white;
        }

        .logo {
            font-size: 24px;
            margin-bottom: 30px;
            padding: 10px;
            border-bottom: 1px solid #34495e;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 10px;
        }

        .alert {
            margin: 20px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
        }

        canvas {
            max-height: 500px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-chart-line"></i>
            <span>TradeAnalytics</span>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="#"><i class="fas fa-home me-2"></i>Dashboard</a>
            <a class="nav-link" href="#"><i class="fas fa-history me-2"></i>Trade History</a>
            <a class="nav-link" href="#"><i class="fas fa-chart-pie me-2"></i>Statistics</a>
            <a class="nav-link" href="#"><i class="fas fa-cog me-2"></i>Settings</a>
        </nav>
    </div>

    <div class="main-content">
        <div id="errorAlert" class="alert alert-danger d-none"></div>
        
        <div class="container mt-4">
            <h2 class="mb-4">Performance Dashboard</h2>
            
            <!-- Filters -->
            <div class="row mb-4 bg-light p-3 rounded">
                <div class="col-md-3">
                    <label>Time Period:</label>
                    <select class="form-select" id="timePeriod">
                        <option value="all">All Time</option>
                        <option value="last_week">Last Week</option>
                        <option value="last_month">Last Month</option>
                        <option value="last_year">Last Year</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Symbol:</label>
                    <select class="form-select" id="symbolFilter">
                        <option value="all">All Symbols</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Timeframe:</label>
                    <select class="form-select" id="timeframeFilter">
                        <option value="all">All Timeframes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Position Type:</label>
                    <select class="form-select" id="positionTypeFilter">
                        <option value="all">All Positions</option>
                        <option value="Long">Long</option>
                        <option value="Short">Short</option>
                    </select>
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">Cumulative PnL History</div>
                        <div class="card-body">
                            <canvas id="cumulativePnlChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-success text-white">Win/Loss Distribution</div>
                        <div class="card-body">
                            <canvas id="winLossChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">Daily PnL Breakdown</div>
                        <div class="card-body">
                            <canvas id="dailyPnlChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cumulativePnlChart, winLossChart, dailyPnlChart;
        const elements = {
            timePeriod: null,
            symbolFilter: null,
            timeframeFilter: null,
            positionTypeFilter: null,
            errorAlert: null
        };

        function init() {
            elements.timePeriod = document.getElementById('timePeriod');
            elements.symbolFilter = document.getElementById('symbolFilter');
            elements.timeframeFilter = document.getElementById('timeframeFilter');
            elements.positionTypeFilter = document.getElementById('positionTypeFilter');
            elements.errorAlert = document.getElementById('errorAlert');

            if (!validateElements()) {
                showError('Critical UI components missing!');
                return;
            }

            document.querySelectorAll('select').forEach(s => {
                s.addEventListener('change', loadData);
            });

            loadData();
        }

        function validateElements() {
            return Object.values(elements).every(element => element !== null);
        }

        async function loadData() {
            try {
                const params = new URLSearchParams({
                    timePeriod: elements.timePeriod.value,
                    symbol: elements.symbolFilter.value,
                    timeframe: elements.timeframeFilter.value,
                    positionType: elements.positionTypeFilter.value
                });

                const response = await fetch(`api.php?${params}`);
                
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                
                const { status, data, message } = await response.json();
                if (status !== 'success') throw new Error(message);

                if (!data?.length) {
                    showError('No trades found matching criteria');
                    updateCharts([]);
                    return;
                }

                populateFilters(data);
                updateCharts(data);
                hideError();

            } catch (error) {
                showError(error.message);
                updateCharts([]);
            }
        }

        function populateFilters(trades) {
            populateSelect(elements.symbolFilter, 
                ['all', ...new Set(trades.map(t => t.symbol))]);
            populateSelect(elements.timeframeFilter, 
                ['all', ...new Set(trades.map(t => t.timeframe))]);
        }

        function populateSelect(select, options) {
            select.innerHTML = options.map(opt => 
                `<option value="${opt}">${opt}</option>`
            ).join('');
        }

        function updateCharts(trades) {
            destroyCharts();
            
            if (trades.length === 0) {
                showError('No data available for selected filters');
                return;
            }

            const cumulativeData = processCumulativeData(trades);
            const winLossData = processWinLossData(trades);
            const dailyData = processDailyData(trades);

            renderCumulativeChart(cumulativeData);
            renderWinLossChart(winLossData);
            renderDailyChart(dailyData);
        }

        function processCumulativeData(trades) {
            let cumulative = 0;
            return {
                dates: trades.map(t => t.trade_date),
                amounts: trades.map(t => cumulative += Number(t.profit_loss))
            };
        }

        function processWinLossData(trades) {
            return trades.reduce((acc, t) => {
                Number(t.profit_loss) >= 0 ? acc.wins++ : acc.losses++;
                return acc;
            }, { wins: 0, losses: 0 });
        }

        function processDailyData(trades) {
            const daily = {};
            trades.forEach(t => {
                daily[t.trade_date] = (daily[t.trade_date] || 0) + Number(t.profit_loss);
            });
            return {
                dates: Object.keys(daily),
                amounts: Object.values(daily)
            };
        }

        function renderCumulativeChart(data) {
            cumulativePnlChart = new Chart(document.getElementById('cumulativePnlChart'), {
                type: 'line',
                data: {
                    labels: data.dates,
                    datasets: [{
                        label: 'Cumulative PnL',
                        data: data.amounts,
                        borderColor: '#4e73df',
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function renderWinLossChart(data) {
            winLossChart = new Chart(document.getElementById('winLossChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Wins', 'Losses'],
                    datasets: [{
                        data: [data.wins, data.losses],
                        backgroundColor: ['#1cc88a', '#e74a3b']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function renderDailyChart(data) {
            dailyPnlChart = new Chart(document.getElementById('dailyPnlChart'), {
                type: 'bar',
                data: {
                    labels: data.dates,
                    datasets: [{
                        label: 'Daily PnL',
                        data: data.amounts,
                        backgroundColor: '#36b9cc'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function destroyCharts() {
            [cumulativePnlChart, winLossChart, dailyPnlChart].forEach(chart => {
                chart?.destroy();
            });
        }

        function showError(message) {
            elements.errorAlert.textContent = `Error: ${message}`;
            elements.errorAlert.classList.remove('d-none');
        }

        function hideError() {
            elements.errorAlert.classList.add('d-none');
        }

        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>