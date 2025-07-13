<nav class="navbar">
    <div class="nav-container">
        <div class="nav-brand">
            <a href="dashboard.php">🚗 Fleet Manager</a>
        </div>
        
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
            <li><a href="vehicles.php" class="nav-link">Vehicles</a></li>
            <li><a href="fuel-logs.php" class="nav-link">Fuel Logs</a></li>
            <li><a href="employees.php" class="nav-link">Employees</a></li>
            <li><a href="departments.php" class="nav-link">Departments</a></li>
            <li><a href="reports.php" class="nav-link">Reports</a></li>
            <li><a href="logout.php" class="nav-link logout">Logout</a></li>
        </ul>
        
        <div class="nav-user">
            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
        </div>
    </div>
</nav>