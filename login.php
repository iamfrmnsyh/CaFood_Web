<?php
session_start();
require_once __DIR__ . '/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] == 'admin') {
                header('Location: dashboard-admin.php');
            } elseif ($user['role'] == 'stand') {
                header('Location: dashboard-stand.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Email atau password salah!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login • CaFood</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 40px 40px;
            animation: floatGrid 20s linear infinite;
        }
        
        @keyframes floatGrid {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        /* Login Card - LEBIH PENDEK */
        .login-container {
            width: 100%;
            max-width: 420px;
            margin: 20px;
            position: relative;
            z-index: 10;
        }
        
        .login-card {
            background: white;
            border-radius: 28px;
            padding: 32px 32px 36px;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.2);
        }
        
        /* Logo Section - LEBIH KECIL */
        .logo-section {
            text-align: center;
            margin-bottom: 24px;
        }
        
        .logo-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }
        
        .logo-icon i {
            font-size: 1.6rem;
            color: white;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .logo-tagline {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-top: 4px;
        }
        
        /* Title - LEBIH KECIL */
        .login-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
            text-align: center;
        }
        
        .login-subtitle {
            font-size: 0.75rem;
            color: #64748b;
            text-align: center;
            margin-bottom: 24px;
        }
        
        /* Form Groups - LEBIH RAPAT */
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-wrapper i:first-child {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 0.9rem;
            z-index: 1;
        }
        
        .input-wrapper input {
            width: 100%;
            padding: 12px 45px 12px 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
            background: #f8fafc;
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            background: none;
            border: none;
            font-size: 0.9rem;
            padding: 0;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
        
        /* Options - LEBIH SIMPLE */
        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            font-size: 0.75rem;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            color: #64748b;
        }
        
        .checkbox-label input {
            width: 14px;
            height: 14px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        /* Button */
        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -5px rgba(102, 126, 234, 0.4);
        }
        
        /* Register Link */
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.8rem;
            color: #64748b;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        /* Error Alert */
        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 10px 14px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 24px 24px 28px;
            }
            
            .logo-icon {
                width: 48px;
                height: 48px;
            }
            
            .logo-icon i {
                font-size: 1.3rem;
            }
            
            .logo-text {
                font-size: 1.3rem;
            }
            
            .login-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="logo-text">CaFood</div>
                <div class="logo-tagline">Your Daily Cafe Companion</div>
            </div>
            
            <h1 class="login-title">Selamat Datang! 👋</h1>
            <p class="login-subtitle">Masuk untuk memesan makanan</p>
            
            <?php if($error): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="contoh@email.com" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="far fa-eye-slash" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="login-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Ingat saya
                    </label>
                    <a href="forgot-password.php" class="forgot-link">Lupa password?</a>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    Masuk
                </button>
            </form>
            
            <div class="register-link">
                Belum punya akun? <a href="register.php">Daftar</a>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'far fa-eye';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'far fa-eye-slash';
            }
        }
    </script>
</body>
</html>