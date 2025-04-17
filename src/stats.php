<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="/assets/stock.png">
    <title>Statistics - TradeAnalytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
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
            <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
            <a class="nav-link" href="history.php"><i class="fas fa-history me-2"></i>Trade History</a>
            <a class="nav-link active" href="stats.php"><i class="fas fa-chart-pie me-2"></i>Statistics</a>
            <a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="container mt-4">
            <h2 class="mb-4">Trading Statistics</h2>
            <div id="errorAlert" class="alert alert-danger d-none"></div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-coins me-2"></i>Profit Distribution
                        </div>
                        <div class="card-body">
                            <canvas id="profitDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <i class="fas fa-chart-bar me-2"></i>Monthly Performance
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                const response = await fetch('api.php');
                if (!response.ok) throw new Error('Failed to fetch data');
                const { data: trades } = await response.json();

                // Profit Distribution Chart
                const profitData = calculateProfitDistribution(trades);
                new Chart(document.getElementById('profitDistributionChart'), {
                    type: 'pie',
                    data: {
                        labels: Object.keys(profitData),
                        datasets: [{
                            data: Object.values(profitData),
                            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e']
                        }]
                    }
                });

                // Monthly Performance Chart
                const monthlyData = calculateMonthlyPerformance(trades);
                new Chart(document.getElementById('monthlyPerformanceChart'), {
                    type: 'bar',
                    data: {
                        labels: Object.keys(monthlyData),
                        datasets: [{
                            label: 'Monthly PnL',
                            data: Object.values(monthlyData),
                            backgroundColor: '#4e73df'
                        }]
                    }
                });

            } catch (error) {
                document.getElementById('errorAlert').textContent = error.message;
                document.getElementById('errorAlert').classList.remove('d-none');
            }
        });

        function calculateProfitDistribution(trades) {
            return trades.reduce((acc, trade) => {
                const range = Math.abs(trade.profit_loss) >= 500 ? '500+' :
                             Math.abs(trade.profit_loss) >= 200 ? '200-499' :
                             Math.abs(trade.profit_loss) >= 100 ? '100-199' : '0-99';
                acc[range] = (acc[range] || 0) + 1;
                return acc;
            }, {});
        }

        function calculateMonthlyPerformance(trades) {
            return trades.reduce((acc, trade) => {
                const month = new Date(trade.trade_date).toLocaleString('default', { month: 'short' });
                acc[month] = (acc[month] || 0) + parseFloat(trade.profit_loss);
                return acc;
            }, {});
        }
    </script>
</body>
</html>