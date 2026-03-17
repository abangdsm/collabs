<?php
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getConnection();
    
    $judul = $conn->real_escape_string($_POST['judul']);
    $created_by = $_SESSION['user_id'];
    
    $sql = "INSERT INTO tasks (judul, created_by) VALUES ('$judul', $created_by)";
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = "Judul tugas berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan judul tugas: " . $conn->error;
    }
    
    $conn->close();
}

// Redirect back to dashboard
header('Location: ' . base_url() . '/modules/dashboard.php');
exit();
?>