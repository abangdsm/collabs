<?php
session_start();

// Catat activity log sebelum logout
if (isset($_SESSION['user_id'])) {
    require_once '../../includes/functions.php';
    logActivity($_SESSION['user_id'], 'Logout');
}

// Hapus semua session
session_destroy();

// Redirect ke halaman login - PASTIKAN PAKAI BASE_URL
header('Location: ' . base_url() . '/modules/auth/login.php');
exit();
?>