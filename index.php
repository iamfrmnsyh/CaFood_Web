<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>CaFood • Food Marketplace</title>
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
            background: linear-gradient(135deg, #6C4CF1 0%, #8B5CF6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        
        .animated-bg::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            top: 10%;
            left: -100px;
            animation: float 8s ease-in-out infinite;
        }
        
        .animated-bg::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            bottom: 10%;
            right: -100px;
            animation: float 10s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(50px, 50px); }
        }
        
        /* Particle Animation */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            animation: particleFloat 8s infinite ease-in-out;
        }
        
        @keyframes particleFloat {
            0%, 100% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            20% {
                opacity: 0.8;
            }
            80% {
                opacity: 0.5;
            }
        }
        
        /* Splash Container */
        .splash-container {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 20px;
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
        
        /* Logo Wrapper */
        .logo-wrapper {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 30px;
        }
        
        .logo-circle {
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s ease-in-out infinite;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        @keyframes pulse {
            0%, 100% { 
                transform: scale(1); 
                box-shadow: 0 0 0 0 rgba(255,255,255,0.3);
            }
            50% { 
                transform: scale(1.05); 
                box-shadow: 0 0 0 20px rgba(255,255,255,0);
            }
        }
        
        .logo-icon {
            font-size: 4rem;
            color: white;
            animation: floatIcon 2s ease-in-out infinite;
        }
        
        @keyframes floatIcon {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        /* Rings */
        .ring {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.2);
            animation: expand 2s ease-out infinite;
        }
        
        .ring:nth-child(2) {
            width: 170px;
            height: 170px;
            animation-delay: 0.5s;
        }
        
        .ring:nth-child(3) {
            width: 200px;
            height: 200px;
            animation-delay: 1s;
        }
        
        @keyframes expand {
            0% {
                width: 140px;
                height: 140px;
                opacity: 0.8;
            }
            100% {
                width: 240px;
                height: 240px;
                opacity: 0;
            }
        }
        
        /* Text */
        .splash-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            letter-spacing: -0.02em;
            margin-bottom: 12px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .splash-subtitle {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.85);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 40px;
        }
        
        /* Loading Bar */
        .loading-container {
            width: 200px;
            margin: 0 auto;
        }
        
        .loading-bar {
            width: 100%;
            height: 3px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .loading-progress {
            width: 0%;
            height: 100%;
            background: white;
            border-radius: 10px;
            animation: load 2.5s ease-out forwards;
            box-shadow: 0 0 8px rgba(255,255,255,0.5);
        }
        
        @keyframes load {
            0% { width: 0%; }
            30% { width: 40%; }
            60% { width: 75%; }
            100% { width: 100%; }
        }
        
        .loading-text {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.7);
            letter-spacing: 1px;
        }
        
        /* Version */
        .version {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.5);
            animation: fadeIn 1s ease-out 1s forwards;
            opacity: 0;
        }
        
        @keyframes fadeIn {
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="animated-bg"></div>
    <div class="particles" id="particles"></div>
    
    <div class="splash-container">
        <div class="logo-wrapper">
            <div class="ring"></div>
            <div class="ring"></div>
            <div class="ring"></div>
            <div class="logo-circle">
                <div class="logo-icon">
                    <i class="fas fa-utensils"></i>
                </div>
            </div>
        </div>
        
        <h1 class="splash-title">CaFood</h1>
        <p class="splash-subtitle">Food Marketplace</p>
        
        <div class="loading-container">
            <div class="loading-bar">
                <div class="loading-progress"></div>
            </div>
            <div class="loading-text">Loading delicious experience...</div>
        </div>
    </div>
    
    <div class="version">Version 2.0 • Made with ❤️</div>
    
    <script>
        // Create particles
        function createParticles() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 40; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                const size = Math.random() * 6 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 8 + 's';
                particle.style.animationDuration = (Math.random() * 5 + 5) + 's';
                container.appendChild(particle);
            }
        }
        
        createParticles();
        
        // Redirect langsung ke LOGIN setelah 3 detik (tanpa splash.html)
        setTimeout(() => {
            // Fade out animation
            const splashContainer = document.querySelector('.splash-container');
            splashContainer.style.transition = 'opacity 0.5s ease-out';
            splashContainer.style.opacity = '0';
            
            setTimeout(() => {
window.location.href = 'login.php';
            }, 500);
        }, 3000);
    </script>
</body>
</html>