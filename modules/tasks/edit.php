<?php
require_once '../../includes/functions.php';
requireLogin();

$conn = getConnection();
$task_id = (int)$_GET['id'];

// Ambil data task
$result = $conn->query("SELECT * FROM tasks WHERE id = $task_id");
$task = $result->fetch_assoc();

// CEK AKSES - Admin boleh, member hanya boleh edit tugas sendiri
if (!$task) {
    $_SESSION['error'] = "Tugas tidak ditemukan!";
    header('Location: ' . base_url() . '/modules/dashboard.php');
    exit();
}

// Kalau bukan admin dan bukan pembuat tugas, TOLAK!
if ($_SESSION['role'] != 'admin' && $task['created_by'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "Anda tidak berhak mengedit tugas ini!";
    header('Location: ' . base_url() . '/modules/dashboard.php');
    exit();
}

$page_title = 'Edit Judul Tugas';
include '../../includes/header.php';

// Proses update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $conn->real_escape_string($_POST['judul']);
    
    $sql = "UPDATE tasks SET judul = '$judul' WHERE id = $task_id";
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = "Judul tugas berhasil diupdate!";
        header('Location: ' . base_url() . '/modules/dashboard.php');
        exit();
    } else {
        $error = "Gagal mengupdate: " . $conn->error;
    }
}
$conn->close();
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Edit Judul Tugas</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Tugas</label>
                        <input type="text" class="form-control" id="judul" name="judul" 
                               value="<?php echo htmlspecialchars($task['judul']); ?>" required>
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