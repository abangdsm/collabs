<?php
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getConnection();
    
    $task_id = (int)$_POST['task_id'];
    $judul_sub = $conn->real_escape_string($_POST['judul_sub']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi'] ?? '');
    $priority = $conn->real_escape_string($_POST['priority'] ?? 'medium');
    $deadline = !empty($_POST['deadline']) ? "'" . $conn->real_escape_string($_POST['deadline']) . "'" : "NULL";
    $status = $conn->real_escape_string($_POST['status'] ?? 'proses');
    $created_by = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    // Ambil judul task untuk keperluan notifikasi
    $task_result = $conn->query("SELECT judul FROM tasks WHERE id = $task_id");
    $task_data = $task_result->fetch_assoc();
    $task_judul = $task_data['judul'];
    
    // Cek urutan terakhir
    $result = $conn->query("SELECT MAX(urutan) as max_urutan FROM subtasks WHERE task_id = $task_id");
    $row = $result->fetch_assoc();
    $urutan = ($row['max_urutan'] ?? 0) + 1;
    
    $sql = "INSERT INTO subtasks (task_id, judul_sub, deskripsi, priority, deadline, status, created_by, urutan) 
            VALUES ($task_id, '$judul_sub', '$deskripsi', '$priority', $deadline, '$status', $created_by, $urutan)";
    
    if ($conn->query($sql)) {
        $subtask_id = $conn->insert_id;
        
        // Catat activity log
        logActivity($created_by, "Menambahkan subtask ID: $subtask_id ke task: $task_judul");
        
        // NOTIFIKASI 1: Untuk semua anggota (kecuali pembuat)
        $notif_message = "✅ $username menambahkan tugas baru: \"$judul_sub\" di project \"$task_judul\"";
        notifyAllMembers($notif_message, 'success', $created_by, base_url() . "/modules/dashboard.php#subtask-$subtask_id");
        
        // NOTIFIKASI 2: Jika ada deadline dan mepet, kasih warning khusus
        if (!empty($_POST['deadline'])) {
            $deadline_date = $_POST['deadline'];
            $today = date('Y-m-d');
            $diff = (strtotime($deadline_date) - strtotime($today)) / 86400; // selisih hari
            
            if ($diff <= 2) {
                $warning_message = "⚠️ Deadline tugas \"$judul_sub\" tinggal $diff hari lagi!";
                notifyAllMembers($warning_message, 'warning', $created_by, base_url() . "/modules/dashboard.php#subtask-$subtask_id");
            }
        }
        
        $_SESSION['success'] = "Daftar tugas berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan: " . $conn->error;
    }
    
    $conn->close();
}

header('Location: ' . base_url() . '/modules/dashboard.php');
exit();
?>