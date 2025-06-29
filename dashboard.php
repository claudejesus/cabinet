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
function save_users($users) {
    $file = __DIR__ . '/users.json';
    file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
}

// Function to generate temporary PIN
function generateTempPin() {
    return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
}

// Function to save temporary PIN
function saveTempPin($pin, $expires_in_minutes = 10) {
    $tempPinsFile = __DIR__ . '/temp_pins.json';
    $tempPins = [];
    
    if (file_exists($tempPinsFile)) {
        $tempPins = json_decode(file_get_contents($tempPinsFile), true);
    }
    
    // Clean expired PINs
    $tempPins = array_filter($tempPins, function($tempPin) {
        return strtotime($tempPin['expires_at']) > time();
    });
    
    // Add new PIN
    $tempPins[] = [
        'pin' => $pin,
        'created_at' => date('Y-m-d H:i:s'),
        'expires_at' => date('Y-m-d H:i:s', time() + ($expires_in_minutes * 60)),
        'created_by' => $_SESSION['username']
    ];
    
    file_put_contents($tempPinsFile, json_encode($tempPins, JSON_PRETTY_PRINT));
}

$users = get_users();
if ($_SESSION["role"] === "admin" && $_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["new_username"])) {
        // Create new user
        $new_username = trim($_POST["new_username"]);
        $new_password = trim($_POST["new_password"]);
        if ($new_username && $new_password) {
            $exists = false;
            foreach ($users as $u) {
                if ($u["username"] === $new_username) $exists = true;
            }
            if (!$exists) {
                $users[] = ["username" => $new_username, "password" => $new_password, "role" => "user"];
                save_users($users);
                $msg = "User created!";
            } else {
                $msg = "Username already exists.";
            }
        } else {
            $msg = "Please fill all fields.";
        }
    } elseif (isset($_POST["share_pin"])) {
        // Generate and share PIN
        $tempPin = generateTempPin();
        $expires_in = isset($_POST["pin_duration"]) ? (int)$_POST["pin_duration"] : 10;
        saveTempPin($tempPin, $expires_in);
        $pinMsg = "Temporary PIN generated: <strong>$tempPin</strong> (expires in $expires_in minutes)";
    }
}
// Example PHP variables (replace with real logic/database)
$cabinetStatus = "UNLOCKED";
$exampleUsers = [
    ["name" => "User A", "role" => "Admin"],
    ["name" => "User B", "role" => "PIN"]
];
$pendingUser = "User C";
$pinLength = 4;
$securityOn = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Cabinet Dashboard</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400" rel="stylesheet">
    <style>
        body {
            background: #10182a;
            color: #e0e6f0;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: #18223a;
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 #000a;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 24px;
            letter-spacing: 2px;
        }
        .main-status, .side-panel, .user-mgmt, .approval, .settings {
            background: #131b2d;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 18px;
        }
        .main-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .main-status .status {
            font-size: 2.2rem;
            color: #7fffa6;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        .main-status .status i {
            font-size: 2.5rem;
            margin-right: 12px;
        }
        .main-status .actions button {
            font-size: 1rem;
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            margin-right: 10px;
            cursor: pointer;
            font-weight: bold;
        }
        .main-status .actions .unlock {
            background: #2563ff;
            color: #fff;
        }
        .main-status .actions .share {
            background: linear-gradient(90deg, #ffb347, #ffcc33);
            color: #222;
        }
        .side-panel {
            float: right;
            width: 220px;
            margin-left: 18px;
        }
        .user-mgmt, .approval, .settings {
            margin-right: 240px;
        }
        .user-mgmt h3, .approval h3, .settings h3 {
            color: #ffb347;
            margin-top: 0;
        }
        .user-mgmt ul {
            list-style: none;
            padding: 0;
        }
        .user-mgmt li {
            margin-bottom: 8px;
        }
        .user-mgmt .add-user {
            background: #18223a;
            color: #fff;
            border: 1px solid #2563ff;
            border-radius: 6px;
            padding: 6px 14px;
            cursor: pointer;
        }
        .approval button {
            margin-right: 10px;
            padding: 8px 18px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        .approval .approve {
            background: #2563ff;
            color: #fff;
        }
        .approval .deny {
            background: #ff4d4d;
            color: #fff;
        }
        .settings label {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .settings input[type="checkbox"] {
            margin-right: 8px;
        }
        .activity-log {
            margin-top: 20px;
        }
        .activity-log h4 {
            color: #ffb347;
            margin-bottom: 6px;
        }
        .activity-log p {
            margin: 0 0 6px 0;
            font-size: 0.95rem;
        }
        @media (max-width: 900px) {
            .side-panel, .user-mgmt, .approval, .settings {
                float: none;
                width: 100%;
                margin: 0 0 18px 0;
            }
        }
    </style>
</head>
<body>
    <div style="width:100%;background:linear-gradient(90deg,#2563ff 0%,#7fffa6 100%);padding:28px 0 18px 0;text-align:center;box-shadow:0 4px 16px #0003;">
        <span style="font-size:2.2rem;vertical-align:middle;">🚀</span>
        <span style="color:#fff;font-size:1.7rem;font-weight:700;letter-spacing:1px;margin-left:12px;vertical-align:middle;">Welcome to Your Smart Cabinet Dashboard!</span>
        <div style="position:absolute;top:20px;right:20px;">
            <span style="color:#fff;margin-right:15px;">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?> (<?php echo htmlspecialchars($_SESSION["role"]); ?>)</span>
            <a href="users.php" style="background:#7fffa6;color:#1a2335;padding:8px 16px;text-decoration:none;border-radius:5px;font-size:14px;margin-right:10px;">👥 View Users</a>
            <?php if ($_SESSION["role"] === "admin"): ?>
            <a href="access_logs.php" style="background:#ffb347;color:#1a2335;padding:8px 16px;text-decoration:none;border-radius:5px;font-size:14px;margin-right:10px;">📊 Access Logs</a>
            <?php endif; ?>
            <a href="logout.php" style="background:#ff4d4d;color:#fff;padding:8px 16px;text-decoration:none;border-radius:5px;font-size:14px;">Logout</a>
        </div>
    </div>
    <div class="dashboard-container">
        <h1>DASHBOARD</h1>
        <div class="main-status">
            <div>
                <div style="color:#ffb347; font-size:1.1rem;">SMART CABINET #001</div>
                <div class="status">
                    <i>✔️</i> <?php echo $cabinetStatus; ?>
                </div>
            </div>
            <div class="actions">
                <button class="unlock">UNLOCK NOW</button>
                <button class="share">SHARE PIN</button>
            </div>
        </div>
        <div class="side-panel">
            <div>
                <span style="font-size:1.3rem;">&#128246;</span>
                <span style="color:#ffb347; font-weight:bold;">Activity Log</span>
                <div class="activity-log">
                    <h4>Multi-Person Approval</h4>
                    <p>PIN Length: <?php echo $pinLength; ?> digits</p>
                </div>
            </div>
            <div class="settings">
                <h3>Settings</h3>
                <label>
                    <input type="checkbox" <?php if($securityOn) echo "checked"; ?> disabled>
                    Security
                </label>
                <label>PIN Length: <?php echo $pinLength; ?></label>
                <label>Calibrate lock</label>
                <label>Firmware Update</label>
            </div>
        </div>
        <?php if ($_SESSION["role"] === "admin"): ?>
        <div class="user-mgmt">
            <h3>Admin User Management</h3>
            <?php if (isset($msg)) echo '<div style="color:#7fffa6;">'.$msg.'</div>'; ?>
            <form method="POST" style="margin-bottom:18px;">
                <input type="text" name="new_username" placeholder="New Username" required style="margin-right:8px;">
                <input type="password" name="new_password" placeholder="New Password" required style="margin-right:8px;">
                <button type="submit">Create User</button>
            </form>
            <h4>Authorized Users</h4>
            <ul>
                <?php foreach($users as $user): if($user["role"]!=="admin"): ?>
                    <li><?php echo htmlspecialchars($user["username"]); ?></li>
                <?php endif; endforeach; ?>
            </ul>
        </div>
        
        <div class="user-mgmt">
            <h3>PIN Sharing</h3>
            <?php if (isset($pinMsg)) echo '<div style="color:#7fffa6;margin-bottom:15px;">'.$pinMsg.'</div>'; ?>
            <form method="POST" style="margin-bottom:18px;">
                <select name="pin_duration" style="margin-right:8px;padding:8px;border-radius:5px;background:#131b2d;color:#e0e6f0;border:1px solid #2a3a5a;">
                    <option value="5">5 minutes</option>
                    <option value="10" selected>10 minutes</option>
                    <option value="15">15 minutes</option>
                    <option value="30">30 minutes</option>
                    <option value="60">1 hour</option>
                </select>
                <button type="submit" name="share_pin" style="background:#ffb347;color:#1a2335;border:none;padding:8px 16px;border-radius:5px;cursor:pointer;font-weight:bold;">Generate Temporary PIN</button>
            </form>
            <div style="margin-bottom:15px;">
                <a href="test_api.php" target="_blank" style="background:#2563ff;color:#fff;padding:8px 16px;text-decoration:none;border-radius:5px;font-size:14px;margin-right:10px;">🔧 Test API</a>
                <a href="access_logs.php" style="background:#7fffa6;color:#1a2335;padding:8px 16px;text-decoration:none;border-radius:5px;font-size:14px;">📊 View Logs</a>
            </div>
            <div style="font-size:0.9rem;color:#888;">
                <strong>How it works:</strong><br>
                1. Generate a temporary PIN<br>
                2. Share the PIN with authorized person<br>
                3. PIN will work on hardware for specified duration<br>
                4. PIN automatically expires after time limit<br>
                <br>
                <strong>Hardware API:</strong> http://192.168.142.246/Cabinet11/cabinet_api.php
            </div>
        </div>
        <?php endif; ?>
        <div class="approval">
            <h3>Approve access for <?php echo $pendingUser; ?>?</h3>
            <button class="approve">APPROVE</button>
            <button class="deny">DENY</button>
        </div>
    </div>
</body>
</html> 