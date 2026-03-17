<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Ambil 15 notifikasi terbaru
$result = $conn->query("
    SELECT * FROM notifications 
    WHERE user_id = $user_id 
    ORDER BY is_read ASC, created_at DESC 
    LIMIT 15
");

$notifications = [];
while ($row = $result->fetch_assoc()) {
    // Format pesan dengan emoji berdasarkan type
    $emoji = '';
    switch ($row['type']) {
        case 'success': $emoji = '✅ '; break;
        case 'warning': $emoji = '⚠️ '; break;
        case 'danger': $emoji = '🔴 '; break;
        default: $emoji = '📢 '; break;
    }
    
    $notifications[] = [
        'id' => $row['id'],
        'message' => $emoji . $row['message'],
        'type' => $row['type'],
        'is_read' => (int)$row['is_read'],
        'link' => $row['link'],
        'created_at' => waktuLalu($row['created_at'])
    ];
}

// Hitung notifikasi yang belum dibaca
$unread = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = $user_id AND is_read = 0");
$unread_count = $unread->fetch_assoc()['total'];

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => (int)$unread_count
]);

$conn->close();

function waktuLalu($datetime) {
    $waktu = strtotime($datetime);
    $sekarang = time();
    $diff = $sekarang - $waktu;
    
    if ($diff < 60) return "baru saja";
    if ($diff < 3600) return floor($diff/60) . " menit lalu";
    if ($diff < 86400) return floor($diff/3600) . " jam lalu";
    if ($diff < 259200) return floor($diff/86400) . " hari lalu";
    return date('d/m/Y', $waktu);
}
?>