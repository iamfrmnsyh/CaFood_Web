<?php
session_start();

// Include database configuration
require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';
$email = '';

// Verify token if provided
if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resetRequest) {
        $email = $resetRequest['email'];
    } else {
        $error = 'Token reset password tidak valid atau sudah kadaluarsa.';
    }
}

// Handle reset password request (forgot password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = 'Email harus diisi!';
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate unique token
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Delete old tokens for this email
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$email]);
                
                // Insert new token
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$email, $token, $expiresAt]);
                
                // In a real application, send email here
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/reset-password.php?token=" . $token;
                
                $success = "Link reset password telah dikirim ke email Anda.<br>
                           <small style='display:block; margin-top:10px; padding:10px; background:#f0f0f0; border-radius:8px; word-break:break-all;'>
                           <strong>Demo Link:</strong> <a href='$resetLink' target='_blank'>$resetLink</a>
                           </small>";
            } else {
                $error = 'Email tidak ditemukan dalam sistem kami.';
            }
        } catch(PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $error = 'Password harus diisi!';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Konfirmasi password tidak sesuai!';
    } else {
        try {
            // Verify token
            $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
            $stmt->execute([$token]);
            $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resetRequest) {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?");
                $stmt->execute([$hashedPassword, $resetRequest['email']]);
                
                // Delete used token
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->execute([$token]);
                
                $success = 'Password berhasil direset! Silakan login dengan password baru Anda.';
                
                // Redirect after 3 seconds
                header("refresh:3;url=login.php");
            } else {
                $error = 'Token reset password tidak valid atau sudah kadaluarsa.';
            }
        } catch(PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

function checkPasswordStrength($password) {
    $score = 0;
    if (strlen($password) >= 6) $score++;
    if (strlen($password) >= 10) $score++;
    if (preg_match('/[A-Z]/', $password)) $score++;
    if (preg_match('/[0-9]/', $password)) $score++;
    if (preg_match('/[^A-Za-z0-9]/', $password)) $score++;
    
    if ($score <= 1) return ['text' => 'Lemah', 'color' => '#EF4444', 'width' => '25%'];
    if ($score <= 3) return ['text' => 'Sedang', 'color' => '#F59E0B', 'width' => '50%'];
    if ($score <= 4) return ['text' => 'Kuat', 'color' => '#10B981', 'width' => '75%'];
    return ['text' => 'Sangat Kuat', 'color' => '#059669', 'width' => '100%'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CaFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .reset-container {
            background: white;
            border-radius: 25px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .reset-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .reset-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .reset-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .reset-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .reset-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group label .required {
            color: #f56565;
        }
        
        .input-icon {
            position: relative;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .input-icon .form-control {
            padding-right: 45px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        .form-control.error {
            border-color: #f56565;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 1.1rem;
        }
        
        .error-message {
            color: #f56565;
            font-size: 0.75rem;
            margin-top: 5px;
            display: none;
        }
        
        .password-strength {
            margin-top: 8px;
        }
        
        .strength-bar {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
        }
        
        .strength-text {
            font-size: 0.7rem;
            color: #999;
        }
        
        .btn-reset {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }
        
        .btn-reset:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }
        
        .alert-success {
            background: #D1FAE5;
            color: #059669;
            border: 1px solid #A7F3D0;
        }
        
        .alert-error {
            background: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 0.85rem;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .info-note {
            background: #FEF3C7;
            color: #D97706;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <div class="reset-icon">
                <i class="fas fa-key"></i>
            </div>
            <div class="reset-title">Reset Password</div>
            <div class="reset-subtitle">Atur ulang kata sandi akun Anda</div>
        </div>
        
        <div class="reset-body">
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($token) && !$success && empty($error)): ?>
                <!-- Form Reset Password Baru -->
                <form method="POST" action="" id="resetForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="form-group">
                        <label>Password Baru <span class="required">*</span></label>
                        <div class="input-icon">
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Minimal 6 karakter" required>
                            <span class="toggle-password" onclick="togglePassword('new_password', this)">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <div class="strength-text" id="strengthText"></div>
                        </div>
                        <div class="error-message" id="passwordError">Password minimal 6 karakter</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Konfirmasi Password Baru <span class="required">*</span></label>
                        <div class="input-icon">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Masukkan ulang password baru" required>
                            <span class="toggle-password" onclick="togglePassword('confirm_password', this)">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <div class="error-message" id="confirmError">Konfirmasi password tidak sesuai</div>
                    </div>
                    
                    <button type="submit" name="update_password" class="btn-reset" id="submitBtn">
                        <i class="fas fa-save"></i> Reset Password
                    </button>
                </form>
                
            <?php elseif(empty($token) && !$success): ?>
                <!-- Form Request Reset Password -->
                <form method="POST" action="" id="requestForm">
                    <div class="form-group">
                        <label>Email Address <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-envelope" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                            <input type="email" class="form-control" name="email" placeholder="Masukkan email Anda" style="padding-left: 42px;" required>
                        </div>
                        <div class="error-message" id="emailError">Email tidak valid</div>
                    </div>
                    
                    <div class="info-note">
                        <i class="fas fa-info-circle"></i> Kami akan mengirimkan link reset password ke email Anda. Link akan kadaluarsa dalam 1 jam.
                    </div>
                    
                    <button type="submit" name="request_reset" class="btn-reset">
                        <i class="fas fa-paper-plane"></i> Kirim Link Reset
                    </button>
                </form>
                
                <a href="login.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Kembali ke Login
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        function togglePassword(fieldId, element) {
            const field = document.getElementById(fieldId);
            const icon = element.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Password strength checker
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const submitBtn = document.getElementById('submitBtn');
        
        if (newPassword) {
            newPassword.addEventListener('input', function() {
                const password = this.value;
                const strengthFill = document.getElementById('strengthFill');
                const strengthText = document.getElementById('strengthText');
                
                let score = 0;
                if (password.length >= 6) score++;
                if (password.length >= 10) score++;
                if (/[A-Z]/.test(password)) score++;
                if (/[0-9]/.test(password)) score++;
                if (/[^A-Za-z0-9]/.test(password)) score++;
                
                let message = '', color = '', width = '0%';
                
                if (password.length === 0) {
                    message = '';
                    width = '0%';
                } else if (score <= 1) {
                    message = 'Lemah';
                    color = '#EF4444';
                    width = '25%';
                } else if (score <= 3) {
                    message = 'Sedang';
                    color = '#F59E0B';
                    width = '50%';
                } else if (score <= 4) {
                    message = 'Kuat';
                    color = '#10B981';
                    width = '75%';
                } else {
                    message = 'Sangat Kuat';
                    color = '#059669';
                    width = '100%';
                }
                
                strengthFill.style.width = width;
                strengthFill.style.backgroundColor = color;
                strengthText.textContent = message;
                strengthText.style.color = color || '#999';
                
                // Validate password length
                if (password.length > 0 && password.length < 6) {
                    document.getElementById('passwordError').style.display = 'block';
                    newPassword.classList.add('error');
                } else {
                    document.getElementById('passwordError').style.display = 'none';
                    newPassword.classList.remove('error');
                }
                
                validateForm();
            });
        }
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                validateForm();
            });
        }
        
        function validateForm() {
            if (!newPassword || !confirmPassword || !submitBtn) return;
            
            const isValid = newPassword.value.length >= 6 && 
                           newPassword.value === confirmPassword.value;
            
            if (newPassword.value !== confirmPassword.value) {
                document.getElementById('confirmError').style.display = 'block';
                confirmPassword.classList.add('error');
                submitBtn.disabled = true;
            } else {
                document.getElementById('confirmError').style.display = 'none';
                confirmPassword.classList.remove('error');
                submitBtn.disabled = !(newPassword.value.length >= 6);
            }
            
            return isValid;
        }
        
        // Email validation for request form
        const emailInput = document.querySelector('input[name="email"]');
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                const email = this.value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (email && !emailRegex.test(email)) {
                    document.getElementById('emailError').style.display = 'block';
                    this.classList.add('error');
                } else {
                    document.getElementById('emailError').style.display = 'none';
                    this.classList.remove('error');
                }
            });
        }
        
        // Form submit loading state
        const requestForm = document.getElementById('requestForm');
        const resetForm = document.getElementById('resetForm');
        
        if (requestForm) {
            requestForm.addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"]');
                btn.innerHTML = '<span class="spinner"></span> Mengirim...';
                btn.disabled = true;
            });
        }
        
        if (resetForm) {
            resetForm.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return;
                }
                const btn = this.querySelector('button[type="submit"]');
                btn.innerHTML = '<span class="spinner"></span> Mereset...';
                btn.disabled = true;
            });
        }
    </script>
</body>
</html>