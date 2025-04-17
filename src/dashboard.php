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
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
        <?php include 'styles.css'; ?>
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-chart-line"></i>
            <span>TradeAnalytics</span>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
            <a class="nav-link" href="history.php"><i class="fas fa-history me-2"></i>Trade History</a>
            <a class="nav-link" href="stats.php"><i class="fas fa-chart-pie me-2"></i>Statistics</a>
            <a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a>
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
                        <div class="card-body chart-container">
                            <canvas id="cumulativePnlChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-success text-white">Win/Loss Distribution</div>
                        <div class="card-body chart-container">
                            <canvas id="winLossChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">Daily PnL Breakdown</div>
                        <div class="card-body chart-container">
                            <canvas id="dailyPnlChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cumulativePnlChart = null;
        let winLossChart = null;
        let dailyPnlChart = null;
        let initialLoad = true;

        const elements = {
            timePeriod: document.getElementById('timePeriod'),
            symbolFilter: document.getElementById('symbolFilter'),
            timeframeFilter: document.getElementById('timeframeFilter'),
            positionTypeFilter: document.getElementById('positionTypeFilter'),
            errorAlert: document.getElementById('errorAlert')
        };

        function init() {
            if (!validateElements()) {
                showError('Critical UI components missing!');
                return;
            }

            document.querySelectorAll('select').forEach(s => {
                s.addEventListener('change', loadData);
            });

            // Initial load with default filters
            loadData();
        }

        function validateElements() {
            return Object.values(elements).every(element => element !== null);
        }

        async function loadData() {
            try {
                showLoading();
                const params = new URLSearchParams({
                    timePeriod: elements.timePeriod.value,
                    symbol: elements.symbolFilter.value,
                    timeframe: elements.timeframeFilter.value,
                    positionType: elements.positionTypeFilter.value
                });

                const response = await fetch(`api.php?${params}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.status !== 'success') {
                    throw new Error(result.message || 'API request failed');
                }

                if (initialLoad) {
                    populateFilters(result.data);
                    initialLoad = false;
                }

                updateCharts(result.data);

            } catch (error) {
                showError(error.message);
                updateCharts([]); // Reset charts on error
            } finally {
                hideLoading();
            }
        }

        function populateFilters(trades) {
            try {
                const symbols = ['all', ...new Set(trades.map(t => t.symbol).filter(Boolean))];
                const timeframes = ['all', ...new Set(trades.map(t => t.timeframe).filter(Boolean))];
                
                elements.symbolFilter.innerHTML = symbols.map(s => 
                    `<option value="${s}">${s}</option>`
                ).join('');
                
                elements.timeframeFilter.innerHTML = timeframes.map(t => 
                    `<option value="${t}">${t}</option>`
                ).join('');
            } catch (error) {
                showError(`Filter population error: ${error.message}`);
            }
        }

        function updateCharts(trades) {
            destroyCharts();
            
            if (!trades || trades.length === 0) {
                showError('No trading data found for selected filters');
                return;
            }

            try {
                cumulativePnlChart = createLineChart('cumulativePnlChart', 
                    processCumulativeData(trades));
                
                winLossChart = createDoughnutChart('winLossChart', 
                    processWinLossData(trades));
                
                dailyPnlChart = createBarChart('dailyPnlChart', 
                    processDailyData(trades));
                
                hideError();
            } catch (error) {
                showError(`Chart rendering error: ${error.message}`);
            }
        }

        function processCumulativeData(trades) {
            let cumulative = 0;
            return {
                labels: trades.map(t => t.trade_date),
                data: trades.map(t => cumulative += Number(t.profit_loss) || 0)
            };
        }

        function processWinLossData(trades) {
            return trades.reduce((acc, t) => {
                if (Number(t.profit_loss) >= 0) acc.wins++;
                else acc.losses++;
                return acc;
            }, { wins: 0, losses: 0 });
        }

        function processDailyData(trades) {
            const daily = {};
            trades.forEach(t => {
                const date = t.trade_date;
                if (!date) return;
                daily[date] = (daily[date] || 0) + (Number(t.profit_loss) || 0);
            });
            return {
                labels: Object.keys(daily).sort(),
                data: Object.keys(daily).sort().map(date => daily[date])
            };
        }

        function createLineChart(canvasId, chartData) {
            const canvas = resetCanvas(canvasId);
            return new Chart(canvas, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Cumulative PnL',
                        data: chartData.data,
                        borderColor: '#4e73df',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.1
                    }]
                },
                options: getChartOptions()
            });
        }

        function createDoughnutChart(canvasId, chartData) {
            const canvas = resetCanvas(canvasId);
            return new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: ['Wins', 'Losses'],
                    datasets: [{
                        data: [chartData.wins, chartData.losses],
                        backgroundColor: ['#1cc88a', '#e74a3b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    ...getChartOptions(),
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function createBarChart(canvasId, chartData) {
            const canvas = resetCanvas(canvasId);
            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Daily PnL',
                        data: chartData.data,
                        backgroundColor: '#36b9cc',
                        borderWidth: 1
                    }]
                },
                options: getChartOptions()
            });
        }

        function resetCanvas(canvasId) {
            const container = document.getElementById(canvasId).parentNode;
            const newCanvas = document.createElement('canvas');
            newCanvas.id = canvasId;
            container.innerHTML = '';
            container.appendChild(newCanvas);
            return newCanvas;
        }

        function getChartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 300
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            };
        }

        function destroyCharts() {
            [cumulativePnlChart, winLossChart, dailyPnlChart].forEach(chart => {
                if (chart) chart.destroy();
            });
            cumulativePnlChart = null;
            winLossChart = null;
            dailyPnlChart = null;
        }

        function showError(message) {
            elements.errorAlert.textContent = `Error: ${message}`;
            elements.errorAlert.classList.remove('d-none');
        }

        function hideError() {
            elements.errorAlert.classList.add('d-none');
        }

        function showLoading() {
            elements.errorAlert.textContent = 'Loading data...';
            elements.errorAlert.classList.remove('d-none');
            elements.errorAlert.classList.remove('alert-danger');
            elements.errorAlert.classList.add('alert-info');
        }

        function hideLoading() {
            elements.errorAlert.classList.remove('alert-info');
            elements.errorAlert.classList.add('alert-danger');
            elements.errorAlert.classList.add('d-none');
        }

        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>