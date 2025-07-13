<?php
require_once 'config.php';
requireAuth();

// Get dashboard statistics
try {
    // Total vehicles
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM vehicles WHERE status = 'active'");
    $totalVehicles = $stmt->fetch()['total'];

    // Monthly fuel cost (current month)
    $currentMonth = date('Y-m');
    $stmt = $pdo->prepare("SELECT SUM(cost) as total FROM fuel_logs WHERE DATE_FORMAT(date, '%Y-%m') = ?");
    $stmt->execute([$currentMonth]);
    $monthlyFuelCost = $stmt->fetch()['total'] ?? 0;

    // Total fuel used this month
    $stmt = $pdo->prepare("SELECT SUM(fuel_quantity) as total FROM fuel_logs WHERE DATE_FORMAT(date, '%Y-%m') = ?");
    $stmt->execute([$currentMonth]);
    $totalFuelUsed = $stmt->fetch()['total'] ?? 0;

    // Recent fuel logs
    $stmt = $pdo->query("
        SELECT fl.*, v.registration_number, v.make, v.model, vc.name as category_name 
        FROM fuel_logs fl 
        JOIN vehicles v ON fl.vehicle_id = v.id 
        JOIN vehicle_categories vc ON v.category_id = vc.id 
        ORDER BY fl.date DESC 
        LIMIT 5
    ");
    $recentLogs = $stmt->fetchAll();

    // Vehicle overview by category
    $stmt = $pdo->query("
        SELECT vc.name, COUNT(v.id) as count 
        FROM vehicle_categories vc 
        LEFT JOIN vehicles v ON vc.id = v.category_id AND v.status = 'active'
        GROUP BY vc.id, vc.name
    ");
    $vehiclesByCategory = $stmt->fetchAll();

} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Calculate average efficiency (simplified)
$avgEfficiency = $totalFuelUsed > 0 ? round(1000 / $totalFuelUsed, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Fleet Fuel Management</title>
    <meta name="description" content="Fleet management dashboard showing fuel consumption statistics and vehicle overview">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Overview of your fleet fuel management</p>
        </div>

        <!-- Metric Cards -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon">🚗</div>
                <div class="metric-content">
                    <h3>Total Vehicles</h3>
                    <div class="metric-value"><?php echo $totalVehicles; ?></div>
                    <div class="metric-label">Active vehicles</div>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">💰</div>
                <div class="metric-content">
                    <h3>Monthly Fuel Cost</h3>
                    <div class="metric-value"><?php echo formatCurrency($monthlyFuelCost); ?></div>
                    <div class="metric-label">This month</div>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">⛽</div>
                <div class="metric-content">
                    <h3>Fuel Used</h3>
                    <div class="metric-value"><?php echo number_format($totalFuelUsed, 1); ?>L</div>
                    <div class="metric-label">This month</div>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">📊</div>
                <div class="metric-content">
                    <h3>Avg Efficiency</h3>
                    <div class="metric-value"><?php echo $avgEfficiency; ?> km/L</div>
                    <div class="metric-label">Fleet average</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="section">
            <h2>Quick Actions</h2>
            <div class="quick-actions">
                <a href="add-fuel-log.php" class="action-btn primary">
                    <span class="btn-icon">⛽</span>
                    Add Fuel Log
                </a>
                <a href="add-vehicle.php" class="action-btn">
                    <span class="btn-icon">🚗</span>
                    Add Vehicle
                </a>
                <a href="reports.php" class="action-btn">
                    <span class="btn-icon">📊</span>
                    Generate Report
                </a>
                <a href="export.php" class="action-btn">
                    <span class="btn-icon">📁</span>
                    Export Data
                </a>
            </div>
        </div>

        <!-- Recent Fuel Logs -->
        <div class="section">
            <div class="section-header">
                <h2>Recent Fuel Logs</h2>
                <a href="fuel-logs.php" class="view-all-btn">View All</a>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vehicle</th>
                            <th>Mileage</th>
                            <th>Fuel (L)</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentLogs)): ?>
                            <tr>
                                <td colspan="5" class="no-data">No fuel logs found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentLogs as $log): ?>
                                <tr>
                                    <td><?php echo formatDate($log['date']); ?></td>
                                    <td>
                                        <div class="vehicle-info">
                                            <span class="registration"><?php echo htmlspecialchars($log['registration_number']); ?></span>
                                            <span class="vehicle-details"><?php echo htmlspecialchars($log['make'] . ' ' . $log['model']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($log['mileage']); ?> km</td>
                                    <td><?php echo number_format($log['fuel_quantity'], 1); ?>L</td>
                                    <td><?php echo formatCurrency($log['cost']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vehicle Overview -->
        <div class="section">
            <h2>Fleet Overview</h2>
            <div class="vehicle-overview">
                <?php foreach ($vehiclesByCategory as $category): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <?php 
                            echo $category['name'] === 'Car' ? '🚗' : 
                                ($category['name'] === 'Motorcycle' ? '🏍️' : '🚛'); 
                            ?>
                        </div>
                        <div class="category-content">
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            <div class="category-count"><?php echo $category['count']; ?></div>
                            <div class="category-label">vehicles</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>