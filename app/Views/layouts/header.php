<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Althia - Smart Healthcare Platform</title>
    <meta name="description" content="Althia connects patients, hospitals and care teams to deliver a smoother, faster and more humane healthcare experience.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=DM+Sans:wght@700;800&family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <?php $currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
    <?php $user = \App\Core\Auth::user(); ?>
    <?php $role = \App\Core\Auth::role(); ?>
    <?php $isDashboard = preg_match('#^/(patient|doctor|admin)/#', $currentUrl); ?>

    <?php $successMsg = flash('success'); ?>
    <?php $errorMsg = flash('error'); ?>
    <?php if ($successMsg): ?>
        <div class="flash-message flash-success"><?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="flash-message flash-error"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <?php if ($isDashboard): ?>

    <?php
    $sidebarLinks = [];
    if ($role === 'patient') {
        $sidebarLinks = [
            ['label' => 'Dashboard',       'icon' => 'fa-chart-pie',        'url' => '/patient/dashboard'],
            ['label' => 'My Profile',      'icon' => 'fa-user',            'url' => '/patient/profile'],
            ['label' => 'Appointments',    'icon' => 'fa-calendar-check',   'url' => '/patient/appointments'],
            ['label' => 'Medical Records', 'icon' => 'fa-notes-medical',   'url' => '/patient/records'],
            ['label' => 'Prescriptions',   'icon' => 'fa-prescription',    'url' => '/patient/prescriptions'],
            ['label' => 'Messages',        'icon' => 'fa-comments',        'url' => '/patient/messages'],
            ['label' => 'Notifications',   'icon' => 'fa-bell',            'url' => '/patient/notifications'],
        ];
    } elseif ($role === 'doctor') {
        $sidebarLinks = [
            ['label' => 'Dashboard',      'icon' => 'fa-chart-pie',       'url' => '/doctor/dashboard'],
            ['label' => 'My Profile',     'icon' => 'fa-user',            'url' => '/doctor/profile'],
            ['label' => 'My Patients',    'icon' => 'fa-users',           'url' => '/doctor/patients'],
            ['label' => 'Appointments',   'icon' => 'fa-calendar-check',  'url' => '/doctor/appointments'],
            ['label' => 'Prescriptions',  'icon' => 'fa-prescription',    'url' => '/doctor/prescriptions'],
            ['label' => 'Messages',       'icon' => 'fa-comments',        'url' => '/doctor/messages'],
            ['label' => 'Availability',   'icon' => 'fa-clock',           'url' => '/doctor/availability'],
            ['label' => 'Notifications',  'icon' => 'fa-bell',            'url' => '/doctor/notifications'],
        ];
    } elseif ($role === 'admin') {
        $sidebarLinks = [
            ['label' => 'Dashboard',      'icon' => 'fa-chart-pie',       'url' => '/admin/dashboard'],
            ['label' => 'My Profile',     'icon' => 'fa-user',            'url' => '/admin/profile'],
            ['label' => 'Manage Users',   'icon' => 'fa-users-gear',      'url' => '/admin/users'],
            ['label' => 'Manage Doctors', 'icon' => 'fa-user-md',         'url' => '/admin/doctors'],
            ['label' => 'Appointments',   'icon' => 'fa-calendar-check',  'url' => '/admin/appointments'],
            ['label' => 'Blog',           'icon' => 'fa-blog',            'url' => '/admin/blog'],
            ['label' => 'Contacts',       'icon' => 'fa-envelope',        'url' => '/admin/contacts'],
            ['label' => 'Notifications',  'icon' => 'fa-bell',            'url' => '/admin/notifications'],
        ];
    }
    ?>

    <aside class="dashboard-sidebar" id="dashboardSidebar">
        <div class="sidebar-brand">
            <img src="<?= asset('images/logo.png') ?>" alt="Althia" height="32">
        </div>

        <div class="sidebar-user">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?= asset($user['avatar']) ?>" alt="Avatar" class="avatar" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
            <?php else: ?>
                <div class="avatar"><?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?></div>
            <?php endif; ?>
            <div class="sidebar-user-info">
                <strong><?= htmlspecialchars(($user['first_name'] ?? 'User') . ' ' . ($user['last_name'] ?? '')) ?></strong>
                <span><?= htmlspecialchars($role ?? 'user') ?></span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <?php foreach ($sidebarLinks as $link): ?>
                <a href="<?= $link['url'] ?>" class="<?= $currentUrl === $link['url'] ? 'active' : '' ?>">
                    <i class="fas <?= $link['icon'] ?>"></i>
                    <?= $link['label'] ?>
                </a>
            <?php endforeach; ?>

            <hr class="sidebar-divider">

            <div class="nav-label">Support</div>
            <a href="/contact">
                <i class="fas fa-headset"></i>
                Help & Support
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="/logout">
                <i class="fas fa-right-from-bracket"></i>
                Logout
            </a>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-layout">
        <main class="main-content">

    <?php else: ?>

    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="/" class="navbar-brand">
                <img src="<?= asset('images/logo.png') ?>" alt="Althia" height="40">
            </a>

            <div class="navbar-menu" id="navMenu">
                <a href="/" class="<?= $currentUrl === '/' ? 'active' : '' ?>">Home</a>
                <a href="/about" class="<?= $currentUrl === '/about' ? 'active' : '' ?>">About us</a>
                <a href="/services" class="<?= $currentUrl === '/services' ? 'active' : '' ?>">Services</a>
                <a href="/experts" class="<?= $currentUrl === '/experts' ? 'active' : '' ?>">Experts</a>
                <a href="/blog" class="<?= str_starts_with($currentUrl, '/blog') ? 'active' : '' ?>">Blog</a>
                <a href="/contact" class="<?= $currentUrl === '/contact' ? 'active' : '' ?>">Contact</a>
            </div>

            <div class="navbar-actions">
                <?php if (\App\Core\Auth::check()): ?>
                    <div class="nav-user">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= asset($user['avatar']) ?>" alt="Avatar" class="avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                        <?php else: ?>
                            <div class="avatar"><?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?></div>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($user['first_name'] ?? 'User') ?></span>
                        <div class="nav-dropdown">
                            <?php if ($role === 'patient'): ?>
                                <a href="/patient/dashboard">Dashboard</a>
                                <a href="/patient/profile">Profile</a>
                                <a href="/patient/appointments">Appointments</a>
                                <a href="/patient/records">Medical Records</a>
                                <a href="/patient/prescriptions">Prescriptions</a>
                                <a href="/patient/messages">Messages</a>
                                <a href="/patient/notifications">Notifications</a>
                            <?php elseif ($role === 'doctor'): ?>
                                <a href="/doctor/dashboard">Dashboard</a>
                                <a href="/doctor/patients">My Patients</a>
                                <a href="/doctor/appointments">Appointments</a>
                                <a href="/doctor/prescriptions">Prescriptions</a>
                                <a href="/doctor/messages">Messages</a>
                                <a href="/doctor/availability">Availability</a>
                                <a href="/doctor/notifications">Notifications</a>
                            <?php elseif ($role === 'admin'): ?>
                                <a href="/admin/dashboard">Dashboard</a>
                                <a href="/admin/users">Manage Users</a>
                                <a href="/admin/doctors">Manage Doctors</a>
                                <a href="/admin/appointments">Appointments</a>
                                <a href="/admin/blog">Blog</a>
                                <a href="/admin/contacts">Contacts</a>
                                <a href="/admin/notifications">Notifications</a>
                            <?php endif; ?>
                            <div class="divider"></div>
                            <a href="/logout">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/login">Log in</a>
                    <a href="/contact" class="btn btn-primary btn-sm">Book Appointment</a>
                <?php endif; ?>
            </div>

            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <main class="main-content">

    <?php endif; ?>
