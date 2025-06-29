<?php
session_start();
if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    header("Location: index.php");
    exit();
}

if ($_SESSION["role"] !== "admin") {
    header("Location: dashboard.php");
    exit();
}

// Function to get access logs
function getAccessLogs() {
    $logFile = __DIR__ . '/access_log.json';
    if (file_exists($logFile)) {
        return json_decode(file_get_contents($logFile), true);
    }
    return [];
}

// Function to get temporary PINs
function getTempPins() {
    $tempPinsFile = __DIR__ . '/temp_pins.json';
    if (file_exists($tempPinsFile)) {
        $tempPins = json_decode(file_get_contents($tempPinsFile), true);
        // Filter out expired PINs
        return array_filter($tempPins, function($tempPin) {
            return strtotime($tempPin['expires_at']) > time();
        });
    }
    return [];
}

$accessLogs = getAccessLogs();
$tempPins = getTempPins();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Logs - Smart Cabinet</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400" rel="stylesheet">
    <style>
        body {
            background: #10182a;
            color: #e0e6f0;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            background: linear-gradient(90deg, #2563ff 0%, #7fffa6 100%);
            padding: 28px 0 18px 0;
            text-align: center;
            box-shadow: 0 4px 16px #0003;
            position: relative;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
        }
        .header-title {
            color: #fff;
            font-size: 1.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
        }
        .header-title span {
            font-size: 2.2rem;
            margin-right: 12px;
        }
        .user-info {
            color: #fff;
            margin-right: 15px;
        }
        .logout-btn {
            background: #ff4d4d;
            color: #fff;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .back-btn {
            background: #2563ff;
            color: #fff;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            margin-right: 10px;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: #18223a;
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 #000a;
        }
        .page-title {
            font-size: 2rem;
            margin-bottom: 30px;
            letter-spacing: 2px;
            color: #ffb347;
            text-align: center;
        }
        .section {
            background: #131b2d;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .section h3 {
            color: #ffb347;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            background: #1a2335;
            border-radius: 8px;
            overflow: hidden;
        }
        .table th {
            background: #2563ff;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .table td {
            padding: 12px;
            border-bottom: 1px solid #2a3a5a;
        }
        .table tr:hover {
            background: #222a3a;
        }
        .status-granted {
            color: #7fffa6;
            font-weight: bold;
        }
        .status-denied {
            color: #ff4d4d;
            font-weight: bold;
        }
        .pin-active {
            color: #7fffa6;
            font-weight: bold;
        }
        .pin-expired {
            color: #888;
            text-decoration: line-through;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #131b2d;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #7fffa6;
        }
        .stat-label {
            color: #ffb347;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="header-title">
                <span>📊</span>
                Access Logs & PINs
            </div>
            <div>
                <a href="dashboard.php" class="back-btn">← Dashboard</a>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?> (<?php echo htmlspecialchars($_SESSION["role"]); ?>)</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h1 class="page-title">ACCESS LOGS & TEMPORARY PINS</h1>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($accessLogs); ?></div>
                <div class="stat-label">Total Access Attempts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($accessLogs, function($log) { return $log['status'] === 'Granted'; })); ?></div>
                <div class="stat-label">Successful Access</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($tempPins); ?></div>
                <div class="stat-label">Active Temporary PINs</div>
            </div>
        </div>

        <div class="section">
            <h3>Active Temporary PINs</h3>
            <?php if (empty($tempPins)): ?>
                <div class="no-data">
                    <h4>No active temporary PINs</h4>
                    <p>Generate PINs from the dashboard to see them here.</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>PIN</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Expires At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tempPins as $tempPin): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($tempPin['pin']); ?></strong></td>
                                <td><?php echo htmlspecialchars($tempPin['created_by']); ?></td>
                                <td><?php echo htmlspecialchars($tempPin['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($tempPin['expires_at']); ?></td>
                                <td class="pin-active">● Active</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="section">
            <h3>Recent Access Attempts</h3>
            <?php if (empty($accessLogs)): ?>
                <div class="no-data">
                    <h4>No access attempts recorded</h4>
                    <p>Access attempts from the hardware will appear here.</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>PIN Used</th>
                            <th>Status</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($accessLogs) as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                <td><?php echo htmlspecialchars($log['pin']); ?></td>
                                <td class="status-<?php echo strtolower($log['status']); ?>">
                                    <?php echo htmlspecialchars($log['status']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($log['ip']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 