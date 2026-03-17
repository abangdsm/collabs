<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Ambil notifikasi yang belum dibaca
$result = $conn->query("
    SELECT * FROM notifications 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC 
    LIMIT 10
");

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['id'],
        'message' => $row['message'],
        'type' => $row['type'],
        'created_at' => date('d/m/Y H:i', strtotime($row['created_at']))
    ];
}

// Hitung notifikasi yang belum dibaca
$unread = $conn->query("
    SELECT COUNT(*) as total 
    FROM notifications 
    WHERE user_id = $user_id AND is_read = 0
");
$unread_count = $unread->fetch_assoc()['total'];

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => (int)$unread_count
]);

$conn->close();
?>