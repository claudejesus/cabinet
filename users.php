<?php
session_start();
if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    header("Location: index.php");
    exit();
}

function get_users() {
    $file = __DIR__ . '/users.json';
    if (file_exists($file)) {
        $json = file_get_contents($file);
        return json_decode($json, true);
    }
    return [
        ["username" => "admin", "password" => "admin123", "role" => "admin"]
    ];
}

$users = get_users();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Users - Smart Cabinet</title>
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
            max-width: 1000px;
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
        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: #131b2d;
            border-radius: 12px;
            overflow: hidden;
        }
        .users-table th {
            background: #2563ff;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #2a3a5a;
        }
        .users-table tr:hover {
            background: #1a2335;
        }
        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .role-admin {
            background: #ff4d4d;
            color: white;
        }
        .role-user {
            background: #7fffa6;
            color: #1a2335;
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
        .no-users {
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
                <span>👥</span>
                Registered Users
            </div>
            <div>
                <a href="dashboard.php" class="back-btn">← Dashboard</a>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?> (<?php echo htmlspecialchars($_SESSION["role"]); ?>)</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h1 class="page-title">REGISTERED USERS</h1>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($users); ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($users, function($user) { return $user['role'] === 'admin'; })); ?></div>
                <div class="stat-label">Administrators</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($users, function($user) { return $user['role'] === 'user'; })); ?></div>
                <div class="stat-label">Regular Users</div>
            </div>
        </div>

        <?php if (empty($users)): ?>
            <div class="no-users">
                <h3>No users found</h3>
                <p>There are currently no registered users in the system.</p>
            </div>
        <?php else: ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user["username"]); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $user["role"]; ?>">
                                    <?php echo htmlspecialchars($user["role"]); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user["username"] === $_SESSION["username"]): ?>
                                    <span style="color: #7fffa6;">● Current User</span>
                                <?php else: ?>
                                    <span style="color: #888;">● Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html> 