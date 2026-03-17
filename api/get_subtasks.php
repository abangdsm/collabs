<?php
require_once '../includes/functions.php';
requireLogin();

$conn = getConnection();
$task_id = (int)$_GET['task_id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$subtasks = $conn->query("
    SELECT s.*, u.username as creator 
    FROM subtasks s
    JOIN users u ON s.created_by = u.id
    WHERE s.task_id = $task_id AND s.is_archived = 0
    ORDER BY s.urutan ASC, s.created_at DESC
");

if ($subtasks->num_rows == 0) {
    echo '<p class="text-muted fst-italic">Belum ada daftar tugas</p>';
} else {
    while($sub = $subtasks->fetch_assoc()) {
        // Tentukan class untuk deadline
        $deadline_class = '';
        $deadline_text = '';
        if($sub['deadline']) {
            $today = date('Y-m-d');
            $deadline = $sub['deadline'];
            if($deadline < $today) {
                $deadline_class = 'text-danger fw-bold';
                $deadline_text = '🔴 ' . date('d/m/Y', strtotime($sub['deadline']));
            } elseif($deadline <= date('Y-m-d', strtotime('+1 day'))) {
                $deadline_class = 'text-danger';
                $deadline_text = '⚠️ ' . date('d/m/Y', strtotime($sub['deadline']));
            } elseif($deadline <= date('Y-m-d', strtotime('+3 day'))) {
                $deadline_class = 'text-warning';
                $deadline_text = '⚡ ' . date('d/m/Y', strtotime($sub['deadline']));
            } else {
                $deadline_class = 'text-success';
                $deadline_text = '📅 ' . date('d/m/Y', strtotime($sub['deadline']));
            }
        }
        
        echo '<div class="card mb-2 subtask-item" data-subtask-id="' . $sub['id'] . '">';
        echo '<div class="card-body py-2">';
        echo '<div class="d-flex justify-content-between align-items-start">';
        echo '<div class="flex-grow-1">';
        echo '<div class="d-flex align-items-center">';
        echo '<span class="me-2">☰</span>'; // Handle untuk drag
        echo '<h6 class="mb-1">' . htmlspecialchars($sub['judul_sub']) . '</h6>';
        echo '</div>';
        
        // Tampilkan deskripsi jika ada
        if (!empty($sub['deskripsi'])) {
            echo '<small class="text-muted d-block mb-1">' . nl2br(htmlspecialchars($sub['deskripsi'])) . '</small>';
        }
        
        echo '<div class="d-flex align-items-center gap-2 mt-1">';
        echo '<small class="text-muted">Dibuat: ' . $sub['creator'] . '</small>';
        
        // Badge priority
        $priority_class = $sub['priority'] == 'high' ? 'bg-danger' : ($sub['priority'] == 'medium' ? 'bg-warning text-dark' : 'bg-success');
        echo '<span class="badge ' . $priority_class . '">' . strtoupper($sub['priority']) . '</span>';
        
        // Deadline
        if($sub['deadline']) {
            echo '<small class="' . $deadline_class . '">' . $deadline_text . '</small>';
        }
        
        // Status badge
        $status_class = $sub['status'] == 'proses' ? 'bg-warning text-dark' : ($sub['status'] == 'selesai' ? 'bg-success' : 'bg-danger');
        echo '<span class="badge ' . $status_class . '">' . strtoupper($sub['status']) . '</span>';
        
        echo '</div>'; // tutup d-flex
        
        echo '</div>'; // tutup flex-grow-1
        
        // Tombol aksi
        echo '<div class="d-flex">';
        if ($role == 'admin' || $sub['created_by'] == $user_id) {
            echo '<a href="subtasks/edit.php?id=' . $sub['id'] . '" class="btn btn-sm btn-outline-primary me-1">';
            echo '<i class="bi bi-pencil"></i>';
            echo '</a>';
            echo '<button class="btn btn-sm btn-outline-danger" onclick="deleteSubtask(' . $sub['id'] . ')">';
            echo '<i class="bi bi-trash"></i>';
            echo '</button>';
        }
        echo '</div>'; // tutup tombol aksi
        
        echo '</div>'; // tutup d-flex utama
        echo '</div>'; // tutup card-body
        echo '</div>'; // tutup card
    }
}

$conn->close();
?>