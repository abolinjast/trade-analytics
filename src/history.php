<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="/assets/stock.png">
    <title>Trade History - TradeAnalytics</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Same styles as dashboard.php */
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
            <a class="nav-link active" href="history.php"><i class="fas fa-history me-2"></i>Trade History</a>
            <a class="nav-link" href="stats.php"><i class="fas fa-chart-pie me-2"></i>Statistics</a>
            <a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="container mt-4">
            <h2 class="mb-4">Trade History</h2>
            <div id="errorAlert" class="alert alert-danger d-none"></div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-table me-2"></i>All Trades
                    <div class="float-end">
                        <select class="form-select form-select-sm" id="sortBy">
                            <option value="date_desc">Newest First</option>
                            <option value="date_asc">Oldest First</option>
                            <option value="profit_desc">Profit High-Low</option>
                            <option value="profit_asc">Profit Low-High</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tradesTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Symbol</th>
                                    <th>Profit/Loss</th>
                                    <th>Timeframe</th>
                                    <th>Position</th>
                                </tr>
                            </thead>
                            <tbody id="tradesBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loadData = async () => {
                try {
                    const response = await fetch('api.php');
                    if (!response.ok) throw new Error('Failed to fetch trades');
                    const { data: trades } = await response.json();
                    
                    document.getElementById('sortBy').addEventListener('change', (e) => {
                        const sorted = sortTrades([...trades], e.target.value);
                        populateTable(sorted);
                    });

                    populateTable(sortTrades(trades, 'date_desc'));
                } catch (error) {
                    showError(error.message);
                }
            };

            const sortTrades = (trades, sortType) => {
                return trades.sort((a, b) => {
                    switch(sortType) {
                        case 'date_asc': return new Date(a.trade_date) - new Date(b.trade_date);
                        case 'date_desc': return new Date(b.trade_date) - new Date(a.trade_date);
                        case 'profit_asc': return a.profit_loss - b.profit_loss;
                        case 'profit_desc': return b.profit_loss - a.profit_loss;
                        default: return 0;
                    }
                });
            };

            const populateTable = (trades) => {
                const tbody = document.getElementById('tradesBody');
                tbody.innerHTML = trades.map(trade => `
                    <tr>
                        <td>${new Date(trade.trade_date).toLocaleDateString()}</td>
                        <td>${trade.symbol}</td>
                        <td class="${trade.profit_loss >= 0 ? 'text-success' : 'text-danger'}">
                            $${parseFloat(trade.profit_loss).toFixed(2)}
                        </td>
                        <td>${trade.timeframe}</td>
                        <td><span class="badge ${trade.position_type === 'Long' ? 'bg-success' : 'bg-danger'}">
                            ${trade.position_type}
                        </span></td>
                    </tr>
                `).join('');
            };

            const showError = (message) => {
                const alert = document.getElementById('errorAlert');
                alert.textContent = message;
                alert.classList.remove('d-none');
            };

            loadData();
        });
    </script>
</body>
</html>