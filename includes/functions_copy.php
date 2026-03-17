<?php
/**
 * Collabs - Functions.php
 * Version: 1.0.0
 * Untuk Production Hosting
 */

// Set timezone default Indonesia
date_default_timezone_set('Asia/Jakarta');

// Buat folder logs jika belum ada (untuk hosting)
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Error reporting - Bedakan Local dan Production
if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) {
    // Mode Development (Local)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/dev_errors.log');
} else {
    // Mode Production (Hosting)
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

// Mulai session hanya jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load konfigurasi database
require_once __DIR__ . '/../config/database.php';

/**
 * Cek apakah user sudah login
 * @return bool
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Redirect jika belum login
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ' . base_url() . '/modules/auth/login.php');
        exit();
    }
}

/**
 * Ambil data user yang sedang login
 * @return array|null
 */
function getCurrentUser()
{
    if (!isLoggedIn()) return null;

    $conn = getConnection();
    $user_id = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Catat activity log user
 * @param int $user_id
 * @param string $action
 */
function logActivity($user_id, $action)
{
    $conn = getConnection();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $ip, $user_agent);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

/**
 * Buat notifikasi untuk semua anggota tim
 * @param string $message Pesan notifikasi
 * @param string $type Tipe (info, success, warning, danger)
 * @param int|null $exclude_user_id User yang dikecualikan
 * @param string $link URL link
 */
function notifyAllMembers($message, $type = 'info', $exclude_user_id = null, $link = '')
{
    $conn = getConnection();

    $message = $conn->real_escape_string($message);
    $type = $conn->real_escape_string($type);
    $link = $conn->real_escape_string($link);

    $sql = "INSERT INTO notifications (user_id, message, type, link) 
            SELECT id, '$message', '$type', '$link' FROM users";

    if ($exclude_user_id) {
        $exclude_user_id = (int)$exclude_user_id;
        $sql .= " WHERE id != $exclude_user_id";
    }

    $conn->query($sql);
    $conn->close();
}

/**
 * Buat notifikasi untuk user tertentu
 * @param int $user_id
 * @param string $message
 * @param string $type
 * @param string $link
 */
function notifyUser($user_id, $message, $type = 'info', $link = '')
{
    $conn = getConnection();

    $message = $conn->real_escape_string($message);
    $type = $conn->real_escape_string($type);
    $link = $conn->real_escape_string($link);
    $user_id = (int)$user_id;

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $message, $type, $link);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

/**
 * Cek dan update deadline otomatis
 * Tugas yang lewat deadline otomatis jadi Evaluasi
 */
function checkDeadlines()
{
    $conn = getConnection();
    $today = date('Y-m-d');
    
    // Update tugas yang lewat deadline menjadi evaluasi
    $conn->query("
        UPDATE subtasks 
        SET status = 'evaluasi' 
        WHERE deadline < '$today' 
        AND status != 'selesai'
        AND (is_archived = 0 OR is_archived IS NULL)
    ");

    // Log untuk monitoring (opsional)
    $affected = $conn->affected_rows;
    if ($affected > 0) {
        error_log("checkDeadlines: $affected tugas diubah statusnya jadi evaluasi");
    }

    $conn->close();
}

/**
 * Base URL function - Otomatis deteksi environment
 * @param string $path Path tambahan (opsional)
 * @return string URL lengkap
 */
function base_url($path = '')
{
    // Deteksi protocol (http atau https)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    
    // Dapatkan host (localhost atau domain)
    $host = $_SERVER['HTTP_HOST'];
    
    // Dapatkan path folder project
    $script_name = $_SERVER['SCRIPT_NAME'];
    $base_folder = dirname($script_name);
    
    // Handle root directory
    if ($base_folder == '/' || $base_folder == '\\') {
        $base_folder = '';
    }
    
    // Build base URL
    $base_url = $protocol . $host . $base_folder;
    
    // Tambahkan path jika ada
    if (!empty($path)) {
        // Pastikan tidak ada double slash
        return rtrim($base_url, '/') . '/' . ltrim($path, '/');
    }
    
    return $base_url;
}

/**
 * Format waktu lalu (misal: "5 menit lalu")
 * @param string $datetime
 * @return string
 */
function waktuLalu($datetime)
{
    if (!$datetime || $datetime == '0000-00-00 00:00:00') return '';
    
    $waktu = strtotime($datetime);
    if (!$waktu) return '';
    
    $sekarang = time();
    $diff = $sekarang - $waktu;
    
    if ($diff < 60) return "baru saja";
    if ($diff < 3600) return floor($diff / 60) . " menit lalu";
    if ($diff < 86400) return floor($diff / 3600) . " jam lalu";
    if ($diff < 259200) return floor($diff / 86400) . " hari lalu";
    
    return date('d/m/Y H:i', $waktu);
}

/**
 * Generate CSRF token untuk keamanan form
 * @return string
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validasi CSRF token
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitasi input untuk keamanan
 * @param string $data
 * @return string
 */
function sanitize($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Redirect dengan pesan flash
 * @param string $url
 * @param string $message
 * @param string $type
 */
function redirectWithMessage($url, $message, $type = 'success')
{
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
    header('Location: ' . base_url($url));
    exit();
}

/**
 * Tampilkan pesan flash jika ada
 */
function displayFlashMessage()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        $type = $flash['type'];
        $message = $flash['message'];
        
        echo "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        
        unset($_SESSION['flash']);
    }
}

/**
 * Cek apakah user adalah admin
 * @return bool
 */
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

/**
 * Dapatkan semua user (untuk admin)
 * @return mysqli_result|false
 */
function getAllUsers()
{
    $conn = getConnection();
    $result = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
    $conn->close();
    return $result;
}

/**
 * Log error ke file (untuk debugging)
 * @param string $message
 * @param array $context
 */
function logError($message, $context = [])
{
    $log = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if (!empty($context)) {
        $log .= ' - ' . json_encode($context);
    }
    error_log($log);
}
?>