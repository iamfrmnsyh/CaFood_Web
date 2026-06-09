<?php
session_start();

// Include database configuration
require_once __DIR__ . '/config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'customer';
    if ($role === 'admin') {
        header('Location: dashboard-admin.php');
    } elseif ($role === 'stand') {
        header('Location: dashboard-stand.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

// Handle AJAX request for registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['role'])) {
            throw new Exception('Missing required fields');
        }
        
        $name = trim($data['name']);
        $email = trim($data['email']);
        $password = $data['password'];
        $role = $data['role'];
        $phone = isset($data['phone']) ? trim($data['phone']) : '';
        $standName = isset($data['standName']) ? trim($data['standName']) : null;
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters');
        }
        
        if (!in_array($role, ['customer', 'stand'])) {
            throw new Exception('Invalid role');
        }
        
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email already registered');
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $email, $hashedPassword, $phone, $role]);
        $userId = $pdo->lastInsertId();
        
        // Insert stand if role is stand
        if ($role === 'stand' && $standName) {
            $stmt = $pdo->prepare("INSERT INTO stands (user_id, stand_name, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $standName]);
            $standId = $pdo->lastInsertId();
            
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            $_SESSION['phone'] = $phone;
            $_SESSION['login_time'] = time();
            $_SESSION['stand_id'] = $standId;
            $_SESSION['stand_name'] = $standName;
            
            $redirectUrl = 'dashboard-stand.php';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            $_SESSION['phone'] = $phone;
            $_SESSION['login_time'] = time();
            
            $redirectUrl = 'index.php';
        }
        
        echo json_encode(['success' => true, 'id' => $userId, 'message' => 'Registration successful', 'redirect' => $redirectUrl]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Register • CaFood</title>
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
            padding: 20px;
        }
        
        /* Register Container - SCROLLABLE TANPA GARIS SCROLLBAR */
        .register-container {
            width: 100%;
            max-width: 460px;
            margin: 0 auto;
            max-height: 100vh;
            overflow-y: auto;
            padding: 10px 0;
            /* Hilangkan scrollbar */
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE dan Edge */
        }
        
        /* Hilangkan scrollbar untuk Chrome, Safari, Opera */
        .register-container::-webkit-scrollbar {
            display: none;
        }
        
        .register-card {
            background: white;
            border-radius: 28px;
            padding: 28px 24px 32px;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.2);
        }
        
        /* Logo Section */
        .logo-section {
            text-align: center;
            margin-bottom: 20px;
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
        
        /* Welcome Section */
        .welcome-section {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .welcome-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }
        
        .welcome-desc {
            font-size: 0.75rem;
            color: #64748b;
        }
        
        /* Role Selector */
        .role-selector {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 50px;
        }
        
        .role-btn {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s;
            color: #64748b;
            text-align: center;
            font-family: 'Inter', sans-serif;
        }
        
        .role-btn.selected {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        /* Form Groups */
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        
        .required {
            color: #ef4444;
            margin-left: 2px;
        }
        
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 0.9rem;
            z-index: 1;
        }
        
        .input-field {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
            background: #f8fafc;
        }
        
        .input-field:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .input-field.error {
            border-color: #ef4444;
        }
        
        /* Password Toggle */
        .toggle-password {
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
        
        .toggle-password:hover {
            color: #667eea;
        }
        
        /* Password Strength */
        .password-strength {
            margin-top: 6px;
        }
        
        .strength-bar {
            height: 4px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 4px;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s;
            border-radius: 4px;
        }
        
        .strength-text {
            font-size: 0.65rem;
            color: #94a3b8;
        }
        
        /* Error Message */
        .error-message {
            color: #ef4444;
            font-size: 0.7rem;
            margin-top: 4px;
            display: none;
        }
        
        /* Terms Group */
        .terms-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
        }
        
        .terms-group input {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        .terms-group label {
            font-size: 0.7rem;
            color: #64748b;
            cursor: pointer;
        }
        
        .terms-group a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .terms-group a:hover {
            text-decoration: underline;
        }
        
        /* Register Button */
        .register-btn {
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
            font-family: 'Inter', sans-serif;
            margin-bottom: 16px;
        }
        
        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -5px rgba(102, 126, 234, 0.4);
        }
        
        .register-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Login Link */
        .login-link {
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        /* Toast */
        .toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 500;
            z-index: 1000;
            animation: slideDown 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .toast.success {
            background: #10b981;
            color: white;
        }
        
        .toast.error {
            background: #ef4444;
            color: white;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
        
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 6px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        #standNameGroup {
            display: none;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .register-card {
                padding: 20px 16px 24px;
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
            
            .welcome-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="logo-text">CaFood</div>
                <div class="logo-tagline">Your Daily Cafe Companion</div>
            </div>
            
            <div class="welcome-section">
                <div class="welcome-title">Join CaFood 👋</div>
                <div class="welcome-desc">Create your account to start ordering</div>
            </div>
            
            <div class="role-selector">
                <button type="button" class="role-btn selected" data-role="customer">
                    <i class="fas fa-user"></i> Customer
                </button>
                <button type="button" class="role-btn" data-role="stand">
                    <i class="fas fa-store"></i> Stand Owner
                </button>
            </div>
            
            <form id="registerForm">
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" class="input-field" id="fullName" placeholder="Enter your full name" autocomplete="off">
                    </div>
                    <div class="error-message" id="nameError">Please enter your full name</div>
                </div>
                
                <div class="form-group">
                    <label>Email Address <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" class="input-field" id="email" placeholder="you@example.com" autocomplete="off">
                    </div>
                    <div class="error-message" id="emailError">Please enter a valid email address</div>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone"></i>
                        <input type="tel" class="input-field" id="phone" placeholder="0812-3456-7890" autocomplete="off">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="input-field" id="password" placeholder="Create a password" autocomplete="off">
                        <button type="button" class="toggle-password" data-target="password">
                            <i class="far fa-eye-slash"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                    <div class="error-message" id="passwordError">Password must be at least 6 characters</div>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-check-circle"></i>
                        <input type="password" class="input-field" id="confirmPassword" placeholder="Confirm your password" autocomplete="off">
                        <button type="button" class="toggle-password" data-target="confirmPassword">
                            <i class="far fa-eye-slash"></i>
                        </button>
                    </div>
                    <div class="error-message" id="confirmError">Passwords do not match</div>
                </div>
                
                <div class="form-group" id="standNameGroup">
                    <label>Stand Name <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-store"></i>
                        <input type="text" class="input-field" id="standName" placeholder="Enter your stand name" autocomplete="off">
                    </div>
                    <div class="error-message" id="standNameError">Please enter your stand name</div>
                </div>
                
                <div class="terms-group">
                    <input type="checkbox" id="termsCheckbox">
                    <label for="termsCheckbox">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="register-btn" id="registerBtn">
                    <i class="fas fa-arrow-right-to-bracket"></i> Create Account
                </button>
            </form>
            
            <div class="login-link">
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Already have an account? Sign In</a>
            </div>
        </div>
    </div>
    
    <script>
        // Role Selector
        let selectedRole = 'customer';
        
        document.querySelectorAll('.role-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                selectedRole = this.dataset.role;
                
                const standNameGroup = document.getElementById('standNameGroup');
                if (selectedRole === 'stand') {
                    standNameGroup.style.display = 'block';
                } else {
                    standNameGroup.style.display = 'none';
                }
            });
        });
        
        // Toggle Password
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        });
        
        // Password Strength
        const passwordInput = document.getElementById('password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let message = '';
            let color = '';
            let width = '0%';
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            if (password.length === 0) {
                message = '';
                width = '0%';
            } else if (strength <= 1) {
                message = 'Weak password';
                color = '#ef4444';
                width = '25%';
            } else if (strength <= 3) {
                message = 'Fair password';
                color = '#f59e0b';
                width = '50%';
            } else if (strength <= 4) {
                message = 'Good password';
                color = '#10b981';
                width = '75%';
            } else {
                message = 'Strong password!';
                color = '#059669';
                width = '100%';
            }
            
            strengthFill.style.width = width;
            strengthFill.style.backgroundColor = color;
            strengthText.textContent = message;
            strengthText.style.color = color || '#94a3b8';
        });
        
        // Confirm Password
        const confirmPassword = document.getElementById('confirmPassword');
        confirmPassword.addEventListener('input', function() {
            if (this.value && this.value !== passwordInput.value) {
                document.getElementById('confirmError').style.display = 'block';
                this.classList.add('error');
            } else {
                document.getElementById('confirmError').style.display = 'none';
                this.classList.remove('error');
            }
        });
        
        // Email Validation
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('input', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                document.getElementById('emailError').style.display = 'block';
                this.classList.add('error');
            } else {
                document.getElementById('emailError').style.display = 'none';
                this.classList.remove('error');
            }
        });
        
        // Validation Function
        function validateForm() {
            let isValid = true;
            
            const fullName = document.getElementById('fullName').value.trim();
            const email = emailInput.value.trim();
            const password = passwordInput.value;
            const confirm = confirmPassword.value;
            const terms = document.getElementById('termsCheckbox').checked;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!fullName) {
                document.getElementById('nameError').style.display = 'block';
                document.getElementById('fullName').classList.add('error');
                isValid = false;
            } else {
                document.getElementById('nameError').style.display = 'none';
                document.getElementById('fullName').classList.remove('error');
            }
            
            if (!email || !emailRegex.test(email)) {
                document.getElementById('emailError').style.display = 'block';
                emailInput.classList.add('error');
                isValid = false;
            } else {
                document.getElementById('emailError').style.display = 'none';
                emailInput.classList.remove('error');
            }
            
            if (!password || password.length < 6) {
                document.getElementById('passwordError').style.display = 'block';
                passwordInput.classList.add('error');
                isValid = false;
            } else {
                document.getElementById('passwordError').style.display = 'none';
                passwordInput.classList.remove('error');
            }
            
            if (password !== confirm) {
                document.getElementById('confirmError').style.display = 'block';
                confirmPassword.classList.add('error');
                isValid = false;
            } else {
                document.getElementById('confirmError').style.display = 'none';
                confirmPassword.classList.remove('error');
            }
            
            if (selectedRole === 'stand') {
                const standName = document.getElementById('standName').value.trim();
                if (!standName) {
                    document.getElementById('standNameError').style.display = 'block';
                    document.getElementById('standName').classList.add('error');
                    isValid = false;
                } else {
                    document.getElementById('standNameError').style.display = 'none';
                    document.getElementById('standName').classList.remove('error');
                }
            }
            
            if (!terms) {
                showToast('Please agree to the Terms and Conditions', 'error');
                isValid = false;
            }
            
            return isValid;
        }
        
        // Toast Function
        function showToast(message, type) {
            const existingToast = document.querySelector('.toast');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        // Form Submit
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!validateForm()) return;
            
            const userData = {
                name: document.getElementById('fullName').value.trim(),
                email: emailInput.value.trim(),
                password: passwordInput.value,
                phone: document.getElementById('phone').value.trim(),
                role: selectedRole
            };
            
            if (selectedRole === 'stand') {
                userData.standName = document.getElementById('standName').value.trim();
            }
            
            const registerBtn = document.getElementById('registerBtn');
            registerBtn.disabled = true;
            registerBtn.innerHTML = '<span class="spinner"></span> Creating account...';
            
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(userData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('✅ Account created successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = result.redirect || 'index.php';
                    }, 1500);
                } else {
                    showToast(result.error || 'Registration failed', 'error');
                    registerBtn.disabled = false;
                    registerBtn.innerHTML = '<i class="fas fa-arrow-right-to-bracket"></i> Create Account';
                }
            } catch (err) {
                showToast('Registration failed. Please try again.', 'error');
                registerBtn.disabled = false;
                registerBtn.innerHTML = '<i class="fas fa-arrow-right-to-bracket"></i> Create Account';
            }
        });
    </script>
</body>
</html>