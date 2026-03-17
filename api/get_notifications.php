<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Set timezone ke Jakarta
date_default_timezone_set('Asia/Jakarta');

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Cek koneksi
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal'
    ]);
    exit();
}

// Ambil 15 notifikasi terbaru
$result = $conn->query("
    SELECT * FROM notifications 
    WHERE user_id = $user_id 
    ORDER BY is_read ASC, created_at DESC 
    LIMIT 15
");

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Query error: ' . $conn->error
    ]);
    $conn->close();
    exit();
}

$notifications = [];
while ($row = $result->fetch_assoc()) {
    // HAPUS EMOJI DARI SINI - SERAHIN KE JAVASCRIPT
    // Cuma kirim type dan message mentah
    
    $notifications[] = [
        'id' => (int)$row['id'],
        'message' => $row['message'], // Teks murni tanpa emoji
        'type' => $row['type'], // 'info', 'success', 'warning', 'danger'
        'is_read' => (int)$row['is_read'],
        'link' => $row['link'] ?? '',
        'created_at' => waktuLalu($row['created_at'])
    ];
}

// Hitung notifikasi yang belum dibaca
$unread = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = $user_id AND is_read = 0");
$unread_count = 0;
if ($unread) {
    $row = $unread->fetch_assoc();
    $unread_count = (int)$row['total'];
}

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => $unread_count
]);

$conn->close();
?>