<?php
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Dashboard';
include '../includes/header.php';

// Cek deadline otomatis
checkDeadlines();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Ambil semua tasks dengan subtasks-nya
$tasks = $conn->query("
    SELECT t.*, u.username as creator 
    FROM tasks t
    JOIN users u ON t.created_by = u.id
    ORDER BY t.created_at DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Dashboard</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTask">
        <i class="bi bi-plus-lg"></i> Buat Judul Tugas Baru
    </button>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-3">
                <select class="form-select" id="filterStatus">
                    <option value="">Semua Status</option>
                    <option value="proses">Dalam Proses</option>
                    <option value="selesai">Selesai</option>
                    <option value="evaluasi">Evaluasi</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterPriority">
                    <option value="">Semua Prioritas</option>
                    <option value="high">🔴 High</option>
                    <option value="medium">🟡 Medium</option>
                    <option value="low">🟢 Low</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterDeadline">
                    <option value="">Semua Deadline</option>
                    <option value="today">Hari ini</option>
                    <option value="tomorrow">Besok</option>
                    <option value="week">Minggu ini</option>
                    <option value="overdue">Sudah lewat</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Cari tugas..." id="searchInput">
                    <button class="btn btn-primary" type="button" id="btnSearch">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tasks Container -->
<div id="tasks-container">
    <?php while($task = $tasks->fetch_assoc()): ?>
    <div class="card mb-3 task-card" data-task-id="<?php echo $task['id']; ?>">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?php echo htmlspecialchars($task['judul']); ?></h5>
            <div>
                <small class="text-muted me-3">Dibuat oleh: <?php echo $task['creator']; ?></small>
                <?php if($_SESSION['role'] == 'admin'): ?>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTask(<?php echo $task['id']; ?>)">
                    <i class="bi bi-trash"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <!-- Subtasks will be loaded here via AJAX -->
            <div class="subtask-list" data-task-id="<?php echo $task['id']; ?>">
                <p class="text-muted">Loading subtasks...</p>
            </div>
            <button class="btn btn-sm btn-outline-primary mt-2" onclick="showAddSubtask(<?php echo $task['id']; ?>)">
                <i class="bi bi-plus"></i> Tambah Daftar Tugas
            </button>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<!-- Modal for Task -->
<div class="modal fade" id="modalTask" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Judul Tugas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTask" method="POST" action="tasks/create.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Tugas</label>
                        <input type="text" class="form-control" id="judul" name="judul" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Load subtasks for each task
$(document).ready(function() {
    $('.subtask-list').each(function() {
        var taskId = $(this).data('task-id');
        loadSubtasks(taskId);
    });
});

function loadSubtasks(taskId) {
    $.ajax({
        url: '../api/get_subtasks.php',
        data: { task_id: taskId },
        success: function(data) {
            $('.subtask-list[data-task-id="' + taskId + '"]').html(data);
        }
    });
}

function showAddSubtask(taskId) {
    // Implementasi modal tambah subtask
    alert('Fitur tambah subtask untuk task ' + taskId + ' (akan segera diimplementasi)');
}

function deleteTask(taskId) {
    if(confirm('Yakin ingin menghapus judul tugas ini?')) {
        $.ajax({
            url: '../api/delete_task.php',
            method: 'POST',
            data: { task_id: taskId },
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert('Gagal menghapus tugas');
                }
            }
        });
    }
}
</script>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>