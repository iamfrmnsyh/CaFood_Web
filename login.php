<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CaFood</title>
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
            background: linear-gradient(135deg, #F5F7FA 0%, #E8ECF0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 440px;
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card {
            background: white;
            border-radius: 32px;
            padding: 40px 32px;
            border: 1px solid rgba(108,76,241,0.15);
            box-shadow: 0 20px 40px rgba(0,0,0,0.05), 0 0 0 1px rgba(108,76,241,0.05);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 25px rgba(108,76,241,0.25);
        }
        
        .logo-icon i {
            font-size: 32px;
            color: white;
        }
        
        .logo-text-large {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        
        .logo-subtitle {
            font-size: 13px;
            color: #888;
            font-weight: 500;
        }
        
        .welcome-section {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .welcome-title {
            font-size: 22px;
            font-weight: 700;
            color: #1A1A2E;
            margin-bottom: 8px;
        }
        
        .welcome-desc {
            font-size: 13px;
            color: #888;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .input-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i:first-child {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 16px;
            z-index: 1;
        }
        
        .input-field {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1.5px solid #E8ECF0;
            border-radius: 16px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            background: white;
        }
        
        .input-field:focus {
            outline: none;
            border-color: #6C4CF1;
            box-shadow: 0 0 0 3px rgba(108,76,241,0.1);
        }
        
        .input-field.error {
            border-color: #EF4444;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon .input-field {
            padding-right: 48px;
        }
        
        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
            font-size: 18px;
            z-index: 10;
            background: white;
            padding-left: 4px;
        }
        
        .toggle-password:hover {
            color: #6C4CF1;
        }
        
        .error-message {
            color: #EF4444;
            font-size: 11px;
            margin-top: 6px;
            display: none;
        }
        
        .forgot-link {
            text-align: right;
            margin-bottom: 24px;
        }
        
        .forgot-link a {
            color: #6C4CF1;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
        }
        
        .forgot-link a:hover {
            text-decoration: underline;
        }
        
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            border: none;
            border-radius: 60px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 24px;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 12px rgba(108,76,241,0.25);
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(108,76,241,0.35);
        }
        
        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #E8ECF0;
        }
        
        .divider span {
            padding: 0 16px;
            color: #aaa;
            font-size: 12px;
        }
        
        .google-btn {
            width: 100%;
            padding: 12px;
            background: white;
            border: 1.5px solid #E8ECF0;
            border-radius: 60px;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.2s;
            margin-bottom: 24px;
            font-family: 'Inter', sans-serif;
        }
        
        .google-btn:hover {
            background: #F8F9FA;
            border-color: #6C4CF1;
        }
        
        .google-btn i {
            font-size: 18px;
            color: #DB4437;
        }
        
        .signup-link {
            text-align: center;
            font-size: 13px;
            color: #666;
        }
        
        .signup-link a {
            color: #6C4CF1;
            font-weight: 600;
            text-decoration: none;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            z-index: 1000;
            animation: slideDown 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .toast.success {
            background: #10B981;
            color: white;
        }
        
        .toast.error {
            background: #EF4444;
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
            width: 18px;
            height: 18px;
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="logo-text-large">CaFood</div>
                <div class="logo-subtitle">Cafe & Food Marketplace</div>
            </div>
            
            <div class="welcome-section">
                <div class="welcome-title">Selamat Datang di CaFood!</div>
                <div class="welcome-desc">Aplikasi pemesanan makanan dan minuman</div>
            </div>
            
            <form id="loginForm">
                <div class="form-group">
                    <label class="input-label">Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" class="input-field" id="email" placeholder="Masukkan email Anda" autocomplete="off">
                    </div>
                    <div class="error-message" id="emailError">Email tidak valid</div>
                </div>
                
                <div class="form-group">
                    <label class="input-label">Password</label>
                    <div class="input-icon">
                        <input type="password" class="input-field" id="password" placeholder="Masukkan password" autocomplete="off">
                        <span class="toggle-password" onclick="togglePassword('password', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="error-message" id="passwordError">Password harus diisi</div>
                </div>
                
                <div class="forgot-link">
                    <a href="#" id="forgotPasswordBtn">Lupa Password?</a>
                </div>
                
                <button type="submit" class="login-btn" id="loginBtn">Masuk</button>
            </form>
            
            <div class="divider">
                <span>atau</span>
            </div>
            
            <button class="google-btn" id="googleLoginBtn">
                <i class="fab fa-google"></i> Lanjutkan dengan Google
            </button>
            
            <div class="signup-link">
Belum punya akun? <a href="register.php">Sign Up</a>
            </div>
        </div>
    </div>
    
    <script type="module">
        import './js/api-client.js';
        const { login } = window.API;

        
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const loginBtn = document.getElementById('loginBtn');
        const forgotBtn = document.getElementById('forgotPasswordBtn');
        const googleBtn = document.getElementById('googleLoginBtn');
        
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
        window.togglePassword = togglePassword;
        
        emailInput.value = '';
        passwordInput.value = '';
        
        function showToast(message, type = 'success') {
            const existingToast = document.querySelector('.toast');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }
        
        function validateForm() {
            let isValid = true;
            const email = emailInput.value.trim();
            const password = passwordInput.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!email) {
                document.getElementById('emailError').style.display = 'block';
                emailInput.classList.add('error');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                document.getElementById('emailError').style.display = 'block';
                emailInput.classList.add('error');
                isValid = false;
            } else {
                document.getElementById('emailError').style.display = 'none';
                emailInput.classList.remove('error');
            }
            
            if (!password) {
                document.getElementById('passwordError').style.display = 'block';
                passwordInput.classList.add('error');
                isValid = false;
            } else {
                document.getElementById('passwordError').style.display = 'none';
                passwordInput.classList.remove('error');
            }
            
            return isValid;
        }
        
        function redirectByRole(role) {
            if (role === 'customer') window.location.href = 'home.php';
            else if (role === 'stand' || role === 'operator') window.location.href = 'dashboard-stand.php';
            else if (role === 'admin') window.location.href = 'dashboard-admin.php';
            else window.location.href = 'home.php';
        }

        function setSession(user) {
            const userSession = {
                id: user.id,
                uid: user.id,
                name: user.name,
                email: user.email,
                role: user.role || 'customer'
            };
            localStorage.setItem('currentUser', JSON.stringify(userSession));
            showToast(`✅ Selamat datang, ${userSession.name}!`, 'success');
            setTimeout(() => redirectByRole(userSession.role), 800);
        }

        
        forgotBtn.addEventListener('click', (e) => {
            e.preventDefault();
            showToast('Fitur reset password belum tersedia (backend mode).', 'error');
        });

        
        // Google login belum tersedia pada backend mode
        googleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            showToast('Google login belum tersedia (backend mode).', 'error');
        });

        // ============ EMAIL/PASSWORD LOGIN ============
        document.getElementById('loginForm').addEventListener('submit', async (e) => {

            e.preventDefault();
            
            if (!validateForm()) return;
            
            const email = emailInput.value.trim();
            const password = passwordInput.value;
            
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<span class="spinner"></span> Memproses...';
            
            console.log("🔐 Login attempt:", { email });
            
            try {
                const result = await login({ email, password });
                if (result?.user) {
                    setSession({
                        id: result.user.id,
                        name: result.user.name,
                        email: result.user.email,
                        role: result.user.role,
                    });
                } else {
                    showToast(result?.error || 'Login gagal', 'error');
                    loginBtn.disabled = false;
                    loginBtn.innerHTML = 'Masuk';
                    return;
                }

                
            } catch (error) {
                console.error('Login error:', error);
                showToast('Login gagal. Periksa email/password.', 'error');
                
                loginBtn.disabled = false;
                loginBtn.innerHTML = 'Masuk';
            }

        });
        
        emailInput.addEventListener('input', () => {
            if (emailInput.value.trim()) {
                document.getElementById('emailError').style.display = 'none';
                emailInput.classList.remove('error');
            }
        });
        
        passwordInput.addEventListener('input', () => {
            if (passwordInput.value) {
                document.getElementById('passwordError').style.display = 'none';
                passwordInput.classList.remove('error');
            }
        });
        
        const savedUser = localStorage.getItem('currentUser');
        if (savedUser) {
            console.log("Already logged in as:", JSON.parse(savedUser));
        }
    </script>
</body>
</html>