<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CaFood</title>
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
        
        .register-container {
            width: 100%;
            max-width: 480px;
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
        
        .register-card {
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
        
        /* Role Selector Modern */
        .role-selector-modern {
            display: flex;
            gap: 12px;
            margin-bottom: 28px;
            background: #F5F7FA;
            padding: 6px;
            border-radius: 50px;
        }
        
        .role-btn-modern {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            border-radius: 40px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            color: #666;
            text-align: center;
            font-family: 'Inter', sans-serif;
        }
        
        .role-btn-modern.selected {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            box-shadow: 0 2px 8px rgba(108,76,241,0.25);
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
        
        .input-label .required {
            color: #EF4444;
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
        
        /* Toggle Password */
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
        
        /* Password Strength */
        .password-strength {
            margin-top: 8px;
        }
        
        .strength-bar {
            height: 4px;
            background: #E8ECF0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s;
            border-radius: 4px;
        }
        
        .strength-text {
            font-size: 10px;
            color: #aaa;
        }
        
        /* Terms Checkbox */
        .terms-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 24px;
        }
        
        .terms-group input {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            cursor: pointer;
            accent-color: #6C4CF1;
        }
        
        .terms-group label {
            font-size: 12px;
            color: #666;
            cursor: pointer;
        }
        
        .terms-group a {
            color: #6C4CF1;
            text-decoration: none;
        }
        
        .register-btn {
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
            margin-bottom: 20px;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 12px rgba(108,76,241,0.25);
        }
        
        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(108,76,241,0.35);
        }
        
        .register-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .login-link {
            text-align: center;
            font-size: 13px;
            color: #666;
        }
        
        .login-link a {
            color: #6C4CF1;
            font-weight: 600;
            text-decoration: none;
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
        
        #standNameGroup {
            display: none;
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
                <div class="logo-text-large">CaFood</div>
                <div class="logo-subtitle">Cafe & Food Marketplace</div>
            </div>
            
            <div class="welcome-section">
                <div class="welcome-title">Join CaFood</div>
                <div class="welcome-desc">Create your account to start ordering</div>
            </div>
            
            <!-- Role Selector Modern -->
            <div class="role-selector-modern">
                <button type="button" class="role-btn-modern selected" data-role="customer">Customer</button>
                <button type="button" class="role-btn-modern" data-role="stand">Stand Owner</button>
            </div>
            
            <form id="registerForm">
                <!-- Full Name -->
                <div class="form-group">
                    <label class="input-label">Full Name <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" class="input-field" id="fullName" placeholder="Enter your full name" autocomplete="off">
                    </div>
                    <div class="error-message" id="nameError">Please enter your full name</div>
                </div>
                
                <!-- Email -->
                <div class="form-group">
                    <label class="input-label">Email Address <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" class="input-field" id="email" placeholder="you@example.com" autocomplete="off">
                    </div>
                    <div class="error-message" id="emailError">Please enter a valid email address</div>
                </div>
                
                <!-- Phone Number -->
                <div class="form-group">
                    <label class="input-label">Phone Number</label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone"></i>
                        <input type="tel" class="input-field" id="phone" placeholder="0812-3456-7890" autocomplete="off">
                    </div>
                </div>
                
                <!-- Password -->
                <div class="form-group">
                    <label class="input-label">Password <span class="required">*</span></label>
                    <div class="input-icon">
                        <input type="password" class="input-field" id="password" placeholder="Create a password" autocomplete="off">
                        <span class="toggle-password" onclick="togglePassword('password', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                    <div class="error-message" id="passwordError">Password must be at least 6 characters</div>
                </div>
                
                <!-- Confirm Password -->
                <div class="form-group">
                    <label class="input-label">Confirm Password <span class="required">*</span></label>
                    <div class="input-icon">
                        <input type="password" class="input-field" id="confirmPassword" placeholder="Confirm your password" autocomplete="off">
                        <span class="toggle-password" onclick="togglePassword('confirmPassword', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="error-message" id="confirmError">Passwords do not match</div>
                </div>
                
                <!-- Stand Name (only for stand owner) -->
                <div class="form-group" id="standNameGroup">
                    <label class="input-label">Stand Name <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-store"></i>
                        <input type="text" class="input-field" id="standName" placeholder="Enter your stand name" autocomplete="off">
                    </div>
                    <div class="error-message" id="standNameError">Please enter your stand name</div>
                </div>
                
                <!-- Terms Checkbox -->
                <div class="terms-group">
                    <input type="checkbox" id="termsCheckbox">
                    <label for="termsCheckbox">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="register-btn" id="registerBtn">Create Account</button>
            </form>
            
            <div class="login-link">
<a href="login.php">Sign In</a>
            </div>
        </div>
    </div>
    
    <script type="module">
        import { 
            auth, db,
            doc, setDoc,
            createUserWithEmailAndPassword
        } from './js/firebase-config.js';
        
        // ============ DOM ELEMENTS ============
        const fullNameInput = document.getElementById('fullName');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const standNameInput = document.getElementById('standName');
        const termsCheckbox = document.getElementById('termsCheckbox');
        const registerBtn = document.getElementById('registerBtn');
        
        let selectedRole = 'customer';
        
        // ============ ROLE SELECTOR ============
        document.querySelectorAll('.role-btn-modern').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.role-btn-modern').forEach(b => b.classList.remove('selected'));
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
        
        // ============ TOGGLE PASSWORD ============
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
        
        // ============ PASSWORD STRENGTH ============
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
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
                color = '#EF4444';
                width = '25%';
            } else if (strength <= 3) {
                message = 'Fair password';
                color = '#F59E0B';
                width = '50%';
            } else if (strength <= 4) {
                message = 'Good password';
                color = '#10B981';
                width = '75%';
            } else {
                message = 'Strong password!';
                color = '#059669';
                width = '100%';
            }
            
            strengthFill.style.width = width;
            strengthFill.style.backgroundColor = color;
            strengthText.textContent = message;
            strengthText.style.color = color || '#aaa';
        });
        
        // ============ CONFIRM PASSWORD MATCH ============
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value && this.value !== passwordInput.value) {
                document.getElementById('confirmError').style.display = 'block';
                this.classList.add('error');
            } else {
                document.getElementById('confirmError').style.display = 'none';
                this.classList.remove('error');
            }
        });
        
        // ============ VALIDATION ============
        function validateForm() {
            let isValid = true;
            
            const fullName = fullNameInput.value.trim();
            const email = emailInput.value.trim();
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!fullName) {
                document.getElementById('nameError').style.display = 'block';
                fullNameInput.classList.add('error');
                isValid = false;
            } else {
                document.getElementById('nameError').style.display = 'none';
                fullNameInput.classList.remove('error');
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
            
            if (password !== confirmPassword) {
                document.getElementById('confirmError').style.display = 'block';
                confirmPasswordInput.classList.add('error');
                isValid = false;
            }
            
            if (selectedRole === 'stand') {
                const standName = standNameInput.value.trim();
                if (!standName) {
                    document.getElementById('standNameError').style.display = 'block';
                    standNameInput.classList.add('error');
                    isValid = false;
                } else {
                    document.getElementById('standNameError').style.display = 'none';
                    standNameInput.classList.remove('error');
                }
            }
            
            if (!termsCheckbox.checked) {
                showToast('Please agree to the Terms and Conditions', 'error');
                isValid = false;
            }
            
            return isValid;
        }
        
        function showToast(message, type = 'success') {
            const existingToast = document.querySelector('.toast');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        // ============ REGISTER FORM SUBMIT (SIMPAN KE FIREBASE AUTH) ============
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!validateForm()) return;
            
            const email = emailInput.value.trim();
            const password = passwordInput.value;
            const fullName = fullNameInput.value.trim();
            const phone = phoneInput.value.trim();
            const standName = standNameInput.value.trim();
            
            registerBtn.disabled = true;
            registerBtn.innerHTML = '<span class="spinner"></span> Creating account...';
            
            try {
                // 🔥 STEP 1: Buat user di Firebase Authentication
                const userCredential = await createUserWithEmailAndPassword(auth, email, password);
                const user = userCredential.user;
                console.log("✅ User created in Auth:", user.uid);
                
                // 🔥 STEP 2: Simpan data tambahan ke Firestore
                const userData = {
                    uid: user.uid,
                    name: fullName,
                    email: email,
                    phone: phone || '',
                    role: selectedRole,
                    standName: selectedRole === 'stand' ? standName : null,
                    standId: null,
                    createdAt: new Date().toISOString(),
                    isActive: true
                };
                
                await setDoc(doc(db, "users", user.uid), userData);
                console.log("✅ User data saved to Firestore");
                
                // 🔥 STEP 3: Jika Stand Owner, buat juga document stand
                if (selectedRole === 'stand') {
                    // Di sini nanti bisa tambah create stand document
                    console.log("Stand owner registered:", standName);
                }
                
                showToast('✅ Account created successfully! Redirecting to login...', 'success');
                
                setTimeout(() => {
window.location.href = 'login.php';
                }, 2000);
                
            } catch (error) {
                console.error("Registration error:", error.code, error.message);
                
                if (error.code === 'auth/email-already-in-use') {
                    showToast('❌ Email already registered! Please use another email.', 'error');
                } else if (error.code === 'auth/weak-password') {
                    showToast('❌ Password is too weak. Use at least 6 characters.', 'error');
                } else if (error.code === 'auth/invalid-email') {
                    showToast('❌ Invalid email format!', 'error');
                } else {
                    showToast(`❌ Registration failed: ${error.message}`, 'error');
                }
                
                registerBtn.disabled = false;
                registerBtn.innerHTML = 'Create Account';
            }
        });
        
        // Real-time email validation
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
        
        // Kosongkan field saat load
        fullNameInput.value = '';
        emailInput.value = '';
        phoneInput.value = '';
        passwordInput.value = '';
        confirmPasswordInput.value = '';
        standNameInput.value = '';
        termsCheckbox.checked = false;
    </script>
</body>
</html>