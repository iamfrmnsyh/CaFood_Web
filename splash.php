<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>CaFood • Loading...</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow: hidden;
            position: relative;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%);
            animation: pulse 3s ease-in-out infinite;
        }
        
        body::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 80% 80%, rgba(255,255,255,0.08) 0%, transparent 60%);
            animation: pulse 3s ease-in-out infinite reverse;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
        }
        
        /* Floating particles */
        .particle {
            position: absolute;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            pointer-events: none;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
            50% { opacity: 0.6; }
        }
        
        /* Main Content Container */
        .splash-container {
            text-align: center;
            z-index: 10;
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Logo Container */
        .logo-container {
            position: relative;
            margin-bottom: 28px;
        }
        
        /* Logo Image - UKURAN DIPERBESAR */
        .logo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 28px;
            animation: logoEntrance 0.8s cubic-bezier(0.34, 1.2, 0.64, 1) forwards;
            box-shadow: 0 15px 30px -8px rgba(0, 0, 0, 0.25);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes logoEntrance {
            from {
                opacity: 0;
                transform: scale(0.5) rotate(-10deg);
            }
            to {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }
        
        /* Logo Glow Effect */
        .logo-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            border-radius: 50%;
            animation: glow 2s ease-in-out infinite;
            z-index: -1;
        }
        
        @keyframes glow {
            0%, 100% {
                opacity: 0.3;
                transform: translate(-50%, -50%) scale(0.9);
            }
            50% {
                opacity: 0.8;
                transform: translate(-50%, -50%) scale(1.15);
            }
        }
        
        /* App Name */
        .app-name {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #FFFFFF, #FFE0B2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
            animation: textReveal 0.8s ease-out 0.2s both;
        }
        
        @keyframes textReveal {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Tagline */
        .tagline {
            font-size: 0.85rem;
            font-weight: 500;
            color: rgba(255,255,255,0.8);
            letter-spacing: 2px;
            margin-bottom: 45px;
            animation: fadeIn 0.8s ease-out 0.4s both;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Loading Bar */
        .loading-container {
            width: 220px;
            margin: 0 auto;
        }
        
        .loading-bar {
            width: 100%;
            height: 3px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 12px;
        }
        
        .loading-progress {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #FFD89B, #FFFFFF);
            border-radius: 10px;
            animation: loading 2s ease-in-out forwards;
        }
        
        @keyframes loading {
            0% { width: 0%; }
            30% { width: 40%; }
            60% { width: 75%; }
            100% { width: 100%; }
        }
        
        .loading-text {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
            font-weight: 500;
            letter-spacing: 1px;
        }
        
        /* Dots Animation */
        .dots {
            display: inline-block;
        }
        
        .dots span {
            animation: blink 1.4s infinite;
        }
        
        .dots span:nth-child(2) { animation-delay: 0.2s; }
        .dots span:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes blink {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }
        
        /* Footer */
        .footer {
            position: absolute;
            bottom: 30px;
            font-size: 0.7rem;
            color: rgba(255,255,255,0.4);
            text-align: center;
            z-index: 10;
            animation: fadeIn 1s ease-out 0.8s both;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .logo { width: 100px; height: 100px; border-radius: 24px; }
            .logo-glow { width: 130px; height: 130px; }
            .app-name { font-size: 1.8rem; }
            .tagline { font-size: 0.75rem; letter-spacing: 1px; }
        }
        
        @media (max-width: 480px) {
            .logo { width: 85px; height: 85px; border-radius: 20px; }
            .logo-glow { width: 110px; height: 110px; }
            .app-name { font-size: 1.5rem; }
        }
    </style>
    <script>
        // Create floating particles
        function createParticles() {
            const particleCount = 30;
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                const size = Math.random() * 6 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 5 + 's';
                particle.style.animationDuration = (Math.random() * 4 + 3) + 's';
                document.body.appendChild(particle);
            }
        }
        
        // Redirect after splash
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            setTimeout(function() {
                var loggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
                if (loggedIn) {
                    window.location.href = 'index.php';
                } else {
                    window.location.href = 'login.php';
                }
            }, 2500);
        });
    </script>
</head>
<body>
    <div class="splash-container">
        <div class="logo-container">
            <div class="logo-glow"></div>
            <img class="logo" src="assets/images/splash-logo.jpeg" alt="CaFood Logo" 
                 onerror="this.src='https://placehold.co/200x200/FFFFFF/6C4CF1?text=🍽️'; this.onerror=null;">
        </div>
        <h1 class="app-name">CaFood</h1>
        <p class="tagline">Your Daily Cafe Companion</p>
        
        <div class="loading-container">
            <div class="loading-bar">
                <div class="loading-progress"></div>
            </div>
            <div class="loading-text">
                Memuat<span class="dots"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <span>© 2024 CaFood • Temukan makanan favoritmu</span>
    </div>
</body>
</html>