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

<!-- FILTER SECTION -->
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
    <?php if ($tasks->num_rows == 0): ?>
        <div class="alert alert-info">
            Belum ada judul tugas. Klik tombol "Buat Judul Tugas Baru" untuk memulai.
        </div>
    <?php else: ?>
        <?php while($task = $tasks->fetch_assoc()): ?>
        <div class="card mb-3 task-card" data-task-id="<?php echo $task['id']; ?>">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo htmlspecialchars($task['judul']); ?></h5>
                <div>
                    <small class="text-muted me-3">Dibuat oleh: <?php echo $task['creator']; ?></small>
                    
                    <?php if ($_SESSION['role'] == 'admin' || $task['created_by'] == $user_id): ?>
                        <a href="tasks/edit.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($_SESSION['role'] == 'admin' || $task['created_by'] == $user_id): ?>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTask(<?php echo $task['id']; ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="subtask-list" data-task-id="<?php echo $task['id']; ?>">
                    <p class="text-muted">Loading subtasks...</p>
                </div>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="showAddSubtask(<?php echo $task['id']; ?>)">
                    <i class="bi bi-plus"></i> Tambah Daftar Tugas
                </button>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
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

<!-- Modal for CREATE Subtask -->
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

<!-- INI ADALAH SATU-SATUNYA SCRIPT DI HALAMAN INI -->
<script>
// ============================================
// TUNGGU SAMPAI SELURUH HALAMAN SIAP
// ============================================
window.onload = function() {
    console.log('Window loaded - memastikan semua resource siap');
    
    // Cek apakah jQuery sudah tersedia
    if (typeof jQuery === 'undefined') {
        console.error('jQuery tidak ditemukan!');
        alert('Error: jQuery tidak ditemukan. Refresh halaman.');
        return;
    }
    
    console.log('jQuery version:', jQuery.fn.jquery);
    console.log('Base URL:', baseUrl);
    
    // Load subtasks untuk setiap task
    jQuery('.subtask-list').each(function() {
        var taskId = jQuery(this).data('task-id');
        console.log('Loading subtasks for task:', taskId);
        loadSubtasks(taskId);
    });
    
    // Event listener untuk filter dan search
    jQuery('#filterStatus, #filterPriority, #filterDeadline, #btnSearch').on('change click', function() {
        applyFilters();
    });
    
    jQuery('#searchInput').on('keypress', function(e) {
        if (e.which == 13) {
            applyFilters();
        }
    });
    
    jQuery('#modalSubtask').on('hidden.bs.modal', function () {
        jQuery('#formSubtask')[0].reset();
    });
};

// ============================================
// FUNGSI-FUNGSI
// ============================================
function loadSubtasks(taskId) {
    console.log('AJAX call to load subtasks for task:', taskId);
    
    jQuery.ajax({
        url: baseUrl + '/api/get_subtasks.php',
        data: { task_id: taskId },
        method: 'GET',
        dataType: 'html',
        timeout: 10000,
        beforeSend: function() {
            jQuery('.subtask-list[data-task-id="' + taskId + '"]').html('<p class="text-muted small">⏳ Memuat subtasks...</p>');
        },
        success: function(data) {
            console.log('Subtasks loaded for task', taskId);
            jQuery('.subtask-list[data-task-id="' + taskId + '"]').html(data);
        },
        error: function(xhr, status, error) {
            console.error('Error load subtasks for task', taskId, ':', error);
            console.error('Response:', xhr.responseText);
            
            jQuery('.subtask-list[data-task-id="' + taskId + '"]').html(
                '<p class="text-danger small">❌ Gagal memuat subtasks. Error: ' + error + '</p>' +
                '<button class="btn btn-sm btn-outline-secondary mt-1" onclick="loadSubtasks(' + taskId + ')">🔄 Coba Lagi</button>'
            );
        }
    });
}

function showAddSubtask(taskId) {
    console.log('Show add subtask modal for task:', taskId);
    jQuery('#subtask_task_id').val(taskId);
    jQuery('#modalSubtask').modal('show');
}

function deleteTask(taskId) {
    if(confirm('Yakin ingin menghapus judul tugas ini?')) {
        console.log('Deleting task:', taskId);
        
        jQuery.ajax({
            url: baseUrl + '/api/delete_task.php',
            method: 'POST',
            data: { task_id: taskId },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('✅ Tugas berhasil dihapus!');
                    location.reload();
                } else {
                    alert('❌ Gagal: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('❌ Terjadi kesalahan: ' + error);
            }
        });
    }
}

function deleteSubtask(subtaskId) {
    if(confirm('Yakin ingin menghapus daftar tugas ini?')) {
        console.log('Deleting subtask:', subtaskId);
        
        jQuery.ajax({
            url: baseUrl + '/api/delete_subtask.php',
            method: 'POST',
            data: { subtask_id: subtaskId },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('✅ Daftar tugas berhasil dihapus!');
                    location.reload();
                } else {
                    alert('❌ Gagal: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('❌ Terjadi kesalahan: ' + error);
            }
        });
    }
}

function applyFilters() {
    var status = jQuery('#filterStatus').val();
    var priority = jQuery('#filterPriority').val();
    var deadline = jQuery('#filterDeadline').val();
    var search = jQuery('#searchInput').val();
    
    console.log('Filter applied:', {status, priority, deadline, search});
    alert('Fitur filter akan segera hadir!');
}
</script>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>