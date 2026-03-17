<?php
require_once '../../includes/functions.php';
requireLogin();

// Hanya admin yang boleh akses halaman ini
if ($_SESSION['role'] != 'admin') {
    $_SESSION['error'] = "Anda tidak berhak mengakses halaman ini!";
    header('Location: ' . base_url() . '/modules/dashboard.php');
    exit();
}

$page_title = 'Manajemen User';
include '../../includes/header.php';

$conn = getConnection();

// Proses update role
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = $conn->real_escape_string($_POST['role']);
    
    // Cegah admin mengubah role dirinya sendiri
    if ($user_id != $_SESSION['user_id']) {
        $conn->query("UPDATE users SET role = '$role' WHERE id = $user_id");
        $_SESSION['success'] = "Role user berhasil diupdate!";
    } else {
        $_SESSION['error'] = "Anda tidak bisa mengubah role Anda sendiri!";
    }
}

// Ambil semua users
$users = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY role, username");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people me-2"></i>Manajemen User</h2>
    <a href="<?php echo base_url(); ?>/modules/dashboard.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                <?php echo strtoupper($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="role" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                    <option value="member" <?php echo $user['role'] == 'member' ? 'selected' : ''; ?>>Member</option>
                                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </form>
                            <?php else: ?>
                            <span class="text-muted">(Anda)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$conn->close();
include '../../includes/footer.php'; 
?>