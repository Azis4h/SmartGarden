<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SmartGarden - Dashboard IoT monitoring dan kontrol tanaman cerdas berbasis ESP32">
    <title>SmartGarden — Dashboard IoT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* =========================================
           DESIGN TOKENS
        ========================================= */
        :root {
            --bg-base:       #050d0a;
            --bg-card:       rgba(10, 25, 18, 0.75);
            --bg-card-hover: rgba(14, 35, 24, 0.90);
            --border:        rgba(34, 197, 94, 0.12);
            --border-glow:   rgba(34, 197, 94, 0.35);

            --green-400:  #4ade80;
            --green-500:  #22c55e;
            --green-600:  #16a34a;
            --green-dim:  rgba(34, 197, 94, 0.08);
            --green-glow: rgba(34, 197, 94, 0.20);

            --blue-400:   #60a5fa;
            --blue-500:   #3b82f6;
            --blue-dim:   rgba(59, 130, 246, 0.10);

            --amber-400:  #fbbf24;
            --amber-dim:  rgba(251, 191, 36, 0.10);

            --red-400:    #f87171;
            --red-dim:    rgba(248, 113, 113, 0.10);

            --text-primary:   #f0fdf4;
            --text-secondary: #86efac;
            --text-muted:     #4ade8088;

            --radius-sm:  8px;
            --radius-md:  14px;
            --radius-lg:  20px;
            --radius-xl:  28px;

            --shadow-card: 0 4px 32px rgba(0,0,0,0.6), 0 1px 0 rgba(255,255,255,0.04) inset;
            --shadow-glow: 0 0 40px rgba(34,197,94,0.12);
        }

        /* =========================================
           RESET & BASE
        ========================================= */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(34,197,94,0.08) 0%, transparent 70%),
                radial-gradient(ellipse 60% 40% at 80% 100%, rgba(59,130,246,0.05) 0%, transparent 60%);
        }

        /* =========================================
           NOISE TEXTURE OVERLAY
        ========================================= */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
            opacity: 0.4;
        }

        /* =========================================
           LAYOUT
        ========================================= */
        .app-wrapper {
            position: relative;
            z-index: 1;
            max-width: 1280px;
            margin: 0 auto;
            padding: 20px 16px 48px;
        }

        /* =========================================
           HEADER
        ========================================= */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--green-500), var(--green-600));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 4px 16px rgba(34,197,94,0.35);
            flex-shrink: 0;
        }

        .brand-text h1 {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff, var(--green-400));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-text p {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* =========================================
           STATUS BADGE
        ========================================= */
        .status-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid var(--border);
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            transition: all 0.3s ease;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #374151;
            transition: background 0.3s ease;
        }

        .status-badge.online .status-dot {
            background: var(--green-400);
            box-shadow: 0 0 8px var(--green-400);
            animation: pulse-dot 2s infinite;
        }

        .status-badge.offline .status-dot {
            background: var(--red-400);
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.6; transform: scale(1.3); }
        }

        .status-label { color: var(--text-secondary); }

        .status-badge.online .status-label { color: var(--green-400); }
        .status-badge.offline .status-label { color: var(--red-400); }

        /* =========================================
           LAST UPDATE BAR
        ========================================= */
        .last-update-bar {
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .refresh-spinner {
            width: 10px;
            height: 10px;
            border: 1.5px solid var(--text-muted);
            border-top-color: var(--green-400);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: none;
        }

        .refresh-spinner.active { display: block; }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* =========================================
           GRID LAYOUTS
        ========================================= */
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .grid-3-1 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        @media (max-width: 900px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-3-1 { grid-template-columns: 1fr; }
        }

        @media (max-width: 540px) {
            .grid-4 { grid-template-columns: 1fr 1fr; }
            .grid-2 { grid-template-columns: 1fr; }
        }

        /* =========================================
           CARDS
        ========================================= */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(16px);
            transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.06), transparent);
        }

        .card:hover {
            border-color: var(--border-glow);
            box-shadow: var(--shadow-card), var(--shadow-glow);
            transform: translateY(-2px);
        }

        /* =========================================
           METRIC CARDS
        ========================================= */
        .metric-card { padding: 18px; }

        .metric-card .card-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .metric-card .card-icon {
            font-size: 14px;
        }

        .metric-card .metric-value {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1;
            margin-bottom: 4px;
        }

        .metric-card .metric-unit {
            font-size: 1rem;
            font-weight: 400;
            opacity: 0.6;
        }

        .metric-card .metric-sub {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 8px;
        }

        /* =========================================
           SOIL MOISTURE GAUGE
        ========================================= */
        .moisture-card .gauge-wrap {
            margin: 12px 0 8px;
        }

        .moisture-bar {
            height: 8px;
            background: rgba(255,255,255,0.06);
            border-radius: 100px;
            overflow: hidden;
        }

        .moisture-fill {
            height: 100%;
            border-radius: 100px;
            transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(90deg, var(--green-600), var(--green-400));
            position: relative;
        }

        .moisture-fill::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 20px; height: 100%;
            background: rgba(255,255,255,0.35);
            border-radius: 100px;
            filter: blur(4px);
        }

        .moisture-fill.low {
            background: linear-gradient(90deg, #b91c1c, var(--red-400));
        }

        .moisture-fill.medium {
            background: linear-gradient(90deg, #d97706, var(--amber-400));
        }

        /* =========================================
           RAIN STATUS CARD
        ========================================= */
        .rain-card .rain-icon-wrap {
            font-size: 2.4rem;
            margin: 8px 0;
            transition: all 0.4s ease;
        }

        .rain-card .rain-icon-wrap.raining {
            animation: rain-sway 2s ease-in-out infinite;
        }

        @keyframes rain-sway {
            0%, 100% { transform: rotate(-5deg); }
            50%       { transform: rotate(5deg); }
        }

        /* =========================================
           PUMP STATUS CARD
        ========================================= */
        .pump-status-value {
            font-size: 1.4rem;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 100px;
            display: inline-block;
            margin: 8px 0;
        }

        .pump-on {
            background: rgba(34,197,94,0.15);
            color: var(--green-400);
            border: 1px solid rgba(34,197,94,0.3);
            animation: pump-glow 2s ease-in-out infinite;
        }

        @keyframes pump-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.4); }
            50%       { box-shadow: 0 0 0 8px rgba(34,197,94,0); }
        }

        .pump-off {
            background: rgba(248,113,113,0.1);
            color: var(--red-400);
            border: 1px solid rgba(248,113,113,0.2);
        }

        /* =========================================
           CLOCK CARD
        ========================================= */
        .clock-card .clock-time {
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: var(--blue-400);
            margin: 6px 0 4px;
            font-variant-numeric: tabular-nums;
        }

        .clock-card .clock-date {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* =========================================
           PUMP CONTROL CARD
        ========================================= */
        .control-card { padding: 24px; }

        .control-card h2 {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .control-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-control {
            flex: 1;
            min-width: 90px;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            border: 1.5px solid transparent;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.3px;
        }

        .btn-control:active { transform: scale(0.96); }

        .btn-on {
            background: linear-gradient(135deg, var(--green-600), var(--green-500));
            color: white;
            border-color: var(--green-500);
            box-shadow: 0 4px 16px rgba(34,197,94,0.3);
        }

        .btn-on:hover {
            background: linear-gradient(135deg, var(--green-500), var(--green-400));
            box-shadow: 0 6px 24px rgba(34,197,94,0.45);
            transform: translateY(-1px);
        }

        .btn-off {
            background: rgba(248,113,113,0.12);
            color: var(--red-400);
            border-color: rgba(248,113,113,0.3);
        }

        .btn-off:hover {
            background: rgba(248,113,113,0.22);
            border-color: rgba(248,113,113,0.5);
            transform: translateY(-1px);
        }

        .btn-auto {
            background: rgba(96,165,250,0.1);
            color: var(--blue-400);
            border-color: rgba(96,165,250,0.25);
        }

        .btn-auto:hover {
            background: rgba(96,165,250,0.2);
            border-color: rgba(96,165,250,0.4);
            transform: translateY(-1px);
        }

        .btn-control.active-cmd {
            outline: 2px solid rgba(255,255,255,0.3);
            outline-offset: 2px;
        }

        .cmd-status {
            margin-top: 14px;
            font-size: 0.75rem;
            color: var(--text-muted);
            min-height: 18px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cmd-status span {
            color: var(--green-400);
        }

        /* =========================================
           CHART SECTION
        ========================================= */
        .chart-card { padding: 24px; }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .chart-title {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-legend {
            display: flex;
            gap: 16px;
            font-size: 0.72rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--text-muted);
        }

        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .chart-container {
            position: relative;
            height: 200px;
        }

        /* =========================================
           HISTORY TABLE
        ========================================= */
        .table-card { padding: 24px; }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .table-title {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-count {
            font-size: 0.72rem;
            color: var(--text-muted);
            background: rgba(255,255,255,0.05);
            padding: 3px 10px;
            border-radius: 100px;
            border: 1px solid var(--border);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }

        .data-table th {
            text-align: left;
            padding: 8px 10px;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            font-weight: 600;
        }

        .data-table td {
            padding: 10px 10px;
            border-bottom: 1px solid rgba(34,197,94,0.05);
            color: var(--text-primary);
            vertical-align: middle;
        }

        .data-table tr:last-child td { border-bottom: none; }

        .data-table tr:hover td {
            background: rgba(34,197,94,0.03);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 100px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-green {
            background: rgba(34,197,94,0.12);
            color: var(--green-400);
            border: 1px solid rgba(34,197,94,0.2);
        }

        .badge-red {
            background: rgba(248,113,113,0.1);
            color: var(--red-400);
            border: 1px solid rgba(248,113,113,0.2);
        }

        .badge-blue {
            background: rgba(96,165,250,0.1);
            color: var(--blue-400);
            border: 1px solid rgba(96,165,250,0.2);
        }

        .badge-amber {
            background: rgba(251,191,36,0.1);
            color: var(--amber-400);
            border: 1px solid rgba(251,191,36,0.2);
        }

        /* =========================================
           EMPTY STATE
        ========================================= */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .empty-state .empty-icon { font-size: 2.5rem; margin-bottom: 12px; }
        .empty-state p { font-size: 0.85rem; }

        /* =========================================
           TOAST NOTIFICATION
        ========================================= */
        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 16px;
            z-index: 100;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
        }

        .toast {
            padding: 12px 18px;
            border-radius: var(--radius-md);
            font-size: 0.82rem;
            font-weight: 500;
            background: rgba(10, 25, 18, 0.95);
            border: 1px solid var(--border-glow);
            backdrop-filter: blur(16px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
            animation: toast-in 0.3s ease;
            pointer-events: auto;
            max-width: 300px;
        }

        .toast.success { border-color: rgba(34,197,94,0.4); }
        .toast.error   { border-color: rgba(248,113,113,0.4); }

        @keyframes toast-in {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* =========================================
           SECTION DIVIDER
        ========================================= */
        .section-label {
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* =========================================
           MOBILE TWEAKS
        ========================================= */
        @media (max-width: 480px) {
            .app-wrapper { padding: 12px 12px 40px; }
            .header { margin-bottom: 18px; }
            .brand-text h1 { font-size: 1.1rem; }
            .metric-card .metric-value { font-size: 1.8rem; }
            .clock-card .clock-time { font-size: 1.5rem; }
            .data-table th:nth-child(3),
            .data-table td:nth-child(3) { display: none; }
        }

        /* =========================================
           SCROLLBAR
        ========================================= */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb {
            background: rgba(34,197,94,0.2);
            border-radius: 100px;
        }
        ::-webkit-scrollbar-thumb:hover { background: rgba(34,197,94,0.4); }
    </style>
</head>
<body>
<div class="app-wrapper">

    <!-- ============ HEADER ============ -->
    <header class="header" role="banner">
        <div class="header-brand">
            <div class="brand-icon">🌿</div>
            <div class="brand-text">
                <h1>SmartGarden</h1>
                <p>IoT Dashboard · ESP32</p>
            </div>
        </div>

        <div id="statusBadge" class="status-badge offline" role="status" aria-live="polite">
            <div class="status-dot" id="statusDot"></div>
            <span class="status-label" id="statusLabel">Menghubungkan…</span>
        </div>
    </header>

    <!-- ============ LAST UPDATE ============ -->
    <div class="last-update-bar" aria-live="polite">
        <div class="refresh-spinner" id="refreshSpinner"></div>
        <span id="lastUpdateText">Memuat data…</span>
    </div>

    <!-- ============ METRIC CARDS ============ -->
    <div class="section-label">Sensor Realtime</div>
    <div class="grid-4" role="region" aria-label="Data sensor realtime">

        <!-- Soil Moisture -->
        <div class="card metric-card moisture-card" id="cardMoisture">
            <div class="card-label"><span class="card-icon">🌱</span> Kelembapan Tanah</div>
            <div class="metric-value" id="soilValue" style="color: var(--green-400)">—<span class="metric-unit">%</span></div>
            <div class="gauge-wrap">
                <div class="moisture-bar">
                    <div class="moisture-fill" id="moistureFill" style="width:0%"></div>
                </div>
            </div>
            <div class="metric-sub" id="soilAdcText">ADC: —</div>
        </div>

        <!-- Rain Status -->
        <div class="card metric-card rain-card" id="cardRain">
            <div class="card-label"><span class="card-icon">🌦️</span> Status Hujan</div>
            <div class="rain-icon-wrap" id="rainIcon">⛅</div>
            <div class="metric-value" id="rainValue" style="font-size:1.2rem; color: var(--blue-400)">—</div>
        </div>

        <!-- Pump Status -->
        <div class="card metric-card" id="cardPump">
            <div class="card-label"><span class="card-icon">💧</span> Status Pompa</div>
            <div style="margin: 10px 0;">
                <span class="pump-status-value pump-off" id="pumpBadge">—</span>
            </div>
            <div class="metric-sub" id="pumpModeText">Mode: —</div>
        </div>

        <!-- Clock -->
        <div class="card metric-card clock-card" id="cardClock">
            <div class="card-label"><span class="card-icon">🕐</span> Waktu RTC</div>
            <div class="clock-time" id="rtcTime">--:--:--</div>
            <div class="clock-date" id="rtcDate">—</div>
        </div>

    </div>

    <!-- ============ CHART + CONTROL ============ -->
    <div class="grid-3-1">

        <!-- Chart -->
        <div class="card chart-card">
            <div class="chart-header">
                <div class="chart-title">Grafik Kelembapan</div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-dot" style="background:var(--green-400)"></div>
                        <span>Kelembapan (%)</span>
                    </div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="soilChart" aria-label="Grafik kelembapan tanah" role="img"></canvas>
            </div>
        </div>

        <!-- Pump Control -->
        <div class="card control-card">
            <h2>Kontrol Pompa</h2>
            <div class="control-buttons">
                <button id="btnManual" class="btn-control btn-off" onclick="toggleManualPump()" aria-label="Kontrol Manual Pompa">
                    MANUAL
                </button>
                <button id="btnAuto" class="btn-control btn-auto" onclick="sendPumpCommand('auto')" aria-label="Mode otomatis pompa">
                    AUTO
                </button>
            </div>
            <div class="cmd-status" id="cmdStatus">
                Pilih mode kontrol pompa
            </div>
        </div>

    </div>

    <!-- ============ HISTORY TABLE ============ -->
    <div class="card table-card">
        <div class="table-header">
            <div class="table-title">Riwayat Sensor</div>
            <div style="display:flex; gap:12px; align-items:center;">
                <span class="table-count" id="historyCount">0 data</span>
                <a href="{{ route('history') }}" class="btn-control btn-auto" style="padding:6px 14px; min-width:auto; text-decoration:none; font-size:0.75rem;">Lihat Semua Riwayat ➔</a>
            </div>
        </div>

        <div id="tableWrapper">
            <div class="empty-state" id="tableEmpty">
                <div class="empty-icon">📡</div>
                <p>Menunggu data dari ESP32…</p>
            </div>
            <div id="tableContainer" style="display:none; overflow-x:auto;">
                <table class="data-table" aria-label="Riwayat data sensor">
                    <thead>
                        <tr>
                            <th>Waktu RTC</th>
                            <th>Kelembapan</th>
                            <th>ADC</th>
                            <th>Hujan</th>
                            <th>Pompa</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody"></tbody>
                </table>
            </div>
        </div>
    </div>

</div><!-- /app-wrapper -->

<!-- ============ TOAST ============ -->
<div class="toast-container" id="toastContainer" aria-live="polite"></div>

<script>
// ===================================================
// SmartGarden Dashboard — Main Script
// ===================================================

const API_BASE  = '/api';
const POLL_MS   = 3000; // refresh setiap 3 detik
const CHART_MAX = 30;   // maksimum titik di grafik

let soilChart      = null;
let lastUpdateTime = null;
let activeCmd      = null;
let isSendingCmd   = false;
let currentPumpStatus = false;

// ---------------------------------------------------
// Chart Initialization
// ---------------------------------------------------
function initChart() {
    const ctx = document.getElementById('soilChart').getContext('2d');
    soilChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Kelembapan (%)',
                data: [],
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34,197,94,0.08)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#22c55e',
                pointBorderColor: 'transparent',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 400 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(10,25,18,0.95)',
                    borderColor: 'rgba(34,197,94,0.3)',
                    borderWidth: 1,
                    titleColor: '#86efac',
                    bodyColor: '#f0fdf4',
                    padding: 10,
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y}%`
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    ticks: {
                        color: '#4ade8088',
                        font: { size: 10 },
                        maxTicksLimit: 6,
                    },
                    grid: { color: 'rgba(34,197,94,0.05)' }
                },
                y: {
                    min: 0, max: 100,
                    display: true,
                    ticks: {
                        color: '#4ade8088',
                        font: { size: 10 },
                        callback: v => v + '%'
                    },
                    grid: { color: 'rgba(34,197,94,0.07)' }
                }
            }
        }
    });
}

// ---------------------------------------------------
// Fetch Latest Sensor Data
// ---------------------------------------------------
async function fetchLatest() {
    showSpinner(true);
    try {
        const res  = await fetch(`${API_BASE}/sensor/latest`);
        const json = await res.json();

        if (json.success && json.data) {
            updateMetricCards(json.data);
            addChartPoint(json.data);
            lastUpdateTime = new Date();
            updateLastUpdateText();
        }

        // Check connection status
        const statusRes  = await fetch(`${API_BASE}/sensor/status`);
        const statusJson = await statusRes.json();
        updateConnectionStatus(statusJson);
    } catch (err) {
        console.error('Fetch error:', err);
        setOfflineStatus();
    } finally {
        showSpinner(false);
    }
}

// ---------------------------------------------------
// Fetch History
// ---------------------------------------------------
async function fetchHistory() {
    try {
        const res  = await fetch(`${API_BASE}/sensor/history`);
        const json = await res.json();
        updateHistoryTable(json.data || []);
    } catch (err) {
        console.error('History fetch error:', err);
    }
}

// ---------------------------------------------------
// Update Metric Cards
// ---------------------------------------------------
function updateMetricCards(data) {
    // Soil Moisture
    const moisture = data.soil_moisture ?? 0;
    const soilEl   = document.getElementById('soilValue');
    soilEl.innerHTML = `${moisture}<span class="metric-unit">%</span>`;

    const fill = document.getElementById('moistureFill');
    fill.style.width = `${moisture}%`;
    fill.className = 'moisture-fill';
    if (moisture < 30) {
        fill.classList.add('low');
        soilEl.style.color = 'var(--red-400)';
    } else if (moisture < 60) {
        fill.classList.add('medium');
        soilEl.style.color = 'var(--amber-400)';
    } else {
        soilEl.style.color = 'var(--green-400)';
    }

    document.getElementById('soilAdcText').textContent = `ADC: ${data.soil_adc ?? '—'}`;

    // Rain
    const isRaining = data.is_raining;
    const rainIcon  = document.getElementById('rainIcon');
    rainIcon.textContent  = isRaining ? '🌧️' : '☀️';
    rainIcon.className    = 'rain-icon-wrap' + (isRaining ? ' raining' : '');
    document.getElementById('rainValue').textContent = data.rain_label ?? '—';
    document.getElementById('rainValue').style.color = isRaining ? 'var(--blue-400)' : 'var(--amber-400)';

    // Pump
    const pumpOn = data.pump_status;
    currentPumpStatus = pumpOn;
    
    const pumpBadge = document.getElementById('pumpBadge');
    pumpBadge.textContent = data.pump_label ?? '—';
    pumpBadge.className   = 'pump-status-value ' + (pumpOn ? 'pump-on' : 'pump-off');
    document.getElementById('pumpModeText').textContent = 'Mode: ' + (activeCmd ? activeCmd.toUpperCase() : 'AUTO');

    // Update UI Button Manual
    const btnManual = document.getElementById('btnManual');
    if (activeCmd === 'on' || activeCmd === 'off') {
        // Jika mode manual aktif
        btnManual.classList.add('active-cmd');
        if (pumpOn) {
            btnManual.innerHTML = '🚫 MANUAL (MATIKAN)';
            btnManual.className = 'btn-control btn-off active-cmd'; // merah karena aksi selanjutnya mematikan
        } else {
            btnManual.innerHTML = '💧 MANUAL (NYALAKAN)';
            btnManual.className = 'btn-control btn-on active-cmd'; // hijau karena aksi selanjutnya menyalakan
        }
    } else {
        // Jika mode auto aktif
        btnManual.classList.remove('active-cmd');
        btnManual.innerHTML = '⚙️ MANUAL';
        btnManual.className = 'btn-control btn-off';
    }

    // RTC Clock
    if (data.recorded_at) {
        const dt = new Date(data.recorded_at);
        document.getElementById('rtcTime').textContent = dt.toLocaleTimeString('id-ID');
        document.getElementById('rtcDate').textContent = dt.toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });
    }
}

// ---------------------------------------------------
// Add Chart Point
// ---------------------------------------------------
function addChartPoint(data) {
    if (!soilChart || !data.recorded_at) return;

    const label = new Date(data.recorded_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

    // Avoid duplicate timestamps
    const labels = soilChart.data.labels;
    if (labels.length > 0 && labels[labels.length - 1] === label) return;

    soilChart.data.labels.push(label);
    soilChart.data.datasets[0].data.push(data.soil_moisture);

    if (soilChart.data.labels.length > CHART_MAX) {
        soilChart.data.labels.shift();
        soilChart.data.datasets[0].data.shift();
    }

    soilChart.update('none');
}

// ---------------------------------------------------
// Update History Table
// ---------------------------------------------------
function updateHistoryTable(rows) {
    const count   = rows.length;
    const countEl = document.getElementById('historyCount');
    const empty   = document.getElementById('tableEmpty');
    const container = document.getElementById('tableContainer');
    const tbody   = document.getElementById('historyBody');

    countEl.textContent = `${count} data`;

    if (count === 0) {
        empty.style.display     = 'block';
        container.style.display = 'none';
        return;
    }

    empty.style.display     = 'none';
    container.style.display = 'block';

    tbody.innerHTML = rows.slice(0, 10).map(row => {
        const dt = row.recorded_at ? new Date(row.recorded_at) : null;
        const timeStr = dt ? dt.toLocaleString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', day: '2-digit', month: '2-digit' }) : '—';
        const rainBadge = row.is_raining
            ? '<span class="badge badge-blue">🌧 Hujan</span>'
            : '<span class="badge badge-amber">☀️ Kering</span>';
        const pumpBadge = row.pump_status
            ? '<span class="badge badge-green">💧 ON</span>'
            : '<span class="badge badge-red">🚫 OFF</span>';
        const moisture = row.soil_moisture ?? 0;
        const moistureColor = moisture < 30 ? 'var(--red-400)' : moisture < 60 ? 'var(--amber-400)' : 'var(--green-400)';

        return `<tr>
            <td style="font-variant-numeric:tabular-nums; font-size:0.78rem;">${timeStr}</td>
            <td><span style="color:${moistureColor}; font-weight:600;">${moisture}%</span></td>
            <td style="color:var(--text-muted); font-size:0.75rem;">${row.soil_adc ?? '—'}</td>
            <td>${rainBadge}</td>
            <td>${pumpBadge}</td>
        </tr>`;
    }).join('');
}

// ---------------------------------------------------
// Connection Status
// ---------------------------------------------------
function updateConnectionStatus(status) {
    const badge = document.getElementById('statusBadge');
    const label = document.getElementById('statusLabel');

    if (status.is_online) {
        badge.className = 'status-badge online';
        label.textContent = 'ESP32 Online';
    } else if (status.last_seen) {
        badge.className = 'status-badge offline';
        const secsAgo = Math.round(status.seconds_ago ?? 0);
        label.textContent = `Terakhir ${secsAgo}d lalu`;
    } else {
        setOfflineStatus();
    }
}

function setOfflineStatus() {
    const badge = document.getElementById('statusBadge');
    const label = document.getElementById('statusLabel');
    badge.className   = 'status-badge offline';
    label.textContent = 'Tidak Terhubung';
}

// ---------------------------------------------------
// Pump Command
// ---------------------------------------------------
async function sendPumpCommand(cmd) {
    if (isSendingCmd) return; // Mencegah spam klik beruntun
    isSendingCmd = true;
    
    activeCmd = cmd;

    // Highlight active button
    if (cmd === 'auto') {
        document.getElementById('btnAuto').classList.add('active-cmd');
        document.getElementById('btnManual').classList.remove('active-cmd');
    } else {
        document.getElementById('btnAuto').classList.remove('active-cmd');
        document.getElementById('btnManual').classList.add('active-cmd');
    }

    try {
        const res  = await fetch(`${API_BASE}/pump/command`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ command: cmd })
        });
        const json = await res.json();

        if (json.success) {
            showCmdStatus(`✅ Perintah <strong>${cmd.toUpperCase()}</strong> dikirim ke ESP32`);
            showToast(`Perintah pompa ${cmd.toUpperCase()} berhasil dikirim! 🚀`, 'success');
        } else {
            showCmdStatus('❌ Gagal mengirim perintah');
            showToast('Gagal mengirim perintah pompa', 'error');
        }
    } catch (err) {
        showCmdStatus('❌ Error koneksi server');
        showToast('Error: Tidak dapat terhubung ke server', 'error');
    } finally {
        // Beri jeda 500ms sebelum tombol bisa ditekan lagi
        setTimeout(() => { isSendingCmd = false; }, 500);
    }
}

function showCmdStatus(msg) {
    document.getElementById('cmdStatus').innerHTML = msg;
}

// ---------------------------------------------------
// Toggle Manual Pump
// ---------------------------------------------------
function toggleManualPump() {
    // Jika pompa sedang menyala, kirim OFF. Jika mati, kirim ON.
    const newCmd = currentPumpStatus ? 'off' : 'on';
    sendPumpCommand(newCmd);
}

// ---------------------------------------------------
// Toast
// ---------------------------------------------------
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast     = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = (type === 'success' ? '✅' : '❌') + ' ' + message;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'none';
        toast.style.opacity   = '0';
        toast.style.transform = 'translateX(20px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// ---------------------------------------------------
// Helpers
// ---------------------------------------------------
function showSpinner(show) {
    document.getElementById('refreshSpinner').classList.toggle('active', show);
}

function updateLastUpdateText() {
    if (!lastUpdateTime) return;
    document.getElementById('lastUpdateText').textContent =
        `Diperbarui: ${lastUpdateTime.toLocaleTimeString('id-ID')} · Refresh otomatis setiap ${POLL_MS / 1000}d`;
}

// ---------------------------------------------------
// Init & Poll
// ---------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    initChart();
    fetchLatest();
    fetchHistory();

    setInterval(() => {
        fetchLatest();
        fetchHistory();
    }, POLL_MS);
});
</script>
</body>
</html>
