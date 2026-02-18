<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Dinas Perpustakaan dan Kearsipan Kabupaten Seruyan</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #10b981;
            --bg-color: #f3f4f6;
            --sidebar-width: 260px;
            --text-color: #1f2937;
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: 1px solid rgba(255, 255, 255, 0.3);
            --mobile-nav-height: 60px;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            display: flex;
            min-height: 100vh;
            color: var(--text-color);
            overflow-x: hidden;
        }

        /* Mobile Navbar */
        .mobile-navbar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--mobile-nav-height);
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            z-index: 1000;
            padding: 0 1rem;
            align-items: center;
            justify-content: space-between;
        }

        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary-color);
            cursor: pointer;
            padding: 0.5rem;
        }

        .mobile-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .mobile-logo {
            height: 32px;
            width: auto;
        }

        .mobile-brand h2 {
            font-size: 1.1rem;
            margin: 0;
            font-weight: 800;
            color: var(--primary-color);
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: #ffffff;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            z-index: 1100;
            transition: transform 0.3s ease;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1050;
        }

        .sidebar-header {
            padding: 1.25rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
            text-align: center;
            gap: 0.75rem;
            position: relative;
        }

        .close-sidebar {
            display: none;
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #6b7280;
            cursor: pointer;
        }

        .sidebar-logo {
            width: 70px;
            height: auto;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.05));
        }

        .sidebar-header .app-name-container {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .sidebar-header h2 {
            font-size: 1.4rem;
            margin: 0;
            color: var(--primary-color);
            font-weight: 800;
            letter-spacing: 0.05em;
            line-height: 1;
        }

        .sidebar-header p {
            font-size: 0.65rem;
            margin: 0;
            color: var(--text-muted, #64748b);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #4b5563;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: #eef2ff;
            color: var(--primary-color);
        }

        .sidebar-menu a i {
            width: 24px;
            margin-right: 12px;
        }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid #f3f4f6;
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 12px;
        }

        .user-details h4 {
            margin: 0;
            font-size: 0.9rem;
        }

        .user-details p {
            margin: 0;
            font-size: 0.75rem;
            color: #6b7280;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            flex-grow: 1;
            width: calc(100% - var(--sidebar-width));
            transition: all 0.3s ease;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        /* Card Style */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .mobile-navbar {
                display: flex;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .sidebar.active+.sidebar-overlay {
                display: block;
            }

            .close-sidebar {
                display: block;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
                padding-top: calc(var(--mobile-nav-height) + 1.5rem);
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 1rem;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            font-family: 'Outfit', sans-serif;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-content h3 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
        }

        .stat-content p {
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        /* Table */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
        }

        th {
            font-weight: 600;
            color: #6b7280;
            background-color: #f9fafb;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* Form System */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 1.5rem;
        }

        .col-span-12 {
            grid-column: span 12;
        }

        .col-span-8 {
            grid-column: span 8;
        }

        .col-span-6 {
            grid-column: span 6;
        }

        .col-span-4 {
            grid-column: span 4;
        }

        .col-span-3 {
            grid-column: span 3;
        }

        .col-span-2 {
            grid-column: span 2;
        }

        @media (max-width: 768px) {

            .col-span-6,
            .col-span-4,
            .col-span-3,
            .col-span-2 {
                grid-column: span 12;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            /* Space between label and input */
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #374151;
        }

        .text-danger {
            color: #ef4444;
        }

        .form-control {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Outfit', sans-serif;
            color: #1f2937;
            background-color: #fff;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            box-sizing: border-box;
            /* Ensures padding doesn't overflow width */
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        select.form-control {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            appearance: none;
        }

        input[type="file"].form-control {
            padding: 0.4rem;
        }

        input[type="file"]::file-selector-button {
            margin-right: 1rem;
            background: #f3f4f6;
            border: none;
            border-radius: 4px;
            padding: 0.25rem 0.75rem;
            color: #374151;
            cursor: pointer;
            transition: background 0.2s;
        }

        input[type="file"]::file-selector-button:hover {
            background: #e5e7eb;
        }

        .section-separator {
            grid-column: span 12;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #111827;
        }

        .section-separator i {
            color: var(--primary-color);
            background: #e0e7ff;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .section-separator h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
        }

        .form-hint {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body>
    <nav class="mobile-navbar">
        <button class="menu-toggle" id="openSidebar">
            <i class="fas fa-bars"></i>
        </button>
        <div class="mobile-brand">
            <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" class="mobile-logo">
            <h2>SICAPE</h2>
        </div>
        <div style="width: 40px;"></div> <!-- Spacer -->
    </nav>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="close-sidebar" id="closeSidebar">
                <i class="fas fa-times"></i>
            </button>
            <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" class="sidebar-logo">
            <div class="app-name-container">
                <h2>SICAPE</h2>
                <p>Sistem Cuti Administrasi Pegawai</p>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="<?= base_url('dashboard') ?>" class="<?= uri_string() == 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="<?= base_url('leave/create') ?>" class="<?= uri_string() == 'leave/create' ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i> Ajukan Cuti
                </a>
            </li>
            <li>
                <a href="<?= base_url('leave/history') ?>"
                    class="<?= uri_string() == 'leave/history' ? 'active' : '' ?>">
                    <i class="fas fa-history"></i> Riwayat Cuti
                </a>
            </li>
            <li>
                <a href="<?= base_url('approval') ?>" class="<?= uri_string() == 'approval' ? 'active' : '' ?>">
                    <i class="fas fa-check-circle"></i> Persetujuan Atasan
                </a>
            </li>
            <?php if (session()->get('role') == 'admin'): ?>
                <li>
                    <a href="<?= base_url('admin') ?>">
                        <i class="fas fa-user-shield"></i> Admin Panel
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('employee') ?>" class="<?= uri_string() == 'employee' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i> Manajemen Pegawai
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('employee/contracts') ?>"
                        class="<?= uri_string() == 'employee/contracts' ? 'active' : '' ?>">
                        <i class="fas fa-file-contract"></i> Manajemen Kontrak
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('employee/supervisors') ?>"
                        class="<?= uri_string() == 'employee/supervisors' ? 'active' : '' ?>">
                        <i class="fas fa-user-tie"></i> Data Atasan
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('employee/hierarchy') ?>"
                        class="<?= uri_string() == 'employee/hierarchy' ? 'active' : '' ?>">
                        <i class="fas fa-sitemap"></i> Manajemen Hirarki
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('employee/admins') ?>"
                        class="<?= uri_string() == 'employee/admins' ? 'active' : '' ?>">
                        <i class="fas fa-user-shield"></i> Data Administrator
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('admin/holidays') ?>"
                        class="<?= uri_string() == 'admin/holidays' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-times"></i> Manajemen Hari Libur
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('report') ?>" class="<?= strpos(uri_string(), 'report') === 0 ? 'active' : '' ?>">
                        <i class="fas fa-file-invoice"></i> Laporan
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <?= substr(session()->get('name'), 0, 1) ?>
                </div>
                <div class="user-details">
                    <h4><?= session()->get('name') ?></h4>
                    <p><?= session()->get('role') == 'admin' ? 'Administrator' : 'Pegawai ASN' ?></p>
                </div>
            </div>
            <a href="<?= base_url('logout') ?>" class="btn btn-danger btn-sm"
                style="width: 100%; justify-content: center;">
                <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Logout
            </a>
        </div>
    </aside>

    <main class="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const openSidebar = document.getElementById('openSidebar');
            const closeSidebar = document.getElementById('closeSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            function toggleSidebar() {
                sidebar.classList.toggle('active');
            }

            openSidebar.addEventListener('click', toggleSidebar);
            closeSidebar.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', toggleSidebar);

            // Close sidebar on menu click (mobile)
            const menuLinks = sidebar.querySelectorAll('.sidebar-menu a');
            menuLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 1024) {
                        sidebar.classList.remove('active');
                    }
                });
            });
        });
    </script>
</body>

</html>