<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cuenta Bloqueada — ErpTaller</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet" />
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #0d0a07;
            --card: #141009;
            --border: rgba(200, 160, 80, 0.18);
            --gold: #c8a050;
            --gold-lt: #e8c87a;
            --red: #c0392b;
            --red-glow: rgba(192, 57, 43, 0.25);
            --text: #f0e8d8;
            --muted: #8a7a60;
            --ff-head: 'Playfair Display', serif;
            --ff-body: 'DM Sans', sans-serif;
        }

        html {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: var(--ff-body);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 40px 0;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 50% -10%, rgba(200, 160, 80, 0.07) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 80% 110%, rgba(192, 57, 43, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 10% 90%, rgba(200, 160, 80, 0.04) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            background-size: 200px 200px;
            opacity: 0.4;
            pointer-events: none;
            z-index: 0;
        }

        .deco-lines {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .deco-lines::before,
        .deco-lines::after {
            content: '';
            position: absolute;
            border: 1px solid var(--border);
            border-radius: 50%;
        }

        .deco-lines::before {
            width: 700px;
            height: 700px;
            top: -200px;
            right: -150px;
            animation: spin 40s linear infinite;
        }

        .deco-lines::after {
            width: 500px;
            height: 500px;
            bottom: -200px;
            left: -100px;
            animation: spin 60s linear infinite reverse;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .card {
            position: relative;
            z-index: 1;
            max-width: 560px;
            width: 90%;
            margin: auto;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.01));
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 56px 52px;
            text-align: center;
            box-shadow:
                0 0 0 1px rgba(200, 160, 80, 0.06),
                0 30px 80px rgba(0, 0, 0, 0.7),
                0 0 60px var(--red-glow);
            animation: fadeUp .9s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-mark {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 40px;
            animation: fadeUp .9s .1s cubic-bezier(.22, 1, .36, 1) both;
        }

        .logo-icon {
            width: 28px;
            height: 28px;
            position: relative;
        }

        .logo-icon svg {
            width: 100%;
            height: 100%;
        }

        .logo-text {
            font-family: var(--ff-head);
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: .04em;
            color: var(--gold);
        }

        .logo-text span {
            color: var(--gold-lt);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 36px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--border), transparent);
        }

        .divider-diamond {
            width: 6px;
            height: 6px;
            background: var(--gold);
            transform: rotate(45deg);
            flex-shrink: 0;
        }

        .lock-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
            animation: fadeUp .9s .2s cubic-bezier(.22, 1, .36, 1) both;
        }

        .lock-ring {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            border: 1px solid rgba(192, 57, 43, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .lock-ring::before {
            content: '';
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 1px solid rgba(192, 57, 43, 0.2);
        }

        .lock-ring-pulse {
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 1px solid rgba(192, 57, 43, 0.15);
            animation: pulse 2.5s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.08);
                opacity: 0;
            }
        }

        .lock-icon {
            width: 40px;
            height: 40px;
            color: var(--red);
            filter: drop-shadow(0 0 12px rgba(192, 57, 43, 0.5));
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(192, 57, 43, 0.12);
            border: 1px solid rgba(192, 57, 43, 0.3);
            border-radius: 2px;
            padding: 5px 14px;
            font-size: .7rem;
            font-weight: 500;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #e87060;
            margin-bottom: 24px;
        }

        .badge-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #e87060;
            animation: blink 1.4s ease-in-out infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .2;
            }
        }

        .heading {
            font-family: var(--ff-head);
            font-size: 2rem;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -.01em;
            margin-bottom: 14px;
            color: var(--text);
            animation: fadeUp .9s .3s cubic-bezier(.22, 1, .36, 1) both;
        }

        .heading em {
            font-style: normal;
            color: var(--gold);
        }

        .sub {
            font-size: .93rem;
            font-weight: 300;
            color: var(--muted);
            line-height: 1.7;
            margin-bottom: 40px;
            animation: fadeUp .9s .4s cubic-bezier(.22, 1, .36, 1) both;
        }

        .info-box {
            background: rgba(200, 160, 80, 0.05);
            border: 1px solid rgba(200, 160, 80, 0.14);
            border-radius: 3px;
            padding: 20px 24px;
            margin-bottom: 36px;
            text-align: left;
            animation: fadeUp .9s .5s cubic-bezier(.22, 1, .36, 1) both;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(200, 160, 80, 0.08);
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-row:first-child {
            padding-top: 0;
        }

        .info-icon {
            width: 32px;
            height: 32px;
            background: rgba(200, 160, 80, 0.08);
            border-radius: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .info-icon svg {
            width: 15px;
            height: 15px;
            color: var(--gold);
        }

        .info-label {
            font-size: .72rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 1px;
        }

        .info-value {
            font-size: .88rem;
            font-weight: 500;
            color: var(--text);
        }

        .cta {
            animation: fadeUp .9s .6s cubic-bezier(.22, 1, .36, 1) both;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--gold), #a8823a);
            color: #0d0a07;
            font-family: var(--ff-body);
            font-size: .85rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            text-decoration: none;
            border: none;
            border-radius: 2px;
            padding: 14px 32px;
            cursor: pointer;
            transition: all .25s ease;
            width: 100%;
            justify-content: center;
            margin-bottom: 14px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--gold-lt), var(--gold));
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(200, 160, 80, 0.25);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            color: var(--muted);
            font-family: var(--ff-body);
            font-size: .82rem;
            font-weight: 400;
            text-decoration: none;
            border: 1px solid var(--border);
            border-radius: 2px;
            padding: 12px 32px;
            cursor: pointer;
            transition: all .25s ease;
            width: 100%;
            justify-content: center;
        }

        .btn-secondary:hover {
            color: var(--text);
            border-color: rgba(200, 160, 80, 0.3);
        }

        .footer-note {
            margin-top: 36px;
            font-size: .72rem;
            color: var(--muted);
            letter-spacing: .04em;
            animation: fadeUp .9s .7s cubic-bezier(.22, 1, .36, 1) both;
        }

        .footer-note a {
            color: var(--gold);
            text-decoration: none;
        }

        .footer-note a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .card {
                padding: 40px 28px;
            }

            .heading {
                font-size: 1.6rem;
            }
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, var(--gold), #a8823a);
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, var(--gold-lt), var(--gold));
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: var(--gold) var(--bg);
        }
    </style>
