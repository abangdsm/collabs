<?php
require_once '../../includes/functions.php';
requireLogin();

$conn = getConnection();
$subtask_id = (int)$_GET['id'];

// Ambil data subtask
$result = $conn->query("
    SELECT s.*, t.judul as task_judul 
    FROM subtasks s
    JOIN tasks t ON s.task_id = t.id
    WHERE s.id = $subtask_id
");
$subtask = $result->fetch_assoc();

if (!$subtask) {
    $_SESSION['error'] = "Subtask tidak ditemukan!";
    header('Location: ' . base_url() . '/modules/dashboard.php');
    exit();
}

// CEK AKSES - Admin boleh, member hanya boleh edit subtask sendiri
if ($_SESSION['role'] != 'admin' && $subtask['created_by'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "Anda tidak berhak mengedit subtask ini!";
    header('Location: ' . base_url() . '/modules/dashboard.php');
    exit();
}

$page_title = 'Edit Daftar Tugas';
include '../../includes/header.php';

// Proses update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul_sub = $conn->real_escape_string($_POST['judul_sub']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi'] ?? '');
    $priority = $conn->real_escape_string($_POST['priority']);
    $deadline = !empty($_POST['deadline']) ? "'" . $conn->real_escape_string($_POST['deadline']) . "'" : "NULL";
    $status = $conn->real_escape_string($_POST['status']);
    
    $sql = "UPDATE subtasks SET 
            judul_sub = '$judul_sub',
            deskripsi = '$deskripsi',
            priority = '$priority',
            deadline = $deadline,
            status = '$status'
            WHERE id = $subtask_id";
    
    if ($conn->query($sql)) {
        logActivity($_SESSION['user_id'], "Mengupdate subtask ID: $subtask_id");
        $_SESSION['success'] = "Daftar tugas berhasil diupdate!";
        header('Location: ' . base_url() . '/modules/dashboard.php');
        exit();
    } else {
        $error = "Gagal mengupdate: " . $conn->error;
    }
}
$conn->close();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Edit Daftar Tugas - <?php echo htmlspecialchars($subtask['task_judul']); ?></h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="judul_sub" class="form-label">Judul Daftar Tugas</label>
                        <input type="text" class="form-control" id="judul_sub" name="judul_sub" 
                               value="<?php echo htmlspecialchars($subtask['judul_sub']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi & Skill yang Dibutuhkan</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo htmlspecialchars($subtask['deskripsi']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="priority" class="form-label">Prioritas</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low" <?php echo $subtask['priority'] == 'low' ? 'selected' : ''; ?>>🟢 Low</option>
                                <option value="medium" <?php echo $subtask['priority'] == 'medium' ? 'selected' : ''; ?>>🟡 Medium</option>
                                <option value="high" <?php echo $subtask['priority'] == 'high' ? 'selected' : ''; ?>>🔴 High</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="deadline" class="form-label">Deadline</label>
                            <input type="date" class="form-control" id="deadline" name="deadline" 
                                   value="<?php echo $subtask['deadline']; ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="proses" <?php echo $subtask['status'] == 'proses' ? 'selected' : ''; ?>>Dalam Proses</option>
                                <option value="selesai" <?php echo $subtask['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                <option value="evaluasi" <?php echo $subtask['status'] == 'evaluasi' ? 'selected' : ''; ?>>Evaluasi</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo base_url(); ?>/modules/dashboard.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>