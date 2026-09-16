<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login — MC App Almara</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(
                    circle at 15% 20%,
                    rgba(16, 185, 129, 0.16),
                    transparent 32%
                ),
                radial-gradient(
                    circle at 85% 80%,
                    rgba(234, 179, 8, 0.10),
                    transparent 30%
                ),
                #f5f7f6;
            color: #17211c;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-shell {
            width: 100%;
            max-width: 1080px;
            min-height: 650px;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(15, 118, 110, 0.12);
            border-radius: 28px;
            overflow: hidden;
            box-shadow:
                0 30px 80px rgba(15, 23, 42, 0.12),
                0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .brand-panel {
            position: relative;
            overflow: hidden;
            padding: 52px;
            color: white;
            background:
                linear-gradient(
                    145deg,
                    #064e3b 0%,
                    #047857 48%,
                    #059669 100%
                );
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-panel::before {
            content: "";
            position: absolute;
            width: 340px;
            height: 340px;
            right: -120px;
            top: -120px;
            border: 1px solid rgba(255,255,255,0.13);
            border-radius: 50%;
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            width: 460px;
            height: 460px;
            left: -240px;
            bottom: -260px;
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 50%;
        }

        .brand-content,
        .brand-footer {
            position: relative;
            z-index: 1;
        }

        .logo {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.22);
            font-size: 30px;
            font-weight: 800;
            color: #facc15;
            box-shadow: 0 12px 28px rgba(0,0,0,0.12);
        }

        .brand-title {
            margin: 28px 0 12px;
            font-size: 40px;
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: -1.5px;
        }

        .brand-subtitle {
            max-width: 440px;
            margin: 0;
            color: rgba(255,255,255,0.80);
            font-size: 16px;
            line-height: 1.7;
        }

        .feature-list {
            margin-top: 42px;
            display: grid;
            gap: 14px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.90);
            font-size: 14px;
        }

        .feature-icon {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.10);
            color: #fde68a;
            font-weight: 700;
        }

        .brand-footer {
            color: rgba(255,255,255,0.58);
            font-size: 12px;
        }

        .login-panel {
            padding: 58px;
            display: flex;
            align-items: center;
        }

        .login-form {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
        }

        .eyebrow {
            color: #047857;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .login-title {
            margin: 0;
            font-size: 34px;
            line-height: 1.15;
            letter-spacing: -1px;
            color: #111827;
        }

        .login-description {
            margin: 12px 0 32px;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }

        .field {
            margin-bottom: 20px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
        }

        .input {
            width: 100%;
            height: 50px;
            border: 1px solid #d9e1dd;
            border-radius: 12px;
            padding: 0 15px;
            outline: none;
            background: #fbfdfc;
            color: #111827;
            font-size: 14px;
            transition:
                border-color .2s ease,
                box-shadow .2s ease,
                background .2s ease;
        }

        .input:focus {
            background: white;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16,185,129,0.10);
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 4px 0 24px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 13px;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: #059669;
        }

        .submit-button {
            width: 100%;
            height: 52px;
            border: 0;
            border-radius: 13px;
            background:
                linear-gradient(
                    135deg,
                    #047857,
                    #059669
                );
            color: white;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(5,150,105,0.20);
            transition:
                transform .15s ease,
                box-shadow .15s ease;
        }

        .submit-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(5,150,105,0.25);
        }

        .error-box {
            margin-bottom: 20px;
            padding: 12px 14px;
            border-radius: 11px;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;
            font-size: 13px;
            line-height: 1.5;
        }

        .field-error {
            margin-top: 7px;
            color: #be123c;
            font-size: 12px;
        }

        .login-note {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #edf1ee;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
            line-height: 1.6;
        }

        .gold {
            color: #d4a72c;
        }

        @media (max-width: 820px) {
            body {
                padding: 14px;
            }

            .login-shell {
                grid-template-columns: 1fr;
                min-height: auto;
                border-radius: 22px;
            }

            .brand-panel {
                padding: 34px;
                min-height: 330px;
            }

            .brand-title {
                font-size: 32px;
            }

            .feature-list {
                margin-top: 28px;
            }

            .brand-footer {
                margin-top: 30px;
            }

            .login-panel {
                padding: 38px 28px;
            }
        }

        @media (max-width: 480px) {
            .brand-panel {
                padding: 28px;
            }

            .login-panel {
                padding: 32px 22px;
            }

            .brand-title {
                font-size: 29px;
            }

            .login-title {
                font-size: 29px;
            }
        }
    </style>
</head>

<body>

<div class="login-shell">

    <section class="brand-panel">

        <div class="brand-content">

            <div class="logo">
                MC
            </div>

            <h1 class="brand-title">
                MC App<br>
                <span class="gold">Almara</span>
            </h1>

            <p class="brand-subtitle">
                Money Changer Digital OS untuk mengelola
                operasional money changer secara terintegrasi,
                aman, dan profesional.
            </p>

            <div class="feature-list">

                <div class="feature">
                    <div class="feature-icon">✓</div>
                    <span>Multi Tenant &amp; Multi Cabang</span>
                </div>

                <div class="feature">
                    <div class="feature-icon">✓</div>
                    <span>RBAC &amp; Permission Management</span>
                </div>

                <div class="feature">
                    <div class="feature-icon">✓</div>
                    <span>Transaction &amp; Financial Control</span>
                </div>

                <div class="feature">
                    <div class="feature-icon">✓</div>
                    <span>Audit Trail &amp; Security</span>
                </div>

            </div>

        </div>

        <div class="brand-footer">
            MC App Almara · Money Changer Digital OS
        </div>

    </section>

    <section class="login-panel">

        <form
            class="login-form"
            method="POST"
            action="{{ url('/login') }}"
        >

            @csrf

            <div class="eyebrow">
                Secure Access
            </div>

            <h2 class="login-title">
                Selamat Datang
            </h2>

            <p class="login-description">
                Masuk menggunakan akun Anda untuk mengakses
                workspace MC App Almara.
            </p>

            @if ($errors->any())
                <div class="error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="field">

                <label for="email">
                    Email
                </label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    class="input"
                    value="{{ old('email') }}"
                    placeholder="nama@perusahaan.com"
                    autocomplete="email"
                    required
                    autofocus
                >

                @error('email')
                    <div class="field-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="field">

                <label for="password">
                    Password
                </label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    class="input"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >

                @error('password')
                    <div class="field-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="remember-row">

                <label class="remember">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                    >

                    <span>Ingat saya</span>
                </label>

            </div>

            <button
                type="submit"
                class="submit-button"
            >
                Masuk ke MC App
            </button>

            <div class="login-note">
                Akses sistem dilindungi oleh autentikasi,
                tenant isolation, RBAC, dan permission control.
            </div>

        </form>

    </section>

</div>

</body>
</html>