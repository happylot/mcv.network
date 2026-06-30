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

        .portal-body {
            background:
                linear-gradient(180deg, #f8fbff 0%, var(--bg-light) 360px),
                var(--bg-light);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .portal-shell {
            display: grid;
            grid-template-columns: 228px minmax(0, 1fr);
            min-height: 100vh;
            padding-bottom: 52px;
        }

        .portal-sidebar {
            background: var(--mcv-navy-dark);
            border-right: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.68);
            min-height: 100vh;
            padding: 16px 16px 24px;
            position: sticky;
            top: 0;
        }

        .portal-logo {
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.16);
            color: #fff;
            display: flex;
            font-family: var(--font-heading);
            font-size: 22px;
            font-weight: 900;
            gap: 10px;
            height: 54px;
            letter-spacing: 0;
            margin-bottom: 10px;
        }

        .portal-logo-mark {
            align-items: center;
            background: var(--gradient-primary);
            border-radius: 8px;
            display: inline-flex;
            height: 30px;
            justify-content: center;
            width: 30px;
        }

        .portal-logo-mark i {
            color: #fff;
        }

        .portal-nav {
            display: grid;
            gap: 4px;
        }

        .portal-nav a,
        .portal-nav button {
            align-items: center;
            background: transparent;
            border: 0;
            border-bottom: 1px solid rgba(255,255,255,0.14);
            color: rgba(255,255,255,0.68);
            cursor: pointer;
            display: flex;
            font: inherit;
            font-size: 14px;
            font-weight: 800;
            gap: 10px;
            min-height: 36px;
            padding: 8px 0;
            text-align: left;
            width: 100%;
        }

        .portal-nav a:hover,
        .portal-nav a.active,
        .portal-nav button:hover {
            color: var(--mcv-teal-light);
        }

        .portal-nav i {
            color: currentColor;
            width: 16px;
        }

        .portal-main {
            min-width: 0;
        }

        .portal-topbar {
            align-items: center;
            background: var(--gradient-dark);
            color: #fff;
            display: flex;
            gap: 12px;
            height: 68px;
            justify-content: flex-end;
            padding: 0 16px;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .portal-icon-btn,
        .portal-avatar {
            align-items: center;
            border: 0;
            border-radius: 999px;
            display: inline-flex;
            height: 34px;
            justify-content: center;
            width: 34px;
        }

        .portal-icon-btn {
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.78);
        }

        .portal-avatar {
            background: #f2f6ff;
            color: var(--mcv-navy);
            font-weight: 900;
        }

        .top-wallet {
            align-items: center;
            border-radius: 5px;
            color: #fff;
            display: inline-flex;
            font-weight: 900;
            gap: 8px;
            height: 32px;
            padding: 0 12px;
        }

        .top-wallet.blue { background: var(--mcv-navy); }
        .top-wallet.sky { background: var(--mcv-blue-light); }
        .top-wallet.green { background: var(--mcv-teal); color: var(--mcv-navy-dark); }

        .top-add-funds {
            align-items: center;
            background: var(--mcv-teal);
            border-radius: 5px;
            color: var(--mcv-navy-dark);
            display: inline-flex;
            font-weight: 900;
            gap: 8px;
            height: 34px;
            padding: 0 16px;
        }

        .portal-content {
            padding: 18px 26px 32px;
        }

        .dashboard-crumb {
            border-bottom: 1px solid #dfe5ef;
            color: var(--mcv-navy);
            font-size: 16px;
            margin-bottom: 16px;
            padding: 0 0 14px;
        }

        .dash-stat-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            margin-bottom: 18px;
        }

        .dash-card {
            background: #fff;
            border: 1px solid var(--border-light);
            border-radius: 10px;
            box-shadow: var(--shadow-md);
            min-width: 0;
            min-height: 150px;
            padding: 20px;
        }

        .dash-stat-card {
            align-items: flex-start;
            display: grid;
            gap: 12px;
            grid-template-columns: 52px 1fr;
        }

        .stat-icon {
            align-items: center;
            border-radius: 14px;
            display: inline-flex;
            font-size: 24px;
            height: 48px;
            justify-content: center;
            width: 48px;
        }

        .stat-icon.blue,
        .stat-icon.green,
        .stat-icon.purple,
        .stat-icon.orange,
        .stat-icon.sky,
        .stat-icon.red {
            background: rgba(56,192,184,0.1);
            color: var(--mcv-teal);
        }

        .stat-icon.blue,
        .stat-icon.purple,
        .stat-icon.sky {
            background: rgba(32,72,152,0.08);
            color: var(--mcv-navy);
        }

        .dash-stat-label {
            color: var(--text-secondary);
            display: block;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.35;
        }

        .dash-stat-value {
            color: var(--text-primary);
            display: block;
            font-family: var(--font-heading);
            font-size: 22px;
            font-weight: 900;
            line-height: 1.2;
            margin-top: 6px;
        }

        .dash-card-link {
            color: var(--mcv-navy);
            font-size: 13px;
            font-weight: 900;
            grid-column: 1 / -1;
            margin-top: 2px;
        }

        .dashboard-grid {
            display: grid;
            gap: 30px;
            grid-template-columns: minmax(0, 1fr) 282px;
            min-width: 0;
        }

        .dashboard-grid > * {
            min-width: 0;
        }

        .dash-panel-header {
            align-items: center;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            padding-bottom: 16px;
        }

        .dash-panel-header h2 {
            font-size: 18px;
            margin: 0;
        }

        .dash-panel-header a {
            color: var(--mcv-navy);
            font-size: 13px;
            font-weight: 900;
        }

        .website-table {
            min-width: 850px;
        }

        .website-table th {
            color: var(--text-secondary);
            font-size: 12px;
            letter-spacing: 0;
            text-transform: none;
        }

        .website-table td {
            color: var(--text-secondary);
            font-size: 14px;
            padding: 12px 20px;
            vertical-align: middle;
        }

        .website-name {
            align-items: center;
            color: var(--text-primary);
            display: flex;
            font-weight: 800;
            gap: 8px;
            min-width: 220px;
        }

        .domain-dot {
            align-items: center;
            border-radius: 50%;
            display: inline-flex;
            font-size: 11px;
            height: 18px;
            justify-content: center;
            width: 18px;
        }

        .domain-dot.gold,
        .domain-dot.gray,
        .domain-dot.red,
        .domain-dot.blue { background: rgba(56,192,184,0.12); color: var(--mcv-teal); }

        .score-pill {
            border-radius: 5px;
            color: #fff;
            display: inline-flex;
            font-size: 11px;
            font-weight: 900;
            justify-content: center;
            min-width: 27px;
            padding: 2px 7px;
        }

        .score-pill.green { background: var(--mcv-teal); }
        .score-pill.blue { background: var(--mcv-navy); }

        .flag-us {
            border-radius: 3px;
            box-shadow: 0 6px 10px rgba(0,0,0,0.14);
            display: inline-block;
            font-size: 24px;
            line-height: 1;
        }

        .price {
            color: var(--mcv-navy);
            font-weight: 900;
        }

        .buy-btn {
            background: var(--mcv-navy);
            border-radius: 4px;
            color: #fff;
            display: inline-flex;
            font-weight: 800;
            justify-content: center;
            line-height: 1.2;
            min-width: 82px;
            padding: 8px 12px;
            text-align: center;
        }

        .buy-btn:hover {
            color: #fff;
            background: var(--mcv-blue-light);
        }

        .right-rail {
            display: grid;
            gap: 30px;
            min-width: 0;
        }

        .wallet-card {
            background: var(--gradient-primary);
            border: 0;
            color: #fff;
            min-height: 150px;
            overflow: hidden;
            position: relative;
        }

        .wallet-card::after {
            background: rgba(255,255,255,0.07);
            border-radius: 44px;
            bottom: -42px;
            content: '';
            height: 108px;
            position: absolute;
            right: -28px;
            transform: rotate(-18deg);
            width: 170px;
        }

        .wallet-card h3 {
            color: #fff;
            font-size: 13px;
            margin-bottom: 4px;
            position: relative;
            z-index: 1;
        }

        .wallet-card strong {
            display: block;
            font-size: 30px;
            font-weight: 500;
            line-height: 1.15;
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
        }

        .wallet-mini-icon {
            background: #fff;
            border-radius: 5px;
            color: var(--mcv-navy);
            font-size: 22px;
            padding: 6px 8px;
            position: absolute;
            right: 24px;
            top: 26px;
        }

        .wallet-card .button {
            background: rgba(255,255,255,0.92);
            color: var(--mcv-navy);
            min-height: 30px;
            padding: 5px 12px;
            position: relative;
            z-index: 1;
        }

        .activity-card {
            background: #fff;
            border: 1px solid var(--border-light);
            border-radius: 5px;
            overflow: hidden;
        }

        .activity-head {
            align-items: center;
            background: var(--mcv-navy-dark);
            color: #fff;
            display: flex;
            justify-content: space-between;
            padding: 16px 20px;
        }

        .activity-head h2 {
            color: #fff;
            font-size: 17px;
            margin: 0;
        }

        .activity-head a {
            color: #fff;
            font-size: 13px;
            font-weight: 800;
        }

        .activity-list {
            display: grid;
            gap: 10px;
            padding: 12px;
        }

        .activity-item {
            align-items: flex-start;
            background: rgba(32,72,152,0.06);
            border-radius: 5px;
            box-shadow: inset 0 0 0 1px rgba(32,72,152,0.05);
            display: grid;
            gap: 10px;
            grid-template-columns: 26px 1fr;
            padding: 16px;
        }

        .activity-item i {
            color: var(--mcv-teal);
            font-size: 20px;
            margin-top: 2px;
        }

        .activity-item p {
            color: var(--text-primary);
            font-size: 14px;
            line-height: 1.35;
            margin: 0;
        }

        .activity-time {
            color: var(--text-muted);
            display: block;
            font-size: 13px;
            margin-top: 8px;
            text-align: right;
        }

        .bonus-bar {
            align-items: center;
            background: var(--mcv-navy-dark);
            bottom: 0;
            color: #fff;
            display: flex;
            font-size: 16px;
            gap: 10px;
            justify-content: center;
            left: 0;
            min-height: 52px;
            padding: 8px 24px;
            position: fixed;
            right: 0;
            z-index: 40;
        }

        .bonus-bar strong,
        .bonus-bar .timer {
            color: var(--mcv-teal-light);
        }

        .bonus-button {
            background: var(--mcv-teal);
            border-radius: 4px;
            color: var(--mcv-navy-dark);
            font-size: 14px;
            font-weight: 900;
            padding: 7px 20px;
        }

        .chat-fab {
            align-items: center;
            background: var(--gradient-primary);
            border-radius: 50%;
            bottom: 28px;
            box-shadow: 0 12px 28px rgba(32,72,152,0.25);
            color: #fff;
            display: inline-flex;
            height: 52px;
            justify-content: center;
            position: fixed;
            right: 22px;
            width: 52px;
            z-index: 45;
        }

        @media (max-width: 780px) {
            .grid, .two-col { grid-template-columns: 1fr; }
            .auth-wrap { grid-template-columns: 1fr; }
            .auth-side { display: none; }
            .topbar-inner { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            h1 { font-size: 26px; }
        }

        @media (max-width: 1280px) {
            .dashboard-grid { grid-template-columns: minmax(0, 1fr); }
            .right-rail { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 860px) {
            .portal-shell { grid-template-columns: 1fr; }
            .portal-sidebar {
                min-height: auto;
                position: static;
            }
            .portal-nav { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .portal-topbar {
                height: auto;
                justify-content: flex-start;
                overflow-x: auto;
                padding: 12px;
            }
            .portal-content { padding: 16px; }
            .dash-stat-grid,
            .right-rail { grid-template-columns: 1fr; }
            .bonus-bar {
                align-items: flex-start;
                flex-direction: column;
                font-size: 14px;
                padding-right: 84px;
            }
        }
    </style>
</head>
@auth
<body class="portal-body">
    <div class="portal-shell">
        <aside class="portal-sidebar">
            <a class="portal-logo" href="{{ route('dashboard') }}">
                <span class="portal-logo-mark"><i class="fa-solid fa-chart-simple"></i></span>
                <span>MCV Ads</span>
            </a>
            <nav class="portal-nav" aria-label="Portal navigation">
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                <a href="#"><i class="fa-solid fa-folder-plus"></i> All My Projects</a>
                <a href="#"><i class="fa-solid fa-folder-open"></i> {{ auth()->user()->currentAccount()?->name ?? 'MCV Network' }}</a>
                <a href="#"><i class="fa-solid fa-bullhorn"></i> Campaigns</a>
                <a href="#"><i class="fa-solid fa-handshake-angle"></i> Affiliate Program</a>
                <a class="{{ request()->routeIs('billing.*') ? 'active' : '' }}" href="{{ route('billing.index') }}"><i class="fa-solid fa-coins"></i> Add Funds</a>
                <a href="#"><i class="fa-solid fa-circle-question"></i> FAQ</a>
                <a href="#"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"><i class="fa-solid fa-right-from-bracket"></i> Log out</button>
                </form>
            </nav>
        </aside>

        <div class="portal-main">
            <header class="portal-topbar">
                <span class="portal-icon-btn" title="Theme"><i class="fa-solid fa-moon"></i></span>
                <span class="portal-icon-btn" title="Messages"><i class="fa-solid fa-envelope"></i></span>
                <a class="top-add-funds" href="{{ route('billing.index') }}"><i class="fa-solid fa-plus"></i> Add Funds</a>
                <span class="top-wallet blue">{{ auth()->user()->currentAccount()?->wallet?->formattedBalance() ?? '$0.00' }} <i class="fa-solid fa-wallet"></i></span>
                <span class="top-wallet sky">${{ number_format((auth()->user()->currentAccount()?->wallet?->pending_balance_cents ?? 0) / 100, 2) }} <i class="fa-solid fa-file-invoice-dollar"></i></span>
                <span class="top-wallet green">$0.00 <i class="fa-solid fa-coins"></i></span>
                <span class="portal-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </header>

            <main class="portal-content @yield('mainClass', '')">
                @if (session('status'))
                    <div class="flash">{{ session('status') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    <div class="bonus-bar">
        <span>🎁 Get <strong>100% Bonus Credit</strong> (Only On Your Next Deposit)</span>
        <span>⏰ Expires in <span class="timer">05 h : 23 m : 33 s</span></span>
        <span aria-hidden="true">→</span>
        <a class="bonus-button" href="{{ route('billing.index') }}">Claim Your 100% Bonus</a>
    </div>
    <a class="chat-fab" href="#" aria-label="Open chat"><i class="fa-solid fa-comment"></i></a>
</body>
@else
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
@endauth
</html>
