<?php
// Mulai session hanya jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . base_url('/modules/auth/login.php'));
        exit();
    }
}

// Ambil data user yang login
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $conn = getConnection();
    $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    return $result->fetch_assoc();
}

// Catat activity log
function logActivity($user_id, $action) {
    $conn = getConnection();
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $action, $ip);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Buat notifikasi
function createNotification($user_id, $message, $type = 'info') {
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $message, $type);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Cek dan update deadline otomatis
function checkDeadlines() {
    $conn = getConnection();
    $today = date('Y-m-d');
    
    // Update tugas yang lewat deadline menjadi evaluasi
    $conn->query("
        UPDATE subtasks 
        SET status = 'evaluasi' 
        WHERE deadline < '$today' 
        AND status != 'selesai'
        AND is_archived = 0
    ");
    
    $conn->close();
}

// Base URL function - PERBAIKAN DISINI
function base_url($path = '') {
    // Deteksi protocol (http atau https)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    
    // Dapatkan host (localhost atau domain)
    $host = $_SERVER['HTTP_HOST'];
    
    // Dapatkan path ke folder project
    $script_name = $_SERVER['SCRIPT_NAME'];
    $folder = str_replace('\\', '/', dirname($script_name));
    
    // Hapus 'modules/auth' atau bagian lain dari path untuk mendapatkan base folder
    $base_folder = preg_replace('#/(modules|auth|tasks|api)/.*$#', '', $folder);
    
    $base_url = $protocol . $host . $base_folder;
    
    if (!empty($path)) {
        // Pastikan tidak ada double slash
        return rtrim($base_url, '/') . '/' . ltrim($path, '/');
    }
    
    return $base_url;
}
?>