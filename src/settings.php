<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="/assets/stock.png">
    <title>Settings - TradeAnalytics</title>
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
            <a class="nav-link" href="stats.php"><i class="fas fa-chart-pie me-2"></i>Statistics</a>
            <a class="nav-link active" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="container mt-4">
            <h2 class="mb-4">Account Settings</h2>
            <div id="errorAlert" class="alert alert-danger d-none"></div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-user-cog me-2"></i>Profile Settings
                        </div>
                        <div class="card-body">
                            <form id="settingsForm">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" value="trader123" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" value="user@example.com">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" placeholder="Enter new password">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-chart-line me-2"></i>Trading Preferences
                        </div>
                        <div class="card-body">
                            <form id="preferencesForm">
                                <div class="mb-3">
                                    <label class="form-label">Default Timeframe</label>
                                    <select class="form-select">
                                        <option>1H</option>
                                        <option>4H</option>
                                        <option>1D</option>
                                        <option>1W</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Risk Tolerance</label>
                                    <select class="form-select">
                                        <option>Low</option>
                                        <option>Medium</option>
                                        <option>High</option>
                                    </select>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-sync me-2"></i>Update Preferences
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('settingsForm').addEventListener('submit', (e) => {
            e.preventDefault();
            document.getElementById('errorAlert').classList.add('d-none');
            // Add actual save functionality here
            alert('Profile settings saved successfully!');
        });

        document.getElementById('preferencesForm').addEventListener('submit', (e) => {
            e.preventDefault();
            document.getElementById('errorAlert').classList.add('d-none');
            // Add actual save functionality here
            alert('Trading preferences updated successfully!');
        });
    </script>
</body>
</html>