</head>

<body>

    <div class="deco-lines"></div>

    <div class="card">

        <div class="logo-mark">
            <div class="logo-icon">
                <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 26 L14 6 L18 6 L26 26" stroke="#c8a050" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <circle cx="16" cy="16" r="3.2" fill="none" stroke="#e8c87a" stroke-width="1.4" />
                    <line x1="16" y1="16" x2="16" y2="11" stroke="#e8c87a" stroke-width="1.2"
                        stroke-linecap="round" />
                    <line x1="16" y1="16" x2="19" y2="17.5" stroke="#e8c87a" stroke-width="1.2"
                        stroke-linecap="round" />
                </svg>
            </div>
            <div class="logo-text">Erp<span>Taller</span></div>
        </div>

        <div class="divider">
            <div class="divider-diamond"></div>
        </div>

        <div class="lock-wrap">
            <div class="lock-ring-pulse"></div>
            <div class="lock-ring">
                <svg class="lock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
            </div>
        </div>

        <div>
            <span class="badge">
                <span class="badge-dot"></span>
                Cuenta suspendida
            </span>
        </div>

        <h1 class="heading">
            Acceso <em>Restringido</em>
        </h1>

        <p class="sub">
            Tu cuenta ha sido temporalmente bloqueada.<br />
            Por favor, contacta a nuestro equipo de soporte para reactivar tu servicio.
        </p>

        <div class="info-box">
            <div class="info-row">
                <div class="info-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                </div>
                <div>
                    <div class="info-label">Motivo</div>
                    <div class="info-value">Suscripción vencida o pago pendiente</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.07 12 19.79 19.79 0 0 1 1.07 3.4 2 2 0 0 1 3 1.07h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16z" />
                    </svg>
                </div>
                <div>
                    <div class="info-label">Soporte</div>
                    <div class="info-value">soporte@erptaller.com</div>
                </div>
            </div>
        </div>

        <div class="cta">
            <a href="mailto:soporte@erptaller.com" class="btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                    <polyline points="22,6 12,13 2,6" />
                </svg>
                Contactar Soporte
            </a>
            <a href="{{ route('login') }}" class="btn-secondary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12" />
                    <polyline points="12 19 5 12 12 5" />
                </svg>
                Volver al inicio de sesión
            </a>
        </div>

        <div class="footer-note">
            ¿Necesitas ayuda urgente? Escríbenos a <a href="mailto:soporte@erptaller.com">soporte@erptaller.com</a>
        </div>

    </div>

</body>

</html>
