<?php
require_once 'functions.php';
$isLoggedIn = isLoggedIn();
$base_url = base_url(); 
?><!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collabs - <?php echo $page_title ?? 'Platform Kolaborasi'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
</head>

<body>
    <?php if ($isLoggedIn): ?>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="<?php echo $base_url; ?>/modules/dashboard.php">
                    <i class="bi bi-check2-square me-1"></i> Collabs
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>/modules/dashboard.php">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>/modules/archive.php">
                                <i class="bi bi-archive"></i> Arsip
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <!-- Notifikasi Dropdown - BADGE LEBIH DEKAT -->
                        <li class="nav-item dropdown mx-1">
                            <a class="nav-link dropdown-toggle position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="position-absolute badge rounded-pill bg-danger" id="notif-badge" 
                                      style="display: none; font-size: 0.6rem; top: 2px; right: 2px; padding: 0.2rem 0.4rem;">
                                    0
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg" id="notif-menu" style="width: 380px; padding: 0; border: none; border-radius: 8px;">
                                <li>
                                    <div class="bg-light px-3 py-2 d-flex justify-content-between align-items-center" style="border-radius: 8px 8px 0 0;">
                                        <h6 class="mb-0"><i class="bi bi-bell me-2"></i>Notifikasi</h6>
                                        <a href="<?php echo $base_url; ?>/modules/notifications/index.php" class="text-primary small" style="text-decoration: none;">
                                            Lihat Semua <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </li>
                                <li>
                                    <div id="notif-content" style="max-height: 400px; overflow-y: auto; min-height: 100px;">
                                        <div class="text-center text-muted py-3">
                                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                            Memuat notifikasi...
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i> 
                                <span class="d-none d-lg-inline"><?php echo $_SESSION['username'] ?? 'User'; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base_url; ?>/modules/notifications/index.php">
                                        <i class="bi bi-bell me-2"></i> Semua Notifikasi
                                        <?php 
                                        // Hitung notifikasi belum dibaca untuk badge di menu
                                        if ($isLoggedIn) {
                                            $conn = getConnection();
                                            $user_id = $_SESSION['user_id'];
                                            $unread = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = $user_id AND is_read = 0")->fetch_assoc()['total'];
                                            if ($unread > 0) {
                                                echo '<span class="badge bg-primary ms-2">' . $unread . '</span>';
                                            }
                                            $conn->close();
                                        }
                                        ?>
                                    </a>
                                </li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Profil</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Pengaturan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo $base_url; ?>/modules/auth/logout.php">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>
    
    <!-- Main Content Container -->
    <div class="container mt-4">
        <!-- Pesan flash akan muncul di sini -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>