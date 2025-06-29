<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Function to get valid PINs from users.json
function getValidPins() {
    $file = __DIR__ . '/users.json';
    $pins = [];
    
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $users = json_decode($json, true);
        
        foreach ($users as $user) {
            // Use password as PIN for now (you might want to add a separate PIN field)
            $pins[] = $user['password'];
        }
    }
    
    // Add temporary PINs
    $tempPinsFile = __DIR__ . '/temp_pins.json';
    if (file_exists($tempPinsFile)) {
        $tempPins = json_decode(file_get_contents($tempPinsFile), true);
        foreach ($tempPins as $tempPin) {
            if (strtotime($tempPin['expires_at']) > time()) {
                $pins[] = $tempPin['pin'];
            }
        }
    }
    
    return $pins;
}

// Function to log access attempts
function logAccess($pin, $status) {
    $logFile = __DIR__ . '/access_log.json';
    $logs = [];
    
    if (file_exists($logFile)) {
        $logs = json_decode(file_get_contents($logFile), true);
    }
    
    $logs[] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'pin' => $pin,
        'status' => $status,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Keep only last 100 entries
    if (count($logs) > 100) {
        $logs = array_slice($logs, -100);
    }
    
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
}

// Handle GET request - return valid PINs
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $pins = getValidPins();
    echo json_encode($pins);
}

// Handle POST request - log access attempt
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    parse_str($input, $data);
    
    $pin = $data['pin_code'] ?? '';
    $status = $data['status'] ?? '';
    
    if ($pin && $status) {
        logAccess($pin, $status);
        echo json_encode(['success' => true, 'message' => 'Access logged']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing pin_code or status']);
    }
}

// Handle unsupported methods
else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?> 