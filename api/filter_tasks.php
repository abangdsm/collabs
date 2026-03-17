<?php
require_once '../includes/functions.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

$status = isset($_GET['status']) ? $_GET['status'] : '';
$priority = isset($_GET['priority']) ? $_GET['priority'] : '';
$deadline = isset($_GET['deadline']) ? $_GET['deadline'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query untuk subtasks dengan filter
$subtask_conditions = [];
if (!empty($status)) {
    $subtask_conditions[] = "s.status = '$status'";
}
if (!empty($priority)) {
    $subtask_conditions[] = "s.priority = '$priority'";
}
if (!empty($deadline)) {
    $today = date('Y-m-d');
    switch($deadline) {
        case 'today':
            $subtask_conditions[] = "s.deadline = '$today'";
            break;
        case 'tomorrow':
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $subtask_conditions[] = "s.deadline = '$tomorrow'";
            break;
        case 'week':
            $nextWeek = date('Y-m-d', strtotime('+7 days'));
            $subtask_conditions[] = "s.deadline BETWEEN '$today' AND '$nextWeek'";
            break;
        case 'overdue':
            $subtask_conditions[] = "s.deadline < '$today' AND s.status != 'selesai'";
            break;
    }
}
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $subtask_conditions[] = "(s.judul_sub LIKE '%$search%' OR s.deskripsi LIKE '%$search%' OR t.judul LIKE '%$search%')";
}

// Cari subtasks yang sesuai filter
$subtask_query = "
    SELECT DISTINCT s.task_id 
    FROM subtasks s
    JOIN tasks t ON s.task_id = t.id
    WHERE (s.is_archived = 0 OR s.is_archived IS NULL)
";

if (!empty($subtask_conditions)) {
    $subtask_query .= " AND " . implode(" AND ", $subtask_conditions);
}

$filtered_task_ids = [];
$filtered_tasks = $conn->query($subtask_query);
if ($filtered_tasks) {
    while ($row = $filtered_tasks->fetch_assoc()) {
        $filtered_task_ids[] = $row['task_id'];
    }
}

// Query tasks - hanya tampilkan tasks yang punya subtasks sesuai filter
$task_query = "
    SELECT t.*, u.username as creator 
    FROM tasks t
    JOIN users u ON t.created_by = u.id
";

if (!empty($filtered_task_ids)) {
    $task_ids_str = implode(',', $filtered_task_ids);
    $task_query .= " WHERE t.id IN ($task_ids_str)";
} else if (!empty($status) || !empty($priority) || !empty($deadline) || !empty($search)) {
    // Jika ada filter tapi tidak ada subtasks yang cocok, tampilkan pesan kosong
    $task_query .= " WHERE 1=0"; // Tidak ada hasil
}

$task_query .= " ORDER BY t.created_at DESC";

$tasks = $conn->query($task_query);

$output = '';

if ($tasks->num_rows == 0) {
    $output .= '<div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Tidak ada tugas yang sesuai dengan filter.
            </div>';
} else {
    while($task = $tasks->fetch_assoc()) {
        $output .= '<div class="card mb-3 task-card shadow-sm" data-task-id="' . $task['id'] . '">';
        $output .= '<div class="card-header bg-light d-flex justify-content-between align-items-center">';
        $output .= '<h5 class="mb-0"><i class="bi bi-folder me-2 text-primary"></i>' . htmlspecialchars($task['judul']) . '</h5>';
        $output .= '<div>';
        $output .= '<small class="text-muted me-3"><i class="bi bi-person-circle me-1"></i>' . $task['creator'] . '</small>';
        
        if ($_SESSION['role'] == 'admin' || $task['created_by'] == $user_id) {
            $output .= '<a href="tasks/edit.php?id=' . $task['id'] . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>';
            $output .= '<button class="btn btn-sm btn-outline-danger" onclick="deleteTask(' . $task['id'] . ')"><i class="bi bi-trash"></i></button>';
        }
        
        $output .= '</div></div>';
        $output .= '<div class="card-body">';
        $output .= '<div class="subtask-list" data-task-id="' . $task['id'] . '">';
        $output .= '<div class="text-center text-muted py-3"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Memuat daftar tugas...</div>';
        $output .= '</div>';
        $output .= '<button class="btn btn-sm btn-outline-dark mt-3" onclick="showAddSubtask(' . $task['id'] . ')"><i class="bi bi-plus-circle me-1"></i> Tambah Daftar Tugas</button>';
        $output .= '</div></div>';
    }
}

// Hitung total subtasks yang cocok untuk notifikasi
$count_query = "
    SELECT COUNT(*) as total 
    FROM subtasks s
    JOIN tasks t ON s.task_id = t.id
    WHERE (s.is_archived = 0 OR s.is_archived IS NULL)
";

if (!empty($subtask_conditions)) {
    $count_query .= " AND " . implode(" AND ", $subtask_conditions);
}

$count_result = $conn->query($count_query);
$total_count = $count_result->fetch_assoc()['total'];

$conn->close();

header('Content-Type: application/json');
echo json_encode([
    'tasks_html' => $output,
    'subtasks_count' => (int)$total_count
]);
?>