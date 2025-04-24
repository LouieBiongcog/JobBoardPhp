<?php
date_default_timezone_set('Asia/Manila');
include('db.php');


$error = '';
$success = false;
$debugInfo = '';

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    
    $query = "SELECT * FROM users WHERE reset_token=? AND reset_token_expiry > NOW()";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        $error = "Invalid or expired token.";
        
        
        $debugInfo .= "<div class='debug-info'>";
        $debugInfo .= "<p>Debug Information:</p>";
        $debugInfo .= "<p>Token received: " . htmlspecialchars($token) . "</p>";
        
       
        $checkQuery = "SELECT * FROM users WHERE reset_token=?";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "s", $token);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $expiredUser = mysqli_fetch_assoc($checkResult);
            $debugInfo .= "<p>Token exists but expired. Expiry time: " . htmlspecialchars($expiredUser['reset_token_expiry']) . "</p>";
        } else {
            $debugInfo .= "<p>Token not found in database.</p>";
        }
        
        $debugInfo .= "<p>Current server time: " . date('Y-m-d H:i:s') . "</p>";
        $debugInfo .= "</div>";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
       
        $updateQuery = "UPDATE users SET password=?, reset_token=NULL, reset_token_expiry=NULL WHERE id=?";
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "si", $newPassword, $user['id']);
        
        if (mysqli_stmt_execute($updateStmt)) {
            $success = true;
        } else {
            $error = "Error updating password: " . mysqli_error($conn);
        }
    }
} else {
    $error = "Token is required.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .reset-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            transform: translateY(0);
            animation: floatUp 0.5s ease-out forwards;
            opacity: 0;
        }
        
        @keyframes floatUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e6e6e6;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .form-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, #667eea, #764ba2);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(231, 76, 60, 0.1);
            border-radius: 8px;
            animation: shake 0.5s ease;
        }
        
        .success-message {
            color: #2ecc71;
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(46, 204, 113, 0.1);
            border-radius: 8px;
            animation: floatUp 0.5s ease;
        }
        
        .login-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        
        .debug-info {
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            font-size: 14px;
            color: #666;
        }
        
        .debug-info p {
            margin-bottom: 10px;
        }
        
        .password-rules {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="reset-container">
        <h2>Reset Your Password</h2>
        
        <?php if($error): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
                <?= $debugInfo ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success-message">
                <p>Password updated successfully!</p>
                <a href="login.php" class="login-link">Click here to login</a>
            </div>
        <?php elseif(isset($user) && $user): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" required minlength="8">
                    <p class="password-rules">Password must be at least 8 characters long</p>
                </div>
                
                <button type="submit">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </form>
            
            <div class="login-link">
                Remember your password? <a href="login.php">Login here</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.querySelector('.reset-container');
            container.style.opacity = '1';
            
            
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    
                });
            }
        });
    </script>
</body>
</html>