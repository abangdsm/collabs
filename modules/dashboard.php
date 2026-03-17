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

$base_url = base_url();
?>

<!-- Tampilkan pesan sukses/error -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Dashboard</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTask">
        <i class="bi bi-plus-lg"></i> Buat Judul Tugas Baru
    </button>
</div>

<!-- Filter Section (sama seperti sebelumnya) -->
<div class="card mb-4">
    <!-- ... filter section tetap sama ... -->
</div>

<!-- Tasks Container -->
<div id="tasks-container">
    <?php while($task = $tasks->fetch_assoc()): ?>
    <div class="card mb-3 task-card" data-task-id="<?php echo $task['id']; ?>">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?php echo htmlspecialchars($task['judul']); ?></h5>
            <div>
                <small class="text-muted me-3">Dibuat oleh: <?php echo $task['creator']; ?></small>
                
                <!-- Tampilkan tombol Edit untuk admin ATAU pembuat tugas -->
                <?php if ($_SESSION['role'] == 'admin' || $task['created_by'] == $user_id): ?>
                    <a href="tasks/edit.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                <?php endif; ?>
                
                <!-- Tombol Delete hanya untuk admin ATAU pembuat tugas -->
                <?php if ($_SESSION['role'] == 'admin' || $task['created_by'] == $user_id): ?>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTask(<?php echo $task['id']; ?>)">
                        <i class="bi bi-trash"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <!-- Subtasks akan diload via AJAX -->
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

<!-- Modal for CREATE Task -->
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
                        <input type="text" class="form-control" id="judul" name="judul" required 
                               placeholder="Contoh: Pengembangan Fitur A">
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

<!-- Modal for CREATE Subtask (akan diisi nanti) -->
<div class="modal fade" id="modalSubtask" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Daftar Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSubtask" method="POST" action="subtasks/create.php">
                <div class="modal-body">
                    <input type="hidden" id="subtask_task_id" name="task_id">
                    
                    <div class="mb-3">
                        <label for="subtask_judul" class="form-label">Judul Daftar Tugas</label>
                        <input type="text" class="form-control" id="subtask_judul" name="judul_sub" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subtask_deskripsi" class="form-label">Deskripsi & Skill yang Dibutuhkan</label>
                        <textarea class="form-control" id="subtask_deskripsi" name="deskripsi" rows="3"></textarea>
                        <small class="text-muted">Jelaskan detail tugas dan skill yang diperlukan</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="subtask_priority" class="form-label">Prioritas</label>
                            <select class="form-select" id="subtask_priority" name="priority">
                                <option value="low">🟢 Low</option>
                                <option value="medium" selected>🟡 Medium</option>
                                <option value="high">🔴 High</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="subtask_deadline" class="form-label">Deadline</label>
                            <input type="date" class="form-control" id="subtask_deadline" name="deadline">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="subtask_status" class="form-label">Status</label>
                            <select class="form-select" id="subtask_status" name="status">
                                <option value="proses" selected>Dalam Proses</option>
                                <option value="selesai">Selesai</option>
                                <option value="evaluasi">Evaluasi</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subtask_link" class="form-label">Link Eksternal (Opsional)</label>
                        <input type="url" class="form-control" id="subtask_link" name="link" 
                               placeholder="https://drive.google.com/...">
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
        url: baseUrl + '/api/get_subtasks.php',
        data: { task_id: taskId },
        success: function(data) {
            $('.subtask-list[data-task-id="' + taskId + '"]').html(data);
        }
    });
}

function showAddSubtask(taskId) {
    $('#subtask_task_id').val(taskId);
    $('#modalSubtask').modal('show');
}

function deleteTask(taskId) {
    if(confirm('Yakin ingin menghapus judul tugas ini? Semua daftar tugas di dalamnya juga akan ikut terhapus!')) {
        $.ajax({
            url: baseUrl + '/api/delete_task.php',
            method: 'POST',
            data: { task_id: taskId },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert('Gagal menghapus tugas: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat menghapus');
            }
        });
    }
}

// Reset form modal ketika ditutup
$('#modalSubtask').on('hidden.bs.modal', function () {
    $('#formSubtask')[0].reset();
});
</script>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>