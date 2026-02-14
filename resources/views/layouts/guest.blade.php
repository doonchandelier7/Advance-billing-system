<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f1117">
    <title>@yield('title', 'Login') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        (function(){
            var t = localStorage.getItem('abs-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <style>
        :root, [data-theme="dark"] {
            --guest-bg: #0f1117;
            --guest-card-bg: #1a1e25;
            --guest-card-border: rgba(255,255,255,0.08);
            --guest-text: #fff;
            --guest-text-sub: rgba(255,255,255,0.5);
            --guest-text-label: rgba(255,255,255,0.7);
            --guest-text-link: #a4b4f4;
            --guest-text-ssl: rgba(255,255,255,0.35);
            --guest-input-bg: rgba(255,255,255,0.05);
            --guest-input-border: rgba(255,255,255,0.1);
            --guest-input-color: #fff;
            --guest-input-placeholder: rgba(255,255,255,0.35);
            --guest-input-icon: rgba(255,255,255,0.35);
            --guest-checkbox-label: rgba(255,255,255,0.6);
            --guest-alert-bg: rgba(255,118,117,0.15);
            --guest-alert-border: rgba(255,118,117,0.25);
            --guest-alert-color: #ff7675;
            --guest-right-bg: transparent;
        }
        [data-theme="light"] {
            --guest-bg: #eef0f6;
            --guest-card-bg: #ffffff;
            --guest-card-border: #ddd8f7;
            --guest-text: #1e1b4b;
            --guest-text-sub: #6b6798;
            --guest-text-label: #4a4678;
            --guest-text-link: #667eea;
            --guest-text-ssl: #a09cc0;
            --guest-input-bg: #f6f5ff;
            --guest-input-border: #c9c3e8;
            --guest-input-color: #1e1b4b;
            --guest-input-placeholder: #a09cc0;
            --guest-input-icon: #7a76a8;
            --guest-checkbox-label: #4a4678;
            --guest-alert-bg: #fef2f2;
            --guest-alert-border: #fecaca;
            --guest-alert-color: #b91c1c;
            --guest-right-bg: #eef0f6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--guest-bg);
            min-height: 100vh;
            transition: background-color 0.3s ease;
        }
        .guest-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative; overflow: hidden;
        }
        .guest-left::before { content:''; position:absolute; top:-30%; right:-20%; width:500px; height:500px; background:rgba(255,255,255,0.08); border-radius:50%; }
        .guest-left::after { content:''; position:absolute; bottom:-40%; left:-10%; width:400px; height:400px; background:rgba(255,255,255,0.04); border-radius:50%; }
        .guest-left .content { position:relative; z-index:1; }
        .feature-box { background:rgba(255,255,255,0.1); border-radius:12px; padding:16px; }
        .feature-box .icon { width:40px; height:40px; background:rgba(255,255,255,0.15); border-radius:10px; display:flex; align-items:center; justify-content:center; margin-bottom:10px; }
        .feature-box h6 { font-weight:600; margin-bottom:4px; }
        .feature-box p { font-size:0.8rem; color:rgba(255,255,255,0.7); margin:0; }
        .guest-right {
            background: var(--guest-right-bg);
            transition: background-color 0.3s ease;
        }
        .login-card {
            background: var(--guest-card-bg);
            border: 1px solid var(--guest-card-border);
            border-radius: 16px;
            padding: 36px;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        [data-theme="light"] .login-card {
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .form-control {
            background: var(--guest-input-bg) !important;
            border: 1px solid var(--guest-input-border) !important;
            color: var(--guest-input-color) !important;
            border-radius: 10px !important;
            padding: 12px 16px 12px 44px !important;
            height: auto !important;
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.15) !important;
        }
        .form-control::placeholder { color: var(--guest-input-placeholder) !important; }
        .input-wrap { position: relative; }
        .input-wrap i { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:var(--guest-input-icon); transition: color 0.3s ease; }
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: 0;
            border-radius: 10px;
            color: #fff;
            font-weight: 600;
            padding: 14px;
            font-size: 1rem;
        }
        .btn-login:hover { opacity:0.9; box-shadow:0 6px 20px rgba(102,126,234,0.3); color:#fff; }
        .alert {
            background: var(--guest-alert-bg) !important;
            border: 1px solid var(--guest-alert-border) !important;
            color: var(--guest-alert-color) !important;
            border-radius: 10px !important;
        }
        .custom-checkbox label { color: var(--guest-checkbox-label); }
        a { color: var(--guest-text-link); }
        label { color: var(--guest-text-label); font-weight: 500; transition: color 0.3s ease; }

        /* Guest theme toggle */
        .guest-theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            border: 2px solid var(--guest-card-border);
            background: var(--guest-card-bg);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: var(--guest-text-label);
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .guest-theme-toggle:hover {
            border-color: #667eea;
            box-shadow: 0 4px 15px rgba(102,126,234,0.2);
            transform: scale(1.05);
        }
        [data-theme="dark"] .guest-theme-toggle .fa-sun { display: none; }
        [data-theme="dark"] .guest-theme-toggle .fa-moon { display: inline; }
        [data-theme="light"] .guest-theme-toggle .fa-sun { display: inline; color: #667eea; }
        [data-theme="light"] .guest-theme-toggle .fa-moon { display: none; }
        [data-theme="light"] .login-card {
            box-shadow: 0 4px 24px rgba(102,126,234,0.1);
        }
    </style>
    @stack('styles')
</head>
<body class="hold-transition" data-theme="dark">

{{-- Theme Toggle Button --}}
<button class="guest-theme-toggle" id="guestThemeToggle" title="Toggle Dark/Light Mode" aria-label="Toggle dark and light mode">
    <i class="fas fa-moon"></i>
    <i class="fas fa-sun"></i>
</button>

<div class="d-flex flex-column flex-lg-row min-vh-100">
    {{-- Left Panel --}}
    <div class="guest-left d-none d-lg-flex flex-column justify-content-between p-5 text-white" style="flex:0 0 45%; max-width:45%;">
        <div class="content">
            <div class="d-flex align-items-center mb-5">
                <div style="width:46px; height:46px; background:rgba(255,255,255,0.2); border-radius:12px; display:flex; align-items:center; justify-content:center; margin-right:14px;">
                    <i class="fas fa-bolt fa-lg"></i>
                </div>
                <span style="font-size:1.4rem; font-weight:700;">{{ config('app.name') }}</span>
            </div>
            <h2 style="font-weight:700; font-size:2rem; margin-bottom:16px; line-height:1.3;">Advanced Billing &<br>Accounting System</h2>
            <p style="color:rgba(255,255,255,0.8); font-size:1rem; line-height:1.7; margin-bottom:36px;">
                Streamline invoicing, manage finances, and generate reports — all from one platform.
            </p>
            <div class="row">
                @foreach([['fa-file-invoice','GST Compliant','GSTIN, HSN & E-Way Bill'],['fa-robot','AI Powered','Smart invoice scanning'],['fa-chart-line','Reports','P&L & Balance Sheet'],['fa-shield-alt','Secure','Enterprise-grade security']] as $f)
                <div class="col-6 mb-3">
                    <div class="feature-box">
                        <div class="icon"><i class="fas {{ $f[0] }}"></i></div>
                        <h6>{{ $f[1] }}</h6>
                        <p>{{ $f[2] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div style="color:rgba(255,255,255,0.5); font-size:0.85rem;">&copy; {{ date('Y') }} {{ config('app.name') }}</div>
    </div>

    {{-- Right Panel --}}
    <div class="d-flex flex-grow-1 align-items-center justify-content-center p-4 guest-right">
        <div style="width:100%; max-width:400px;">
            <div class="text-center d-lg-none mb-4">
                <div style="width:56px; height:56px; background:linear-gradient(135deg,#667eea,#764ba2); border-radius:14px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px;">
                    <i class="fas fa-bolt fa-lg" style="color:#fff;"></i>
                </div>
                <h4 style="color:var(--guest-text); font-weight:700;">{{ config('app.name') }}</h4>
            </div>
            <div class="login-card">
                <div class="text-center mb-4">
                    <h4 style="color:var(--guest-text); font-weight:700; font-size:1.4rem; margin-bottom:6px;">@yield('card-title', 'Welcome Back')</h4>
                    <p style="color:var(--guest-text-sub); margin:0; font-size:0.9rem;">Sign in to your account</p>
                </div>
                @yield('content')
            </div>
            <p class="text-center mt-4" style="color:var(--guest-text-ssl); font-size:0.8rem;">
                <i class="fas fa-lock mr-1"></i> Secured with SSL
            </p>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

{{-- Theme Toggle Script --}}
<script>
(function(){
    var toggle = document.getElementById('guestThemeToggle');
    var html = document.documentElement;
    var body = document.body;

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        body.setAttribute('data-theme', theme);
        localStorage.setItem('abs-theme', theme);
    }

    var saved = localStorage.getItem('abs-theme') || 'dark';
    applyTheme(saved);

    if (toggle) {
        toggle.addEventListener('click', function(){
            var current = html.getAttribute('data-theme') || 'dark';
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    }
})();
</script>

@stack('scripts')
</body>
</html>
