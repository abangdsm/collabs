<?php
require_once '../includes/functions.php';
requireLogin();

$conn = getConnection();
$task_id = (int)$_GET['task_id'];

$subtasks = $conn->query("
    SELECT s.*, u.username as creator 
    FROM subtasks s
    JOIN users u ON s.created_by = u.id
    WHERE s.task_id = $task_id AND s.is_archived = 0
    ORDER BY s.urutan ASC, s.created_at DESC
");

if ($subtasks->num_rows == 0) {
    echo '<p class="text-muted">Belum ada daftar tugas</p>';
} else {
    while($sub = $subtasks->fetch_assoc()) {
        // Tentukan class untuk deadline
        $deadline_class = '';
        if($sub['deadline']) {
            $today = date('Y-m-d');
            $deadline = $sub['deadline'];
            if($deadline < $today) {
                $deadline_class = 'deadline-danger';
            } elseif($deadline <= date('Y-m-d', strtotime('+1 day'))) {
                $deadline_class = 'deadline-danger';
            } elseif($deadline <= date('Y-m-d', strtotime('+3 day'))) {
                $deadline_class = 'deadline-warning';
            } else {
                $deadline_class = 'deadline-normal';
            }
        }
        
        echo '<div class="card mb-2 subtask-item" data-subtask-id="' . $sub['id'] . '">';
        echo '<div class="card-body py-2">';
        echo '<div class="d-flex justify-content-between align-items-start">';
        echo '<div>';
        echo '<h6 class="mb-1">' . htmlspecialchars($sub['judul_sub']) . '</h6>';
        echo '<small class="text-muted">Dibuat: ' . $sub['creator'] . '</small>';
        echo '</div>';
        echo '<div class="d-flex align-items-center">';
        echo '<span class="badge bg-' . ($sub['priority']=='high'?'danger':($sub['priority']=='medium'?'warning':'success')) . ' me-2">';
        echo $sub['priority'];
        echo '</span>';
        if($sub['deadline']) {
            echo '<small class="' . $deadline_class . ' me-2">' . date('d/m/Y', strtotime($sub['deadline'])) . '</small>';
        }
        echo '<span class="badge bg-' . ($sub['status']=='proses'?'warning':($sub['status']=='selesai'?'success':'danger')) . '">';
        echo $sub['status'];
        echo '</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}

$conn->close();
?>