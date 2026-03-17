<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: modules/auth/login.php');
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

function base_url($path = '') {
    $base = 'http://localhost/collabs';
    if (!empty($path)) {
        return $base . '/' . ltrim($path, '/');
    }
    return $base;
}
?>