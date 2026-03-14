<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" id="meta-theme-color" content="#1a1d21">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    {{-- Prevent flash of wrong theme --}}
    <script>
        (function(){
            var t = localStorage.getItem('abs-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <style>
        /* ===== CSS VARIABLES ===== */
        :root, [data-theme="dark"] {
            --bg-body: #0f1117;
            --bg-navbar: #1a1d23;
            --bg-sidebar: #12151a;
            --bg-card: #1a1e25;
            --bg-card-hover: rgba(255,255,255,0.03);
            --bg-input: rgba(255,255,255,0.05);
            --bg-input-focus: rgba(255,255,255,0.08);
            --bg-footer: #12151a;
            --bg-table-header: rgba(255,255,255,0.03);
            --bg-hover: rgba(255,255,255,0.06);
            --bg-btn-secondary: rgba(255,255,255,0.08);
            --bg-date-badge: rgba(102,126,234,0.2);

            --border-color: rgba(255,255,255,0.06);
            --border-input: rgba(255,255,255,0.1);
            --border-btn-secondary: rgba(255,255,255,0.12);

            --text-primary: #ffffff;
            --text-secondary: rgba(255,255,255,0.8);
            --text-muted: rgba(255,255,255,0.45);
            --text-nav: rgba(255,255,255,0.7);
            --text-nav-hover: #ffffff;
            --text-sidebar-nav: rgba(255,255,255,0.65);
            --text-nav-header: rgba(255,255,255,0.35);
            --text-date-badge: #a4b4f4;
            --text-table-header: rgba(255,255,255,0.5);
            --text-footer: rgba(255,255,255,0.4);
            --text-placeholder: rgba(255,255,255,0.35);
            --text-input-group: rgba(255,255,255,0.5);

            --link-color: #a4b4f4;
            --link-hover: #c4d0ff;
            --code-bg: rgba(102,126,234,0.15);
            --code-color: #a4b4f4;

            --modal-bg: #1a1e25;
            --modal-border: rgba(255,255,255,0.1);
            --modal-header-border: rgba(255,255,255,0.08);

            --select-option-bg: #1a1e25;
            --select-option-color: #fff;

            --scrollbar-thumb: rgba(255,255,255,0.12);
            --scrollbar-thumb-hover: rgba(255,255,255,0.2);

            --page-link-bg: rgba(255,255,255,0.05);
            --page-link-border: rgba(255,255,255,0.08);
            --page-link-color: rgba(255,255,255,0.7);
            --page-link-hover-bg: rgba(255,255,255,0.1);

            --badge-secondary-bg: rgba(255,255,255,0.15);

            --alert-success-bg: rgba(0,184,148,0.15);
            --alert-success-color: #55efc4;
            --alert-success-border: rgba(0,184,148,0.25);
            --alert-danger-bg: rgba(255,118,117,0.15);
            --alert-danger-color: #ff7675;
            --alert-danger-border: rgba(255,118,117,0.25);
            --alert-warning-bg: rgba(253,203,110,0.15);
            --alert-warning-color: #fdcb6e;
            --alert-warning-border: rgba(253,203,110,0.25);
            --alert-info-bg: rgba(116,185,255,0.15);
            --alert-info-color: #74b9ff;
            --alert-info-border: rgba(116,185,255,0.25);

            --sidebar-user-border: rgba(255,255,255,0.08);
            --logout-color: #ff6b6b;
            --logout-bg: rgba(255,107,107,0.1);
            --logout-bg-hover: rgba(255,107,107,0.2);

            --theme-meta: #1a1d21;

            --invoice-section-bg: rgba(255,255,255,0.02);
            --invoice-section-border: rgba(255,255,255,0.08);
            --invoice-label-color: rgba(255,255,255,0.5);
        }

        [data-theme="light"] {
            /* White + Blue/Violet palette */
            --bg-body: #eef0f6;
            --bg-navbar: #ffffff;
            --bg-sidebar: #2d2b55;
            --bg-card: #ffffff;
            --bg-card-hover: #f4f2ff;
            --bg-input: #f6f5ff;
            --bg-input-focus: #ffffff;
            --bg-footer: #ffffff;
            --bg-table-header: #f4f2ff;
            --bg-hover: #ededfc;
            --bg-btn-secondary: #f0eeff;
            --bg-date-badge: rgba(102,126,234,0.12);

            --border-color: #ddd8f7;
            --border-input: #c9c3e8;
            --border-btn-secondary: #c9c3e8;

            --text-primary: #1e1b4b;
            --text-secondary: #312e5c;
            --text-muted: #6b6798;
            --text-nav: #4a4678;
            --text-nav-hover: #1e1b4b;
            --text-sidebar-nav: rgba(255,255,255,0.72);
            --text-nav-header: rgba(255,255,255,0.4);
            --text-date-badge: #667eea;
            --text-table-header: #6b6798;
            --text-footer: #7a76a8;
            --text-placeholder: #a09cc0;
            --text-input-group: #7a76a8;

            --link-color: #667eea;
            --link-hover: #5a4fcf;
            --code-bg: rgba(102,126,234,0.12);
            --code-color: #5a4fcf;

            --modal-bg: #ffffff;
            --modal-border: #ddd8f7;
            --modal-header-border: #ddd8f7;

            --select-option-bg: #ffffff;
            --select-option-color: #1e1b4b;

            --scrollbar-thumb: #c9c3e8;
            --scrollbar-thumb-hover: #a09cc0;

            --page-link-bg: #ffffff;
            --page-link-border: #ddd8f7;
            --page-link-color: #4a4678;
            --page-link-hover-bg: #f0eeff;

            --badge-secondary-bg: #e4e0f5;

            --alert-success-bg: #ecfdf5;
            --alert-success-color: #065f46;
            --alert-success-border: #a7f3d0;
            --alert-danger-bg: #fef2f2;
            --alert-danger-color: #b91c1c;
            --alert-danger-border: #fecaca;
            --alert-warning-bg: #fffbeb;
            --alert-warning-color: #92400e;
            --alert-warning-border: #fde68a;
            --alert-info-bg: #eef2ff;
            --alert-info-color: #3730a3;
            --alert-info-border: #c7d2fe;

            --sidebar-user-border: rgba(255,255,255,0.12);
            --logout-color: #f87171;
            --logout-bg: rgba(248,113,113,0.12);
            --logout-bg-hover: rgba(248,113,113,0.22);

            --theme-meta: #ffffff;

            --invoice-section-bg: #f8f7ff;
            --invoice-section-border: #ddd8f7;
            --invoice-label-color: #6b6798;
        }

        /* ===== TRANSITION ===== */
        body, .wrapper, .main-header, .main-sidebar, .content-wrapper,
        .card, .card-header, .card-footer, .card-body,
        .modal-content, .main-footer, .form-control, .custom-select,
        .table, .btn-secondary, .btn-default, .page-link,
        .alert, .nav-link, h1, h2, h3, h4, h5, h6, a, p, span, td, th, label,
        .text-muted, code, .input-group-text, .close {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }

        /* ===== BASE ===== */
        body, .wrapper { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important; }

        /* ===== NAVBAR ===== */
        .main-header.navbar {
            background: var(--bg-navbar) !important;
            border-bottom: 1px solid var(--border-color) !important;
        }
        .main-header .nav-link { color: var(--text-nav) !important; }
        .main-header .nav-link:hover { color: var(--text-nav-hover) !important; }
        .nav-date-badge {
            background: var(--bg-date-badge);
            color: var(--text-date-badge) !important;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
        }

        /* ===== THEME TOGGLE ===== */
        .theme-toggle-wrapper {
            display: flex;
            align-items: center;
            padding: 4px 12px;
        }
        .theme-toggle {
            position: relative;
            width: 56px;
            height: 28px;
            background: var(--bg-hover);
            border: 2px solid var(--border-color);
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 6px;
        }
        .theme-toggle:hover {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
        }
        .theme-toggle .toggle-icon {
            font-size: 12px;
            z-index: 1;
            transition: opacity 0.3s ease;
        }
        .theme-toggle .toggle-icon.sun { color: #f39c12; }
        .theme-toggle .toggle-icon.moon { color: #a4b4f4; }
        .theme-toggle .toggle-ball {
            position: absolute;
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        [data-theme="light"] .theme-toggle .toggle-ball {
            transform: translateX(28px);
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        [data-theme="light"] .theme-toggle {
            background: #f0eeff;
            border-color: #c9c3e8;
        }

        /* ===== SIDEBAR ===== */
        .main-sidebar {
            background: var(--bg-sidebar) !important;
        }
        .main-sidebar .brand-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border-bottom: 0 !important;
            padding: 16px !important;
            text-align: center;
        }
        .brand-link .brand-text { font-weight: 700 !important; letter-spacing: 0.3px; }
        .sidebar .user-panel {
            border-color: var(--sidebar-user-border) !important;
        }
        .sidebar .user-panel .info a { color: var(--text-primary) !important; font-weight: 600; }
        .user-avatar-circle {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 1rem;
        }
        .nav-sidebar > .nav-item > .nav-link {
            border-radius: 8px !important;
            margin: 2px 10px !important;
            color: var(--text-sidebar-nav) !important;
        }
        .nav-sidebar > .nav-item > .nav-link:hover {
            background: var(--bg-hover) !important;
            color: var(--text-primary) !important;
        }
        .nav-sidebar > .nav-item > .nav-link.active,
        .nav-sidebar > .nav-item > .nav-link.active:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: #fff !important;
        }
        .nav-header {
            color: var(--text-nav-header) !important;
            font-size: 0.65rem !important;
            letter-spacing: 1.5px !important;
            text-transform: uppercase !important;
            padding: 18px 22px 6px !important;
        }
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.btn-logout-link,
        .nav-sidebar > .nav-item > .nav-link.btn-logout-link {
            color: var(--logout-color) !important;
            background: var(--logout-bg) !important;
        }
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.btn-logout-link:hover,
        .nav-sidebar > .nav-item > .nav-link.btn-logout-link:hover {
            background: var(--logout-bg-hover) !important;
        }

        /* ===== CONTENT ===== */
        .content-wrapper {
            background: var(--bg-body) !important;
        }
        .content-header h1 {
            font-weight: 700 !important;
            font-size: 1.6rem !important;
        }

        /* ===== CARDS ===== */
        .card {
            background: var(--bg-card) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 12px !important;
        }
        .card-header {
            background: transparent !important;
            border-bottom: 1px solid var(--border-color) !important;
        }
        .card-footer {
            background: var(--bg-card-hover) !important;
            border-top: 1px solid var(--border-color) !important;
        }

        /* ===== TABLES ===== */
        .table { color: var(--text-secondary) !important; }
        .table thead th {
            background: var(--bg-table-header) !important;
            border-color: var(--border-color) !important;
            color: var(--text-table-header) !important;
            font-weight: 600 !important;
            font-size: 0.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }
        .table td, .table th { border-color: var(--border-color) !important; }
        .table-hover tbody tr:hover { background: var(--bg-card-hover) !important; }

        /* ===== FORMS ===== */
        .form-control, .custom-select {
            background: var(--bg-input) !important;
            border: 1px solid var(--border-input) !important;
            color: var(--text-primary) !important;
            border-radius: 8px !important;
        }
        .form-control:focus, .custom-select:focus {
            background: var(--bg-input-focus) !important;
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.15) !important;
            color: var(--text-primary) !important;
        }
        .form-control::placeholder { color: var(--text-placeholder) !important; }
        select.form-control option, .custom-select option { background: var(--select-option-bg); color: var(--select-option-color); }
        .input-group-text {
            background: var(--bg-input) !important;
            border-color: var(--border-input) !important;
            color: var(--text-input-group) !important;
        }

        /* ===== BUTTONS ===== */
        .btn-primary, .btn-primary:focus {
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
            border: 0 !important;
            border-radius: 8px !important;
        }
        .btn-primary:hover { opacity: 0.9; box-shadow: 0 4px 15px rgba(102,126,234,0.3) !important; }
        .btn-danger { background: linear-gradient(135deg, #ff7675, #d63031) !important; border: 0 !important; border-radius: 8px !important; }
        .btn-success { background: linear-gradient(135deg, #00b894, #00cec9) !important; border: 0 !important; border-radius: 8px !important; }
        .btn-secondary, .btn-default {
            background: var(--bg-btn-secondary) !important;
            border: 1px solid var(--border-btn-secondary) !important;
            color: var(--text-primary) !important;
            border-radius: 8px !important;
        }

        /* ===== MODAL ===== */
        .modal-content {
            background: var(--modal-bg) !important;
            border: 1px solid var(--modal-border) !important;
            border-radius: 14px !important;
        }
        .modal-header { border-bottom-color: var(--modal-header-border) !important; }
        .modal-footer { border-top-color: var(--modal-header-border) !important; }
        .close { color: var(--text-primary) !important; text-shadow: none !important; }

        /* ===== ALERTS ===== */
        .alert-success {
            background: var(--alert-success-bg) !important;
            color: var(--alert-success-color) !important;
            border: 1px solid var(--alert-success-border) !important;
        }
        .alert-danger {
            background: var(--alert-danger-bg) !important;
            color: var(--alert-danger-color) !important;
            border: 1px solid var(--alert-danger-border) !important;
        }
        .alert-warning {
            background: var(--alert-warning-bg) !important;
            color: var(--alert-warning-color) !important;
            border: 1px solid var(--alert-warning-border) !important;
        }
        .alert-info {
            background: var(--alert-info-bg) !important;
            color: var(--alert-info-color) !important;
            border: 1px solid var(--alert-info-border) !important;
        }

        /* ===== BADGES ===== */
        .badge-success { background: #00b894 !important; }
        .badge-danger { background: #d63031 !important; }
        .badge-warning { background: #f39c12 !important; color: #fff !important; }
        .badge-info { background: #0984e3 !important; }
        .badge-secondary { background: var(--badge-secondary-bg) !important; }

        /* ===== PAGINATION ===== */
        .page-link {
            background: var(--page-link-bg) !important;
            border-color: var(--page-link-border) !important;
            color: var(--page-link-color) !important;
        }
        .page-link:hover { background: var(--page-link-hover-bg) !important; color: var(--text-primary) !important; }
        .page-item.active .page-link { background: #667eea !important; border-color: #667eea !important; color: #fff !important; }

        /* ===== CUSTOM CONTROL ===== */
        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #667eea !important;
            border-color: #667eea !important;
        }
        [data-theme="light"] label {
            color: var(--text-secondary);
        }

        /* ===== FOOTER ===== */
        .main-footer {
            background: var(--bg-footer) !important;
            border-top: 1px solid var(--border-color) !important;
            color: var(--text-footer) !important;
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--scrollbar-thumb); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--scrollbar-thumb-hover); }

        /* ===== MISC ===== */
        .text-muted { color: var(--text-muted) !important; }
        h1, h2, h3, h4, h5, h6 { color: var(--text-primary); }
        a { color: var(--link-color); }
        a:hover { color: var(--link-hover); }
        code { background: var(--code-bg); color: var(--code-color); padding: 2px 6px; border-radius: 4px; }
        p, td, th, li, span, div { color: inherit; }

        /* ===== LIGHT MODE OVERRIDES FOR INLINE STYLES ===== */
        [data-theme="light"] .content-wrapper [style*="color:#fff"],
        [data-theme="light"] .content-wrapper [style*="color: #fff"] {
            color: var(--text-primary) !important;
        }
        [data-theme="light"] .content-wrapper [style*="color:rgba(255,255,255,"] {
            color: var(--text-muted) !important;
        }

        /* Light mode: fix inline color overrides on dashboard, transactions, etc. */
        [data-theme="light"] .card-body h4[style*="color:#fff"],
        [data-theme="light"] .card-body div[style*="color:#fff"],
        [data-theme="light"] .card-body .mb-0[style*="color:#fff"] {
            color: var(--text-primary) !important;
        }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.5)"],
        [data-theme="light"] [style*="color:rgba(255,255,255,0.45)"],
        [data-theme="light"] [style*="color:rgba(255,255,255,0.4)"],
        [data-theme="light"] [style*="color:rgba(255,255,255,0.35)"],
        [data-theme="light"] [style*="color:rgba(255,255,255,0.6)"],
        [data-theme="light"] [style*="color:rgba(255,255,255,0.7)"],
        [data-theme="light"] [style*="color:rgba(255,255,255,0.8)"] {
            color: var(--text-muted) !important;
        }

        /* Light mode: fix stat entry counts & descriptions in cards */
        [data-theme="light"] .card-body div[style*="font-size:1.6rem"][style*="color:#fff"] {
            color: var(--text-primary) !important;
        }
        [data-theme="light"] .card-body div[style*="color:rgba(255,255,255,0.5)"] {
            color: var(--text-muted) !important;
        }

        /* Light mode: module card descriptions */
        [data-theme="light"] .card-body h5[style*="color:#fff"] {
            color: var(--text-primary) !important;
        }
        [data-theme="light"] .card-body p[style*="color:rgba(255,255,255,"] {
            color: var(--text-muted) !important;
        }

        /* Light mode: quick action links */
        [data-theme="light"] a[style*="color:rgba(255,255,255,0.8)"] {
            color: var(--text-secondary) !important;
        }

        /* Light mode: transaction form section overrides */
        [data-theme="light"] .invoice-form-section {
            border-color: var(--invoice-section-border) !important;
            background: var(--invoice-section-bg) !important;
        }
        [data-theme="light"] .invoice-form-section label {
            color: var(--invoice-label-color) !important;
        }

        /* Light mode: total entries counter */
        [data-theme="light"] span[id$="-total-entries"][style*="color:#fff"] {
            color: var(--text-primary) !important;
        }

        /* Light mode: card shadow with violet tint */
        [data-theme="light"] .card {
            box-shadow: 0 1px 4px rgba(102,126,234,0.08), 0 1px 2px rgba(118,75,162,0.04);
        }

        /* Light mode: sidebar — deep violet look */
        [data-theme="light"] .main-sidebar {
            box-shadow: 2px 0 12px rgba(45,43,85,0.15);
        }
        [data-theme="light"] .sidebar .user-panel .info a {
            color: #fff !important;
        }
        [data-theme="light"] .sidebar .user-panel .info small {
            color: rgba(255,255,255,0.55) !important;
        }
        [data-theme="light"] .nav-sidebar > .nav-item > .nav-link:hover {
            background: rgba(255,255,255,0.1) !important;
            color: #fff !important;
        }

        /* Light mode: AdminLTE overrides */
        [data-theme="light"] .sidebar-dark-primary {
            background: var(--bg-sidebar) !important;
        }
        [data-theme="light"] .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link {
            color: var(--text-sidebar-nav) !important;
        }
        [data-theme="light"] .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link p {
            color: inherit !important;
        }
        [data-theme="light"] .nav-header {
            color: var(--text-nav-header) !important;
        }
        [data-theme="light"] .navbar-dark {
            background: var(--bg-navbar) !important;
        }

        /* Light mode: navbar white with violet accent shadow */
        [data-theme="light"] .main-header.navbar {
            box-shadow: 0 1px 4px rgba(102,126,234,0.1);
            border-bottom: 1px solid var(--border-color) !important;
        }

        /* Light mode: footer violet tint */
        [data-theme="light"] .main-footer {
            border-top: 2px solid rgba(102,126,234,0.15) !important;
        }

        /* Light mode: strong/bold text in tables */
        [data-theme="light"] .table strong,
        [data-theme="light"] .table td[style*="font-weight:600"],
        [data-theme="light"] .table td[style*="font-weight:700"] {
            color: var(--text-primary);
        }

        /* Light mode: table header with violet tint */
        [data-theme="light"] .table thead th {
            background: #f4f2ff !important;
            color: #5a4fcf !important;
            border-color: #ddd8f7 !important;
        }

        /* Light mode: dashed upload borders & subtle backgrounds */
        [data-theme="light"] [style*="border:2px dashed rgba(255,255,255"] {
            border-color: #c9c3e8 !important;
        }
        [data-theme="light"] [style*="background:rgba(255,255,255,0.02)"],
        [data-theme="light"] [style*="background:rgba(255,255,255,0.03)"],
        [data-theme="light"] [style*="background:rgba(255,255,255,0.04)"],
        [data-theme="light"] [style*="background: rgba(255,255,255,0.03)"],
        [data-theme="light"] [style*="background: rgba(255,255,255,0.04)"] {
            background: #f8f7ff !important;
        }

        /* Light mode: filter card override */
        [data-theme="light"] .card[style*="background: rgba(255,255,255,0.03)"] {
            background: var(--bg-card) !important;
        }

        /* Light mode: totals row background with violet tint */
        [data-theme="light"] tr[style*="background: rgba("] {
            background: #f0eeff !important;
        }

        /* Light mode: general text color for all body content */
        [data-theme="light"] .content-wrapper,
        [data-theme="light"] .content-wrapper p,
        [data-theme="light"] .content-wrapper span,
        [data-theme="light"] .content-wrapper div,
        [data-theme="light"] .content-wrapper td,
        [data-theme="light"] .content-wrapper th,
        [data-theme="light"] .content-wrapper li,
        [data-theme="light"] .content-wrapper label,
        [data-theme="light"] .content-wrapper small {
            color: var(--text-secondary);
        }

        /* Re-assert primary text for headings in light mode */
        [data-theme="light"] .content-wrapper h1,
        [data-theme="light"] .content-wrapper h2,
        [data-theme="light"] .content-wrapper h3,
        [data-theme="light"] .content-wrapper h4,
        [data-theme="light"] .content-wrapper h5,
        [data-theme="light"] .content-wrapper h6,
        [data-theme="light"] .content-wrapper strong {
            color: var(--text-primary);
        }

        /* Light mode: close button visibility */
        [data-theme="light"] .close {
            color: var(--text-primary) !important;
            opacity: 0.6;
        }

        /* Light mode: welcome banner & gradient cards keep white text */
        [data-theme="light"] .card[style*="background: linear-gradient"] *,
        [data-theme="light"] .card[style*="background:linear-gradient"] h4,
        [data-theme="light"] .card[style*="background:linear-gradient"] p,
        [data-theme="light"] .card-header[style*="background:linear-gradient"] *,
        [data-theme="light"] .form-title-bar[style*="background:linear-gradient"] * {
            color: #fff !important;
        }

        /* Light mode: form labels */
        [data-theme="light"] .form-group label,
        [data-theme="light"] .custom-control-label {
            color: var(--text-secondary) !important;
        }

        /* Light mode: tab buttons in gradient card */
        [data-theme="light"] .card[style*="linear-gradient(135deg, #1e3c72"] button[style*="background:#fff"] {
            background: #fff !important;
            color: #1e3c72 !important;
        }

        /* Light mode: buttons get violet accents */
        [data-theme="light"] .btn-secondary,
        [data-theme="light"] .btn-default {
            background: #f0eeff !important;
            border: 1px solid #c9c3e8 !important;
            color: #4a4678 !important;
        }
        [data-theme="light"] .btn-secondary:hover,
        [data-theme="light"] .btn-default:hover {
            background: #e4e0f5 !important;
            color: #1e1b4b !important;
        }

        /* Light mode: input focus ring violet */
        [data-theme="light"] .form-control:focus,
        [data-theme="light"] .custom-select:focus {
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.18) !important;
        }

        /* Light mode: pagination active violet */
        [data-theme="light"] .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
            border-color: #667eea !important;
        }

        /* Light mode: active nav link keeps gradient white text */
        [data-theme="light"] .nav-sidebar > .nav-item > .nav-link.active,
        [data-theme="light"] .nav-sidebar > .nav-item > .nav-link.active p,
        [data-theme="light"] .nav-sidebar > .nav-item > .nav-link.active i {
            color: #fff !important;
        }

        /* Light mode: custom switch track color violet */
        [data-theme="light"] .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #667eea !important;
            border-color: #667eea !important;
        }
        [data-theme="light"] .custom-control-label::before {
            background-color: #ddd8f7 !important;
            border-color: #c9c3e8 !important;
        }

        /* Light mode: colorful values adapted for light backgrounds */
        [data-theme="light"] [style*="color:#55efc4"] { color: #059669 !important; }
        [data-theme="light"] [style*="color:#a4b4f4"] { color: #4f46e5 !important; }
        [data-theme="light"] [style*="color:#fdcb6e"] { color: #b45309 !important; }
        [data-theme="light"] [style*="color:#fd79a8"] { color: #be185d !important; }
        [data-theme="light"] [style*="color:#ff7675"] { color: #dc2626 !important; }
        [data-theme="light"] [style*="color:#74b9ff"] { color: #2563eb !important; }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single { background: var(--bg-input)!important; border-color: var(--border-input)!important; color: var(--text-primary)!important; min-height: 31px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text-primary)!important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 29px; }
        .select2-dropdown { background: var(--bg-card)!important; border-color: var(--border-color)!important; }
        .select2-container--default .select2-search--dropdown .select2-search__field { background: var(--bg-input)!important; border-color: var(--border-input)!important; color: var(--text-primary)!important; }
        .select2-results__option { color: var(--text-primary)!important; }
        .select2-container--default .select2-results__option--highlighted[aria-selected] { background: #667eea!important; color: #fff!important; }
    </style>
    @stack('styles')
</head>
<body class="dark-mode layout-fixed layout-navbar-fixed sidebar-mini" style="height:auto;" data-theme="dark">
<div class="wrapper">

    {{-- Navbar --}}
    <nav class="main-header navbar navbar-expand navbar-dark">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-md-block">
                <span class="nav-link"><span class="text-muted">Welcome,</span> <strong style="color:var(--text-primary)">{{ auth()->user()->name ?? 'User' }}</strong></span>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            {{-- Theme Toggle --}}
            <li class="nav-item">
                <div class="theme-toggle-wrapper">
                    <div class="theme-toggle" id="themeToggle" title="Toggle Dark/Light Mode" role="button" tabindex="0" aria-label="Toggle dark and light mode">
                        <i class="fas fa-moon toggle-icon moon"></i>
                        <i class="fas fa-sun toggle-icon sun"></i>
                        <div class="toggle-ball"></div>
                    </div>
                </div>
            </li>
            <li class="nav-item">
                <span class="nav-link nav-date-badge"><i class="far fa-calendar-alt mr-1"></i> {{ now()->format('D, d M Y') }}</span>
            </li>
        </ul>
    </nav>

    {{-- Sidebar --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light"><i class="fas fa-bolt mr-2"></i>{{ config('app.name') }}</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <div class="user-avatar-circle">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ auth()->user()->name ?? 'User' }}</a>
                    <small class="text-muted">{{ auth()->user()->email ?? '' }}</small>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-th-large"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-header">Business</li>
                    <li class="nav-item">
                        <a href="{{ route('modules.master-setup') }}" class="nav-link {{ request()->routeIs('modules.master-setup') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-database"></i><p>Master Setup</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('modules.transactions') }}" class="nav-link {{ request()->routeIs('modules.transactions') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-receipt"></i><p>Transactions</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('modules.books-registers') }}" class="nav-link {{ request()->routeIs('modules.books-registers') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book-open"></i><p>Books & Registers</p>
                        </a>
                    </li>
                    <li class="nav-header">Finance</li>
                    <li class="nav-item">
                        <a href="{{ route('modules.accounting') }}" class="nav-link {{ request()->routeIs('modules.accounting*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-coins"></i><p>Accounting</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('modules.reports') }}" class="nav-link {{ request()->routeIs('modules.reports') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-pie"></i><p>Reports</p>
                        </a>
                    </li>
                    <li class="nav-header">Invoicing</li>
                    <li class="nav-item">
                        <a href="{{ route('invoices.templates') }}" class="nav-link {{ request()->routeIs('invoices.templates') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-palette"></i><p>Invoice Templates</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('invoices.create') }}" class="nav-link {{ request()->routeIs('invoices.create') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i><p>Create Invoice</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.index') || request()->routeIs('invoices.generate') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-alt"></i><p>My Invoices</p>
                        </a>
                    </li>
                    <li class="nav-header">AI Tools</li>
                    <li class="nav-item">
                        <a href="{{ route('ai-invoice.index') }}" class="nav-link {{ request()->routeIs('ai-invoice.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-magic"></i><p>AI Invoice Scan</p>
                        </a>
                    </li>
                    <li class="nav-header">Account</li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline w-100" data-offline-exclude>@csrf
                            <button type="submit" class="nav-link btn-logout-link border-0 w-100 text-left" style="cursor:pointer;">
                                <i class="nav-icon fas fa-sign-out-alt"></i><p>Sign out</p>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    {{-- Content --}}
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">@yield('header', 'Dashboard')</h1>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>{{ config('app.name') }}</strong> &mdash; Advanced Billing System
        <span class="float-right d-none d-sm-inline">Built with Laravel</span>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

{{-- Theme Toggle Script --}}
<script>
(function(){
    var toggle = document.getElementById('themeToggle');
    var html = document.documentElement;
    var body = document.body;
    var meta = document.getElementById('meta-theme-color');

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        body.setAttribute('data-theme', theme);
        localStorage.setItem('abs-theme', theme);
        if (meta) {
            meta.setAttribute('content', theme === 'dark' ? '#1a1d21' : '#ffffff');
        }
    }

    // Apply saved theme on load
    var saved = localStorage.getItem('abs-theme') || 'dark';
    applyTheme(saved);

    // Toggle click
    if (toggle) {
        toggle.addEventListener('click', function(){
            var current = html.getAttribute('data-theme') || 'dark';
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
        // Keyboard support
        toggle.addEventListener('keydown', function(e){
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggle.click();
            }
        });
    }
})();
</script>

{{-- PWA Register + Install Button --}}
<script>
(function () {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    var deferredPrompt = null;

    function isStandaloneMode() {
        return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    }

    function showInstallHelp() {
        window.alert('Install is not available right now. Open browser menu and choose "Install app" or "Add to Home screen".');
    }

    function updateInstallButtonState(button) {
        if (isStandaloneMode()) {
            button.style.display = 'none';
            return;
        }

        button.style.display = 'inline-block';
        button.innerText = deferredPrompt ? 'Install App' : 'Install Guide';
    }

    window.addEventListener('load', function () {
        navigator.serviceWorker.register("{{ asset('sw.js') }}", { updateViaCache: "none" }).catch(function (error) {
            console.warn('Service worker registration failed:', error);
        });

        setTimeout(function () {
            updateInstallButtonState(installButton);
        }, 1500);
    });

    function ensureInstallButton() {
        var existing = document.getElementById('pwaInstallButton');
        if (existing) {
            return existing;
        }

        var button = document.createElement('button');
        button.id = 'pwaInstallButton';
        button.type = 'button';
        button.innerText = 'Install App';
        button.style.position = 'fixed';
        button.style.right = '16px';
        button.style.bottom = '16px';
        button.style.zIndex = '1060';
        button.style.border = '0';
        button.style.padding = '10px 14px';
        button.style.borderRadius = '8px';
        button.style.fontWeight = '700';
        button.style.color = '#fff';
        button.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
        button.style.boxShadow = '0 8px 22px rgba(102,126,234,0.35)';
        button.style.display = 'inline-block';
        document.body.appendChild(button);
        return button;
    }

    var installButton = ensureInstallButton();
    updateInstallButtonState(installButton);

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredPrompt = event;
        updateInstallButtonState(installButton);
    });

    installButton.addEventListener('click', async function () {
        if (isStandaloneMode()) {
            return;
        }

        if (!deferredPrompt) {
            showInstallHelp();
            return;
        }

        deferredPrompt.prompt();
        try {
            await deferredPrompt.userChoice;
        } catch (error) {
            console.warn('Install prompt was dismissed:', error);
        }
        deferredPrompt = null;
        updateInstallButtonState(installButton);
    });

    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        updateInstallButtonState(installButton);
    });
})();
</script>

{{-- Offline Form Queue + Auto Sync --}}
<script>
(function () {
    var STORAGE_KEY = 'abs-offline-request-queue-v2';
    var statusPill = null;
    var syncInProgress = false;

    function readQueue() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            var queue = raw ? JSON.parse(raw) : [];
            return Array.isArray(queue) ? queue : [];
        } catch (error) {
            console.warn('Could not read offline queue:', error);
            return [];
        }
    }

    function writeQueue(queue) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(queue));
    }

    function queueCount() {
        return readQueue().length;
    }

    function notify(message, type) {
        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.toast) {
            if (type === 'error') {
                console.error(message);
            } else {
                console.log(message);
            }
            return;
        }

        var bg = '#17a2b8';
        if (type === 'success') bg = '#28a745';
        if (type === 'warning') bg = '#f39c12';
        if (type === 'error') bg = '#dc3545';

        window.jQuery(document).Toasts('create', {
            title: 'Offline Sync',
            body: message,
            autohide: true,
            delay: 3500,
            class: 'bg-dark',
            style: 'border-left:4px solid ' + bg + ';'
        });
    }

    function ensureStatusPill() {
        var existing = document.getElementById('offlineSyncStatus');
        if (existing) {
            return existing;
        }

        var pill = document.createElement('div');
        pill.id = 'offlineSyncStatus';
        pill.style.position = 'fixed';
        pill.style.left = '16px';
        pill.style.bottom = '16px';
        pill.style.zIndex = '1060';
        pill.style.padding = '8px 12px';
        pill.style.borderRadius = '8px';
        pill.style.fontSize = '12px';
        pill.style.fontWeight = '600';
        pill.style.boxShadow = '0 6px 18px rgba(0,0,0,0.2)';
        pill.style.backdropFilter = 'blur(4px)';
        document.body.appendChild(pill);
        return pill;
    }

    function updateStatusPill() {
        if (!statusPill) {
            statusPill = ensureStatusPill();
        }

        var count = queueCount();
        var online = navigator.onLine;

        if (!online) {
            statusPill.style.display = 'block';
            statusPill.style.background = 'rgba(243, 156, 18, 0.95)';
            statusPill.style.color = '#fff';
            statusPill.innerText = 'Offline mode' + (count ? ' • Pending: ' + count : '');
            return;
        }

        if (count > 0) {
            statusPill.style.display = 'block';
            statusPill.style.background = 'rgba(102, 126, 234, 0.95)';
            statusPill.style.color = '#fff';
            statusPill.innerText = syncInProgress ? 'Syncing offline data...' : 'Pending sync: ' + count;
            return;
        }

        statusPill.style.display = 'none';
    }

    function hasFileInput(formData) {
        var found = false;
        formData.forEach(function (value) {
            if (value instanceof File) {
                found = true;
            }
        });
        return found;
    }

    function sameOrigin(url) {
        try {
            return new URL(url, window.location.origin).origin === window.location.origin;
        } catch (error) {
            return false;
        }
    }

    function normalizeHeaders(inputHeaders) {
        var out = {};
        if (!inputHeaders) {
            return out;
        }

        if (typeof Headers !== 'undefined' && inputHeaders instanceof Headers) {
            inputHeaders.forEach(function (value, key) {
                out[key] = value;
            });
            return out;
        }

        if (Array.isArray(inputHeaders)) {
            inputHeaders.forEach(function (pair) {
                if (pair && pair.length === 2) {
                    out[pair[0]] = pair[1];
                }
            });
            return out;
        }

        if (typeof inputHeaders === 'object') {
            Object.keys(inputHeaders).forEach(function (key) {
                out[key] = inputHeaders[key];
            });
        }

        return out;
    }

    function serializeFormDataEntries(formData) {
        var entries = [];
        formData.forEach(function (value, key) {
            if (value instanceof File) {
                return;
            }
            entries.push([key, value]);
        });
        return entries;
    }

    function isExcludedPath(url) {
        var finalUrl = (url || '').toLowerCase();
        return finalUrl.includes('/login') || finalUrl.includes('/logout');
    }

    function isWriteMethod(method) {
        var m = (method || 'GET').toUpperCase();
        return m !== 'GET' && m !== 'HEAD' && m !== 'OPTIONS';
    }

    function enqueueRequest(payload) {
        var queue = readQueue();
        queue.push(payload);
        writeQueue(queue);
        updateStatusPill();
    }

    function requestShouldBeQueued(url, method) {
        if (!sameOrigin(url)) {
            return false;
        }
        if (!isWriteMethod(method)) {
            return false;
        }
        if (isExcludedPath(url)) {
            return false;
        }
        return true;
    }

    function formShouldBeQueued(form) {
        if (form.hasAttribute('data-offline-exclude')) {
            return false;
        }

        var action = form.getAttribute('action') || window.location.href;
        var method = (form.getAttribute('method') || 'GET').toUpperCase();
        if (!requestShouldBeQueued(action, method)) {
            return false;
        }

        return true;
    }

    function buildPayloadFromForm(form, source) {
        var action = form.getAttribute('action') || window.location.href;
        var method = (form.getAttribute('method') || 'POST').toUpperCase();
        var formData = new FormData(form);

        if (hasFileInput(formData)) {
            return { error: 'file_not_supported' };
        }

        var headers = {};
        var csrf = formData.get('_token') || '';
        if (csrf) {
            headers['X-CSRF-TOKEN'] = csrf;
        }

        return {
            source: source || 'form',
            action: action,
            method: method,
            bodyType: 'formData',
            body: serializeFormDataEntries(formData),
            headers: headers,
            queuedAt: new Date().toISOString()
        };
    }

    function buildFetchBody(payload) {
        if (payload.bodyType === 'formData') {
            var data = new FormData();
            (payload.body || []).forEach(function (pair) {
                data.append(pair[0], pair[1]);
            });
            return data;
        }
        if (payload.bodyType === 'text') {
            return payload.body || '';
        }
        return null;
    }

    async function flushQueue() {
        if (syncInProgress || !navigator.onLine) {
            return;
        }

        var queue = readQueue();
        if (!queue.length) {
            updateStatusPill();
            return;
        }

        syncInProgress = true;
        updateStatusPill();

        var processed = 0;
        while (queue.length && navigator.onLine) {
            var item = queue[0];
            try {
                var headers = normalizeHeaders(item.headers);
                headers['X-Offline-Sync'] = '1';
                if (!headers['X-Requested-With']) {
                    headers['X-Requested-With'] = 'XMLHttpRequest';
                }

                var response = await fetch(item.action, {
                    method: item.method || 'POST',
                    body: buildFetchBody(item),
                    credentials: 'same-origin',
                    headers: headers
                });

                if (!response.ok) {
                    // Keep remaining queue for retry later.
                    if (response.status === 401 || response.status === 419) {
                        notify('Session expired. Please login again, then sync will continue.', 'warning');
                    }
                    break;
                }

                queue.shift();
                processed += 1;
                writeQueue(queue);
            } catch (error) {
                // Network failure: stop now and retry when online event fires again.
                break;
            }
        }

        syncInProgress = false;
        updateStatusPill();

        if (processed > 0) {
            notify(processed + ' offline record(s) synced to server.', 'success');
        }
    }

    function buildOfflineSuccessResponse() {
        return new Response(JSON.stringify({
            offlineQueued: true,
            message: 'Saved offline. Will sync automatically when online.'
        }), {
            status: 202,
            headers: { 'Content-Type': 'application/json' }
        });
    }

    function queueFetchRequest(url, method, init) {
        var headers = normalizeHeaders(init && init.headers);
        var payload = {
            source: 'fetch',
            action: url,
            method: method,
            headers: headers,
            queuedAt: new Date().toISOString()
        };

        var body = init && init.body;
        if (typeof FormData !== 'undefined' && body instanceof FormData) {
            if (hasFileInput(body)) {
                return { error: 'file_not_supported' };
            }
            payload.bodyType = 'formData';
            payload.body = serializeFormDataEntries(body);
        } else if (typeof URLSearchParams !== 'undefined' && body instanceof URLSearchParams) {
            payload.bodyType = 'text';
            payload.body = body.toString();
            payload.headers['Content-Type'] = payload.headers['Content-Type'] || 'application/x-www-form-urlencoded; charset=UTF-8';
        } else if (typeof body === 'string') {
            payload.bodyType = 'text';
            payload.body = body;
        } else if (body == null) {
            payload.bodyType = 'text';
            payload.body = '';
        } else {
            return { error: 'unsupported_body' };
        }

        enqueueRequest(payload);
        return { ok: true };
    }

    function interceptFetchForOfflineQueue() {
        if (!window.fetch) {
            return;
        }

        var nativeFetch = window.fetch.bind(window);
        window.fetch = function (input, init) {
            var request = null;
            if (typeof Request !== 'undefined' && input instanceof Request) {
                request = input;
            }

            var url = request ? request.url : String(input || '');
            var method = ((init && init.method) || (request && request.method) || 'GET').toUpperCase();
            var headers = normalizeHeaders((init && init.headers) || (request && request.headers));

            // Never re-queue sync calls.
            if (String(headers['X-Offline-Sync'] || '').toLowerCase() === '1') {
                return nativeFetch(input, init);
            }

            if (!navigator.onLine && requestShouldBeQueued(url, method)) {
                var queued = queueFetchRequest(url, method, init || {});
                if (queued.error === 'file_not_supported') {
                    notify('Offline queue does not support file upload yet.', 'error');
                    return Promise.reject(new Error('Offline file upload not supported'));
                }
                if (queued.error === 'unsupported_body') {
                    notify('Could not queue this offline request type.', 'error');
                    return Promise.reject(new Error('Unsupported offline request body'));
                }

                notify('No internet. Request saved offline and queued for sync.', 'warning');
                return Promise.resolve(buildOfflineSuccessResponse());
            }

            return nativeFetch(input, init);
        };
    }

    function interceptJqueryAjaxForOfflineQueue() {
        if (!window.jQuery || !window.jQuery.ajax) {
            return;
        }

        var originalAjax = window.jQuery.ajax;
        window.jQuery.ajax = function (urlOrOptions, maybeOptions) {
            var opts = {};
            if (typeof urlOrOptions === 'string') {
                opts = maybeOptions || {};
                opts.url = urlOrOptions;
            } else {
                opts = urlOrOptions || {};
            }

            var method = (opts.type || opts.method || 'GET').toUpperCase();
            var url = opts.url || window.location.href;
            var headers = normalizeHeaders(opts.headers);

            if (String(headers['X-Offline-Sync'] || '').toLowerCase() === '1') {
                return originalAjax.apply(window.jQuery, arguments);
            }

            if (!navigator.onLine && requestShouldBeQueued(url, method)) {
                var payload = {
                    source: 'jquery-ajax',
                    action: url,
                    method: method,
                    headers: headers,
                    queuedAt: new Date().toISOString()
                };

                if (opts.data instanceof FormData) {
                    if (hasFileInput(opts.data)) {
                        notify('Offline queue does not support file upload yet.', 'error');
                        return originalAjax.apply(window.jQuery, arguments);
                    }
                    payload.bodyType = 'formData';
                    payload.body = serializeFormDataEntries(opts.data);
                } else if (typeof opts.data === 'string') {
                    payload.bodyType = 'text';
                    payload.body = opts.data;
                    payload.headers['Content-Type'] = payload.headers['Content-Type'] || 'application/x-www-form-urlencoded; charset=UTF-8';
                } else if (opts.data && typeof opts.data === 'object') {
                    payload.bodyType = 'text';
                    payload.body = window.jQuery.param(opts.data);
                    payload.headers['Content-Type'] = payload.headers['Content-Type'] || 'application/x-www-form-urlencoded; charset=UTF-8';
                } else {
                    payload.bodyType = 'text';
                    payload.body = '';
                }

                enqueueRequest(payload);
                notify('No internet. Request saved offline and queued for sync.', 'warning');

                var deferred = window.jQuery.Deferred();
                var mockResponse = { offlineQueued: true, message: 'Saved offline.' };
                if (typeof opts.success === 'function') {
                    opts.success(mockResponse, 'accepted', { status: 202 });
                }
                deferred.resolve(mockResponse, 'accepted', { status: 202 });
                return deferred.promise();
            }

            return originalAjax.apply(window.jQuery, arguments);
        };
    }

    function bindOfflineQueueToForms() {
        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }
            if (!formShouldBeQueued(form)) {
                return;
            }

            // If online, let the normal submit flow continue.
            if (navigator.onLine) {
                return;
            }

            event.preventDefault();
            var payload = buildPayloadFromForm(form, 'form-submit');

            if (payload.error === 'file_not_supported') {
                notify('This form has file upload. Offline save is not supported for files.', 'error');
                return;
            }

            enqueueRequest(payload);
            notify('No internet. Data saved offline and will sync automatically.', 'warning');
            form.reset();
        }, true);
    }

    window.addEventListener('online', function () {
        updateStatusPill();
        flushQueue();
    });
    window.addEventListener('offline', updateStatusPill);
    window.addEventListener('load', function () {
        updateStatusPill();
        interceptFetchForOfflineQueue();
        interceptJqueryAjaxForOfflineQueue();
        bindOfflineQueueToForms();
        flushQueue();
    });
})();
</script>

@stack('scripts')
</body>
</html>
