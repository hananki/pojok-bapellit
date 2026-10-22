<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Pojok Bapelit</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: #f8fafc;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            /* Gradient Hijau ke Biru sesuai Navbar */
            background: linear-gradient(135deg, #10b981 0%, #0d9488 50%, #2563eb 100%);
            position: relative;
            overflow: hidden;
        }

        /* Ornamen Background (Lingkaran Transparan) */
        .login-wrapper::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -50px;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: 1;
        }

        .login-wrapper::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -100px;
            width: 300px;
            height: 300px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            z-index: 1;
        }

        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 950px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        /* Left Side - Illustration */
        .login-illustration {
            background: #f8fafc;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 550px;
            border-right: 1px solid #e2e8f0;
            position: relative;
        }

        /* Ornamen Tambahan di Kiri */
        .login-illustration::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(to right, #10b981, #2563eb);
        }

        .bird-container {
            width: 320px;
            height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .bird-svg {
            width: 100%;
            height: 100%;
        }

        /* Bird Animations */
        @keyframes wingFlap {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(-8deg); }
        }

        @keyframes bobbing {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        @keyframes floatLeft {
            0%, 100% { transform: translateX(0px) translateY(0px); }
            50% { transform: translateX(-10px) translateY(-8px); }
        }

        @keyframes floatRight {
            0%, 100% { transform: translateX(0px) translateY(0px); }
            50% { transform: translateX(10px) translateY(-8px); }
        }

        @keyframes swayLeft {
            0%, 100% { transform: rotate(0deg) translateY(0px); }
            50% { transform: rotate(2deg) translateY(-6px); }
        }

        @keyframes swayRight {
            0%, 100% { transform: rotate(0deg) translateY(0px); }
            50% { transform: rotate(-2deg) translateY(-6px); }
        }

        .bird-wings {
            animation: wingFlap 0.6s ease-in-out infinite;
            transform-origin: center;
        }

        .bird-body {
            animation: bobbing 2s ease-in-out infinite;
        }

        .flower-left {
            animation: floatLeft 3s ease-in-out infinite;
        }

        .flower-right {
            animation: floatRight 3.2s ease-in-out infinite;
        }

        .flower-top {
            animation: swayLeft 2.8s ease-in-out infinite;
        }

        .flower-bottom {
            animation: swayRight 3.4s ease-in-out infinite;
        }

        /* Right Side - Form */
        .login-form-section {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        .form-header {
            margin-bottom: 32px;
            text-align: center;
            animation: slideInDown 0.6s ease-out forwards;
        }

        .form-title {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .form-subtitle {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
        }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 13px;
            font-weight: 500;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 24px;
            animation: slideInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .form-group:nth-child(1) { animation-delay: 0.2s; }
        .form-group:nth-child(2) { animation-delay: 0.3s; }
        .forgot-container { animation: slideInUp 0.6s ease-out forwards; opacity: 0; animation-delay: 0.4s; }
        .submit-btn { animation: slideInUp 0.6s ease-out forwards; opacity: 0; animation-delay: 0.5s; }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Figtree', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            transition: all 0.3s ease;
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .form-input:focus {
            outline: none;
            border-color: #0d9488;
            background: white;
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1);
        }

        .input-group {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            background: none;
            border: none;
            padding: 6px;
            display: flex;
            align-items: center;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .toggle-password:hover {
            color: #0d9488;
            background: #f1f5f9;
        }

        /* Error Message */
        .error-message {
            color: #ef4444;
            font-size: 12px;
            margin-top: 6px;
            font-weight: 600;
        }

        /* Forgot Password */
        .forgot-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        .forgot-link {
            font-size: 13px;
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s ease;
            font-weight: 500;
        }

        .forgot-link:hover {
            color: #0d9488;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(to right, #10b981, #2563eb);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
        }

        .submit-btn:hover {
            background: linear-gradient(to right, #059669, #1d4ed8);
            box-shadow: 0 8px 20px rgba(13, 148, 136, 0.4);
            transform: translateY(-2px);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
                border-radius: 20px;
            }

            .login-illustration {
                display: none;
            }

            .login-form-section {
                padding: 40px 30px;
            }

            .form-title {
                font-size: 26px;
            }
        }

        @media (max-width: 480px) {
            .login-wrapper {
                padding: 12px;
            }

            .login-form-section {
                padding: 32px 20px;
            }

            .form-title {
                font-size: 22px;
            }

            .form-input {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            
            <!-- Left Side: Illustration -->
            <div class="login-illustration">
                <div class="bird-container">
                    <svg class="bird-svg" viewBox="0 0 300 300" xmlns="http://www.w3.org/2000/svg">
                        <!-- LARGE LEFT FLOWER (animated) -->
                        <g class="flower-left">
                            <circle cx="50" cy="100" r="18" fill="#10b981" opacity="0.9"/>
                            <circle cx="65" cy="115" r="18" fill="#14b8a6" opacity="0.9"/>
                            <circle cx="65" cy="85" r="18" fill="#0d9488" opacity="0.9"/>
                            <circle cx="50" cy="130" r="18" fill="#2563eb" opacity="0.9"/>
                            <circle cx="35" cy="115" r="18" fill="#3b82f6" opacity="0.9"/>
                            <circle cx="50" cy="107" r="14" fill="#60a5fa"/>
                        </g>

                        <!-- LARGE TOP FLOWER (animated) -->
                        <g class="flower-top">
                            <circle cx="150" cy="50" r="16" fill="#0d9488" opacity="0.95"/>
                            <circle cx="165" cy="60" r="16" fill="#14b8a6" opacity="0.95"/>
                            <circle cx="165" cy="40" r="16" fill="#10b981" opacity="0.95"/>
                            <circle cx="150" cy="75" r="16" fill="#2563eb" opacity="0.95"/>
                            <circle cx="135" cy="60" r="16" fill="#3b82f6" opacity="0.95"/>
                            <circle cx="150" cy="60" r="12" fill="#60a5fa"/>
                        </g>

                        <!-- LARGE RIGHT FLOWER (animated) -->
                        <g class="flower-right">
                            <circle cx="250" cy="110" r="18" fill="#14b8a6" opacity="0.9"/>
                            <circle cx="235" cy="125" r="18" fill="#10b981" opacity="0.9"/>
                            <circle cx="235" cy="95" r="18" fill="#2563eb" opacity="0.9"/>
                            <circle cx="250" cy="140" r="18" fill="#3b82f6" opacity="0.9"/>
                            <circle cx="265" cy="125" r="18" fill="#0d9488" opacity="0.9"/>
                            <circle cx="250" cy="117" r="14" fill="#60a5fa"/>
                        </g>

                        <!-- BOTTOM FLOWER LEFT (animated) -->
                        <g class="flower-bottom">
                            <circle cx="80" cy="240" r="15" fill="#10b981" opacity="0.85"/>
                            <circle cx="95" cy="250" r="15" fill="#14b8a6" opacity="0.85"/>
                            <circle cx="95" cy="230" r="15" fill="#2563eb" opacity="0.85"/>
                            <circle cx="80" cy="265" r="15" fill="#3b82f6" opacity="0.85"/>
                            <circle cx="65" cy="250" r="15" fill="#0d9488" opacity="0.85"/>
                            <circle cx="80" cy="248" r="11" fill="#60a5fa"/>
                        </g>

                        <!-- BOTTOM FLOWER RIGHT (animated) -->
                        <g class="flower-left">
                            <circle cx="220" cy="245" r="15" fill="#14b8a6" opacity="0.85"/>
                            <circle cx="205" cy="255" r="15" fill="#10b981" opacity="0.85"/>
                            <circle cx="205" cy="235" r="15" fill="#2563eb" opacity="0.85"/>
                            <circle cx="220" cy="270" r="15" fill="#3b82f6" opacity="0.85"/>
                            <circle cx="235" cy="255" r="15" fill="#0d9488" opacity="0.85"/>
                            <circle cx="220" cy="253" r="11" fill="#60a5fa"/>
                        </g>

                        <!-- Decorative leaves -->
                        <g opacity="0.7">
                            <path d="M 40 80 Q 25 70 20 85 Q 30 95 40 80" stroke="#1e293b" fill="#334155" stroke-width="2"/>
                            <path d="M 260 95 Q 275 85 280 100 Q 270 110 260 95" stroke="#1e293b" fill="#334155" stroke-width="2"/>
                            <path d="M 30 140 Q 15 135 10 150 Q 25 160 30 140" stroke="#1e293b" fill="#334155" stroke-width="2"/>
                            <path d="M 270 150 Q 285 145 290 160 Q 275 170 270 150" stroke="#1e293b" fill="#334155" stroke-width="2"/>
                        </g>

                        <!-- Black accent circles behind bird -->
                        <circle cx="120" cy="150" r="40" fill="#0f172a" opacity="0.05"/>
                        <circle cx="180" cy="170" r="35" fill="#0f172a" opacity="0.05"/>
                        <rect x="130" y="110" width="40" height="40" fill="#0f172a" opacity="0.05" rx="8"/>

                        <!-- Bird Body - Twitter Blue (Diganti jadi Teal/Hijau) -->
                        <g class="bird-body">
                            <!-- Main body -->
                            <ellipse cx="150" cy="160" rx="38" ry="45" fill="#0d9488"/>
                            
                            <!-- Breast -->
                            <ellipse cx="150" cy="165" rx="28" ry="35" fill="#14b8a6"/>
                            
                            <!-- Head -->
                            <circle cx="168" cy="135" r="24" fill="#0d9488"/>
                            
                            <!-- Eye -->
                            <circle cx="176" cy="130" r="5" fill="#1e293b"/>
                            <circle cx="177" cy="129" r="2" fill="white"/>
                            
                            <!-- Beak -->
                            <path d="M 192 135 L 215 135 L 192 138 Z" fill="#fbbf24"/>
                            
                            <!-- Wings -->
                            <g class="bird-wings">
                                <path d="M 135 155 Q 100 140 85 165 Q 100 175 135 168 Z" fill="#0d9488" stroke="#0d9488" stroke-width="1"/>
                                <path d="M 135 155 Q 100 140 85 165 Q 100 175 135 168 Z" fill="#0d9488" opacity="0.6"/>
                                <path d="M 125 160 Q 95 150 80 170" stroke="#14b8a6" fill="none" stroke-width="1.5"/>
                            </g>
                            
                            <!-- Tail -->
                            <path d="M 105 160 Q 60 150 50 180 Q 65 185 105 170 Z" fill="#0d9488" stroke="#0d9488" stroke-width="1"/>
                            <path d="M 100 165 Q 65 155 55 175" stroke="#14b8a6" fill="none" stroke-width="1"/>
                        </g>
                    </svg>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="login-form-section">
                <div class="form-header">
                    <h1 class="form-title">Pojok Bapelit</h1>
                    <p class="form-subtitle">Silakan masuk untuk mengakses layanan</p>
                </div>

                <!-- Alert -->
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
                        @foreach ($errors->all() as $error)
                            <p style="margin: 4px 0;">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email/Username -->
                    <div class="form-group">
                        <label for="login" class="form-label">Username atau Email</label>
                        <input 
                            id="login" 
                            type="text" 
                            name="login" 
                            value="{{ old('login') }}" 
                            required 
                            autofocus
                            placeholder="Masukkan email atau username"
                            class="form-input" />
                        @error('login')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                required
                                placeholder="Masukkan password"
                                class="form-input"
                                style="padding-right: 45px;" />
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <svg id="eyeIcon" width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Forgot Password -->
                    <div class="forgot-container">
                        <span class="forgot-link" style="cursor: default;">
                            Lupa password? Hubungi Admin
                        </span>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="submit-btn">Masuk ke Sistem</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.innerHTML = '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28l.46.46A11.804 11.804 0 001 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3M7.53 9.8l1.55 1.55c-.05.21-.08.42-.08.65 0 1.66 1.34 3 3 3 .24 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zM11.84 9.02l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
            } else {
                pwd.type = 'password';
                icon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
            }
        }

        document.querySelector('form').addEventListener('submit', function() {
            const btn = this.querySelector('.submit-btn');
            btn.disabled = true;
            btn.textContent = 'Memproses...';
        });
    </script>
</body>
</html>