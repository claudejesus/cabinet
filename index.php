<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT-SMART SECURE FOR OFFICE CABINET</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(180deg, #2196F3 0%, #1976D2 100%);
            font-family: Arial, sans-serif;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        h1 {
            color: white;
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: normal;
        }

        .user-icon {
            width: 60px;
            height: 60px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-icon svg {
            width: 30px;
            height: 30px;
            fill: white;
        }

        .input-group {
            margin-bottom: 15px;
        }

        input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 16px;
            box-sizing: border-box;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .login-btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #2196F3;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .login-btn:hover {
            background-color: #1976D2;
        }

        .forgot-password {
            margin-top: 10px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password:hover {
            color: white;
        }

        .error-message {
            background: rgba(255, 0, 0, 0.2);
            color: #ffcccc;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .credentials-info {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>IoT-SMART SECURE FOR<br>OFFICE CABINET</h1>
        <div class="user-icon">
            <svg viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
        </div>
        
        <?php
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            $message = '';
            
            switch ($error) {
                case 'empty_fields':
                    $message = 'Please fill in both username and password.';
                    break;
                case 'invalid_credentials':
                    $message = 'Invalid username or password. Please try again.';
                    break;
                default:
                    $message = 'An error occurred. Please try again.';
            }
            
            echo '<div class="error-message">' . htmlspecialchars($message) . '</div>';
        }
        
        if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
            echo '<div style="background: rgba(0, 255, 0, 0.2); color: #ccffcc; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px;">You have been successfully logged out.</div>';
        }
        ?>
        
        <form action="login.php" method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="login-btn">Log In</button>
        </form>
        <a href="#" class="forgot-password">Forgot password?</a>
        
        <div class="credentials-info">
            <strong>Demo Credentials:</strong><br>
            Username: admin<br>
            Password: admin123
        </div>
    </div>
</body>
</html>