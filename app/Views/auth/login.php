<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SICAPE Kabupaten Seruyan</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --primary-glow: rgba(79, 70, 229, 0.4);
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.5);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --input-bg: rgba(255, 255, 255, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #0f172a;
            overflow: hidden;
            position: relative;
        }

        /* Dynamic Background Elements */
        .bg-blobs {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            filter: blur(80px);
        }

        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            animation: move 20s infinite alternate;
        }

        .blob-1 {
            background: #4f46e5;
            top: -10%;
            left: -10%;
            opacity: 0.4;
        }

        .blob-2 {
            background: #7c3aed;
            bottom: -10%;
            right: -10%;
            opacity: 0.4;
            animation-delay: -5s;
        }

        .blob-3 {
            background: #0ea5e9;
            top: 40%;
            left: 30%;
            opacity: 0.2;
            animation-delay: -10s;
        }

        @keyframes move {
            from {
                transform: translate(0, 0) scale(1);
            }

            to {
                transform: translate(100px, 100px) scale(1.1);
            }
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 20px;
            z-index: 10;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            padding: 2.5rem 2.25rem 1.75rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            text-align: center;
            position: relative;
        }

        .login-logo {
            max-width: 100%;
            height: auto;
            width: auto;
            display: block;
            margin: 0 auto 1.5rem;
            /* Intensified multi-layered glow */
            filter:
                drop-shadow(0 0 20px rgba(255, 255, 255, 0.8)) drop-shadow(0 0 40px rgba(255, 255, 255, 0.4)) drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .login-logo:hover {
            transform: scale(1.05);
        }

        .login-header {
            margin-bottom: 1.5rem;
        }

        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .form-group {
            margin-bottom: 1.15rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            width: 20px;
            height: 20px;
        }

        .form-control {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.75rem;
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            background: var(--input-bg);
            font-size: 1rem;
            font-family: 'Outfit', sans-serif;
            transition: all 0.3s ease;
            color: var(--text-main);
        }

        .form-control:focus {
            outline: none;
            background: #fff;
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px var(--primary-glow);
            transform: translateY(-1px);
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 16px;
            background: var(--primary-gradient);
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 1rem;
            box-shadow: 0 10px 15px -3px var(--primary-glow);
            font-family: 'Outfit', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px var(--primary-glow);
            filter: brightness(1.1);
        }

        .btn-login:active {
            transform: translateY(1px);
        }

        .alert {
            padding: 0.85rem;
            margin-bottom: 1.25rem;
            border-radius: 16px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .footer {
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: center;
        }

        .system-badge {
            display: inline-block;
            margin-top: 1rem;
            padding: 4px 12px;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 100px;
            font-weight: 600;
            color: var(--text-muted);
        }

        /* SVG Icons */
        .icon-path {
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
    </style>
</head>

<body>
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="login-wrapper">
        <div class="login-card">
            <img src="<?= base_url('assets/img/logo_horizontal.png') ?>" alt="SICAPE Logo" class="login-logo">

            <div class="login-header">
                <h1>Selamat Datang</h1>
                <p>Sistem Cuti Administrasi Pegawai<br>Dinas Perpustakaan & Kearsipan Seruyan</p>
            </div>

            <?php if (session()->getFlashdata('msg')): ?>
                <div class="alert alert-danger">
                    <svg class="input-icon" viewBox="0 0 24 24">
                        <path class="icon-path" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?= session()->getFlashdata('msg') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('/auth/login') ?>" method="post">
                <div class="form-group">
                    <label for="nip">NIP Pegawai</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24">
                            <path class="icon-path"
                                d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 7a4 4 0 100-8 4 4 0 000 8z" />
                        </svg>
                        <input type="text" name="nip" class="form-control" id="nip" placeholder="Masukkan NIP Anda"
                            required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24">
                            <path class="icon-path"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <input type="password" name="password" class="form-control" id="password"
                            placeholder="Masukkan Kata Sandi" required>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    Login <i class="fas fa-sign-in-alt"></i>
                </button>
            </form>

            <div class="footer">
                <p>&copy; 2026 Dinas Perpustakaan dan Kearsipan Kabupaten Seruyan</p>
            </div>
        </div>
    </div>
</body>

</html>