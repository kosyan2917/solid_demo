<?php

require_once("db.php");
require_once("waf.php");

session_start();

if(isset($_POST['login-submit']))
{
	if(!empty($_POST['username']) && !empty($_POST['password']))
	{
        $username= $_POST['username'];
		$password= sha1($_POST['password']);
        if(waf($username))
        {
            $error_message = "WAF Block - Invalid input detected";
        }
        else
        {
            $res = $conn->query("select * from users where username='$username' and password='$password'");
            if ($res === false) {
                echo "MySQL error: " . $conn->errno . " " . $conn->error;
                // обработайте ошибку (сообщение пользователю и т.п.) и выходите
            } else if($res->num_rows ===1) {
                $_SESSION['username'] = $username;
                $_SESSION['logged_in'] = true;
                header("Location: profile.php");
                exit();
            }
            else
            {
                $error_message = "Invalid username or password";
            }
    }

	}
	else
	{
		$error_message = "Please fill in all fields";
	}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 48px;
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
        }

        .logo-icon i {
            font-size: 36px;
            color: white;
        }

        .company-name {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .company-tagline {
            color: #666;
            font-size: 14px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .input-wrapper {
            position: relative;
            background: #f8f9fa;
            border-radius: 16px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .input-wrapper:focus-within {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .input-wrapper:focus-within .input-icon {
            color: #667eea;
        }

        .form-control {
            border: none;
            background: transparent;
            padding: 18px 20px 18px 50px;
            font-size: 16px;
            color: #1a1a1a;
            width: 100%;
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            box-shadow: none;
        }

        .form-control::placeholder {
            color: #999;
            font-weight: 400;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 18px;
            border-radius: 16px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .links-section {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #eee;
        }

        .links-section a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
            margin: 0 8px;
        }

        .links-section a:hover {
            color: #764ba2;
        }

        .alert {
            border-radius: 16px;
            border: none;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-weight: 500;
        }

        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border-left: 4px solid #16a34a;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px;
                margin: 20px;
            }
            
            .company-name {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="company-name">Secure Login</h1>
            <p class="company-tagline">Secured by Koko Web Application Firewall</p>
        </div>

        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <div class="form-group">
                <div class="input-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" 
                           class="form-control" 
                           name="username" 
                           placeholder="Enter your username"
                           required>
                </div>
            </div>

            <div class="form-group">
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" 
                           class="form-control" 
                           name="password" 
                           placeholder="Enter your password"
                           required>
                </div>
            </div>

            <button type="submit" name="login-submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>
                Sign In
            </button>
        </form>

        <div class="links-section">
            <a href="#"><i class="fas fa-key me-1"></i>Forgot Password?</a>
            <span class="text-muted">|</span>
            <a href="register.php"><i class="fas fa-user-plus me-1"></i>Create Account</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
