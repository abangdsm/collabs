<?php
date_default_timezone_set('Asia/Jakarta');

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
        header('Location: ' . base_url() . '/modules/auth/login.php');
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
/**
 * Buat notifikasi untuk semua anggota tim kecuali pembuat
 * @param string $message Pesan notifikasi
 * @param string $type Tipe notifikasi (info, success, warning, danger)
 * @param int $exclude_user_id User yang TIDAK perlu dapat notifikasi (biasanya pembuat)
 * @param string $link URL link (opsional)
 */
function notifyAllMembers($message, $type = 'info', $exclude_user_id = null, $link = '') {
    $conn = getConnection();
    
    $message = $conn->real_escape_string($message);
    $type = $conn->real_escape_string($type);
    $link = $conn->real_escape_string($link);
    
    $sql = "INSERT INTO notifications (user_id, message, type, link) 
            SELECT id, '$message', '$type', '$link' FROM users";
    
    if ($exclude_user_id) {
        $sql .= " WHERE id != $exclude_user_id";
    }
    
    $conn->query($sql);
    $conn->close();
}

/**
 * Buat notifikasi untuk user tertentu
 */
function notifyUser($user_id, $message, $type = 'info', $link = '') {
    $conn = getConnection();
    
    $message = $conn->real_escape_string($message);
    $type = $conn->real_escape_string($type);
    $link = $conn->real_escape_string($link);
    
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $message, $type, $link);
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

// Base URL function - VERSI SEDERHANA
function base_url() {
    // Deteksi protocol (http atau https)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    
    // Dapatkan host (localhost atau domain)
    $host = $_SERVER['HTTP_HOST'];
    
    // Untuk Laragon, folder project ada di /collabs/
    return $protocol . $host . '/collabs';
}

/**
 * Format waktu lalu (misal: "5 menit lalu")
 */
function waktuLalu($datetime) {
    if (!$datetime) return '';
    
    $waktu = strtotime($datetime);
    $sekarang = time();
    $diff = $sekarang - $waktu;
    
    if ($diff < 60) return "baru saja";
    if ($diff < 3600) return floor($diff/60) . " menit lalu";
    if ($diff < 86400) return floor($diff/3600) . " jam lalu";
    if ($diff < 259200) return floor($diff/86400) . " hari lalu";
    return date('d/m/Y H:i', $waktu);
}
?>