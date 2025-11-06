<?php
session_start();
require_once("db.php");

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: profile.php");
    exit();
}


$error_message = '';
$success_message = '';

if(isset($_POST['register-submit'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    
    // Validation
    if(empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($full_name)) {
        $error_message = "Please fill in all fields";
    } elseif(strlen($username) < 3) {
        $error_message = "Username must be at least 3 characters long";
    } elseif(strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long";
    } elseif($password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address";
    } else {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if($result->num_rows > 0) {
            $error_message = "Username already exists. Please choose a different one.";
        } else {
            // Check if email already exists
            $check_email_stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $check_email_stmt->bind_param("s", $email);
            $check_email_stmt->execute();
            $email_result = $check_email_stmt->get_result();
            
            if($email_result->num_rows > 0) {
                $error_message = "Email address already registered. Please use a different email.";
            } else {
                // Generate unique ID (13 characters, same as uniqid)
                $id = uniqid();
                $hashed_password = sha1($password);
                
                // Insert new user
                $insert_stmt = $conn->prepare("INSERT INTO users (id, username, password, email, full_name) VALUES (?, ?, ?, ?, ?)");
                $insert_stmt->bind_param("sssss", $id, $username, $hashed_password, $email, $full_name);
                
                if($insert_stmt->execute()) {
                    $success_message = "Registration successful! You can now login with your credentials.";
                    // Clear form data
                    $username = $email = $full_name = '';
                } else {
                    $db_error = $conn->error;
                    if (strpos($db_error, 'Data too long') !== false) {
                        $error_message = "Registration failed: Database field length error. This is a system issue - please contact support.";
                    } else {
                        $error_message = "Registration failed due to a database error. Error: " . $db_error . ". Please try again or contact support if the problem persists.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
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

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 48px;
            width: 100%;
            max-width: 500px;
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

        .password-strength {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
        }

        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }

        .btn-register {
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

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.4);
        }

        .btn-register:active {
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 32px 24px;
                margin: 20px;
            }
            
            .company-name {
                font-size: 24px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1 class="company-name">Create Account</h1>
            <p class="company-tagline">Secured by Koko Web Application Firewall</p>
        </div>

        <?php if($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="register-form">
            <div class="form-row">
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" 
                               class="form-control" 
                               name="username" 
                               placeholder="Username"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" 
                               class="form-control" 
                               name="email" 
                               placeholder="Email Address"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="input-wrapper">
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" 
                           class="form-control" 
                           name="full_name" 
                           placeholder="Full Name"
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                           required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" 
                               class="form-control" 
                               name="password" 
                               id="password"
                               placeholder="Password"
                               required>
                    </div>
                    <div class="password-strength" id="password-strength"></div>
                </div>
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" 
                               class="form-control" 
                               name="confirm_password" 
                               placeholder="Confirm Password"
                               required>
                    </div>
                </div>
            </div>

            <button type="submit" name="register-submit" class="btn-register">
                <i class="fas fa-user-plus me-2"></i>
                Create Account
            </button>
        </form>

        <div class="links-section">
            <span class="text-muted">Already have an account?</span>
            <a href="index.php"><i class="fas fa-sign-in-alt me-1"></i>Sign In</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            let strengthText = '';
            let strengthClass = '';
            
            if (strength <= 2) {
                strengthText = 'Weak';
                strengthClass = 'strength-weak';
            } else if (strength <= 3) {
                strengthText = 'Medium';
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Strong';
                strengthClass = 'strength-strong';
            }
            
            strengthDiv.textContent = `Password Strength: ${strengthText}`;
            strengthDiv.className = `password-strength ${strengthClass}`;
        });
    </script>
</body>
</html> 