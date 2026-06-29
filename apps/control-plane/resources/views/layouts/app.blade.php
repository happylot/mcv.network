<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'MCV Ads Control Plane' }}</title>
    <link rel="icon" type="image/png" href="/logo_MCV_network.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/mcv.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=DM+Sans:wght@400;500;600;700&display=swap');

        :root {
            color-scheme: light;
            --mcv-navy: #204898;
            --mcv-teal: #38C0B8;
            --mcv-navy-dark: #0D1B2E;
            --mcv-blue-light: #2D6BC4;
            --mcv-teal-light: #6EE4DC;
            --text-primary: #1A2B4A;
            --text-secondary: #6B7C93;
            --text-muted: #8993A4;
            --bg-light: #F4F6F8;
            --bg-white: #FFFFFF;
            --border-light: #E8ECF0;
            --border: #DFE1E6;
            --gradient-primary: linear-gradient(135deg, #204898, #38C0B8);
            --gradient-dark: linear-gradient(135deg, #0D1B2E, #204898);
            --font-heading: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-body: 'DM Sans', -apple-system, sans-serif;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.06);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.08);
            --success: #1f9f7a;
            --warning: #b86a00;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background:
                linear-gradient(180deg, #f8fbff 0%, var(--bg-light) 360px),
                var(--bg-light);
            color: var(--text-primary);
            font-family: var(--font-body);
            line-height: 1.6;
        }

        a { color: var(--mcv-navy); text-decoration: none; }
        a:hover { color: var(--mcv-blue-light); }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255,255,255,0.95);
            border-bottom: 1px solid var(--border-light);
            backdrop-filter: blur(12px);
        }

        .topbar-inner,
        .page {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 64px;
            gap: 16px;
        }

        .brand {
            color: var(--mcv-navy);
            font-family: var(--font-heading);
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }

        .brand span {
            color: var(--mcv-teal);
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .nav a {
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 600;
        }

        .nav a:hover {
            color: var(--mcv-navy);
        }

        .nav form { margin: 0; }

        .page {
            padding: 44px 0 64px;
        }

        .auth-shell {
            width: min(460px, calc(100% - 32px));
            margin: 56px auto;
        }

        .auth-wrap {
            min-height: calc(100vh - 64px);
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            padding: 0;
        }

        .auth-side {
            background: var(--gradient-dark);
            color: #fff;
            padding: 64px 56px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .auth-side::after {
            content: '';
            position: absolute;
            top: -120px;
            right: -120px;
            width: 360px;
            height: 360px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56,192,184,0.18) 0%, transparent 70%);
        }

        .auth-side h2,
        .auth-side p,
        .auth-side .checklist {
            position: relative;
        }

        .auth-side h2 {
            color: #fff;
            font-family: var(--font-heading);
            font-size: 32px;
            font-weight: 800;
            line-height: 1.18;
            margin-bottom: 16px;
            max-width: 440px;
        }

        .auth-side p {
            color: rgba(255,255,255,0.78);
            font-size: 15px;
            max-width: 420px;
        }

        .auth-side .checklist {
            margin-top: 28px;
        }

        .auth-side .checklist li {
            color: rgba(255,255,255,0.9);
        }

        .auth-side .checklist li::before {
            color: var(--mcv-teal-light);
        }

        .auth-form {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
        }

        .auth-form-inner {
            width: 100%;
            max-width: 380px;
        }

        .auth-form h1 {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .auth-form .sub {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 28px;
        }

        .auth-form .button,
        .auth-form .btn {
            width: 100%;
            justify-content: center;
            margin-top: 6px;
        }

        .auth-alt {
            color: var(--text-secondary);
            font-size: 13px;
            margin-top: 20px;
            text-align: center;
        }

        .auth-divider {
            align-items: center;
            color: var(--text-muted);
            display: flex;
            font-size: 12px;
            font-weight: 700;
            gap: 12px;
            letter-spacing: 0.04em;
            margin: 22px 0;
            text-transform: uppercase;
        }

        .auth-divider::before,
        .auth-divider::after {
            background: var(--border-light);
            content: '';
            flex: 1;
            height: 1px;
        }

        .google-btn {
            width: 100%;
            background: #fff;
            border-color: var(--border);
            color: var(--text-primary);
            justify-content: center;
        }

        .google-btn:hover {
            border-color: var(--mcv-teal);
            box-shadow: 0 8px 24px rgba(56,192,184,0.12);
            color: var(--mcv-navy);
            transform: translateY(-1px);
        }

        .google-mark {
            align-items: center;
            border: 1px solid var(--border-light);
            border-radius: 50%;
            display: inline-flex;
            flex-shrink: 0;
            font-family: var(--font-heading);
            font-size: 13px;
            font-weight: 900;
            height: 22px;
            justify-content: center;
            width: 22px;
        }

        .checklist {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .checklist li {
            color: var(--text-primary);
            display: flex;
            gap: 10px;
            align-items: flex-start;
            font-size: 15px;
        }

        .checklist li::before {
            content: '\2713';
            color: var(--mcv-teal);
            flex-shrink: 0;
            font-weight: 900;
        }

        .form-field {
            margin-bottom: 18px;
        }

        .panel,
        .stat,
        .table-wrap {
            background: var(--bg-white);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
        }

        .panel { padding: 28px; }
        .stack { display: grid; gap: 18px; }
        .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .two-col { display: grid; grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr); gap: 16px; align-items: start; }

        h1, h2, h3, p { margin-top: 0; }
        h1, h2, h3 {
            color: var(--text-primary);
            font-family: var(--font-heading);
            letter-spacing: 0;
        }
        h1 { font-size: 34px; font-weight: 900; line-height: 1.15; margin-bottom: 8px; }
        h2 { font-size: 20px; font-weight: 800; margin-bottom: 14px; }
        h3 { font-size: 16px; font-weight: 800; margin-bottom: 8px; }
        p, .muted { color: var(--text-secondary); }

        .stat {
            padding: 22px;
            position: relative;
            overflow: hidden;
        }

        .stat::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 4px;
            background: var(--gradient-primary);
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .stat-value {
            display: block;
            color: var(--mcv-navy);
            font-family: var(--font-heading);
            font-size: 30px;
            font-weight: 900;
            line-height: 1.15;
            margin-top: 8px;
        }

        label {
            display: block;
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        input, select, textarea {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 12px 14px;
            font: inherit;
            background: #fff;
            color: var(--text-primary);
            transition: border 0.2s, box-shadow 0.2s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--mcv-teal);
            box-shadow: 0 0 0 3px rgba(56,192,184,0.15);
            outline: none;
        }

        textarea { min-height: 96px; resize: vertical; }

        .field { margin-bottom: 16px; }
        .error { color: #E53935; font-size: 14px; margin-top: 6px; }
        .flash {
            border: 1px solid rgba(56,192,184,0.28);
            background: rgba(56,192,184,0.1);
            color: var(--success);
            padding: 12px 16px;
            border-radius: var(--radius-lg);
            margin-bottom: 18px;
            font-weight: 700;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            border-radius: var(--radius-md);
            min-height: 42px;
            padding: 10px 18px;
            background: var(--mcv-navy);
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            border-radius: var(--radius-md);
            min-height: 42px;
            padding: 10px 18px;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--mcv-navy);
        }

        .btn-primary:hover {
            background: var(--mcv-blue-light);
            box-shadow: 0 8px 24px rgba(32,72,152,0.2);
            color: #fff;
            transform: translateY(-1px);
        }

        .button:hover {
            background: var(--mcv-blue-light);
            box-shadow: 0 8px 24px rgba(32,72,152,0.2);
            color: #fff;
            transform: translateY(-1px);
        }

        .auth-form .google-btn {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--text-primary);
            box-shadow: var(--shadow-sm);
        }

        .auth-form .google-btn:hover {
            background: #fff;
            border-color: var(--mcv-teal);
            box-shadow: 0 8px 24px rgba(56,192,184,0.12);
            color: var(--mcv-navy);
            transform: translateY(-1px);
        }

        .auth-form .google-mark {
            background: #fff;
            color: #4285f4;
        }

        .button.secondary {
            background: #fff;
            color: var(--mcv-navy);
            border-color: var(--mcv-navy);
        }

        .button.secondary:hover {
            background: var(--mcv-navy);
            color: #fff;
        }

        .button.link {
            background: transparent;
            border: 0;
            box-shadow: none;
            color: var(--mcv-navy);
            padding: 0;
            min-height: auto;
            transform: none;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 10px;
            background: var(--bg-light);
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .badge.pending { background: #fff4df; color: var(--warning); }
        .badge.posted, .badge.succeeded, .badge.active { background: rgba(56,192,184,0.1); color: var(--success); }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 13px 16px; text-align: left; border-bottom: 1px solid #f0f2f5; vertical-align: top; }
        th {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        tr:last-child td { border-bottom: 0; }

        .table-wrap {
            overflow-x: auto;
        }

        @media (max-width: 780px) {
            .grid, .two-col { grid-template-columns: 1fr; }
            .auth-wrap { grid-template-columns: 1fr; }
            .auth-side { display: none; }
            .topbar-inner { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            h1 { font-size: 26px; }
        }
    </style>
</head>
<body data-nav="">
    <div id="mcv-nav"></div>

    <main class="@yield('mainClass', 'page')">
        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>

    <div id="mcv-footer"></div>
    <script src="/assets/js/mcv.js"></script>
</body>
</html>
