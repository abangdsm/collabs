<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

date_default_timezone_set('Asia/Jakarta');

$conn = getConnection();
$user_id = $_SESSION['user_id'];

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
    exit();
}

// Query dengan SELECT kolom spesifik dan hitung unread sekaligus
$result = $conn->query("
    SELECT 
        id, message, type, is_read, link, created_at,
        (SELECT COUNT(*) FROM notifications WHERE user_id = $user_id AND is_read = 0) as total_unread
    FROM notifications 
    WHERE user_id = $user_id 
    ORDER BY is_read ASC, created_at DESC 
    LIMIT 15
");

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . $conn->error]);
    $conn->close();
    exit();
}

$notifications = [];
$unread_count = 0;

while ($row = $result->fetch_assoc()) {
    $unread_count = $row['total_unread']; // Ambil dari baris pertama
    $notifications[] = [
        'id' => (int)$row['id'],
        'message' => $row['message'],
        'type' => $row['type'],
        'is_read' => (int)$row['is_read'],
        'link' => $row['link'] ?? '',
        'created_at' => waktuLalu($row['created_at'])
    ];
}

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => (int)$unread_count
]);

$conn->close();
?>