<?php
$page_title = 'Register';
require_once '../../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getConnection();
    
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'member';
    
    $check = $conn->query("SELECT id FROM users WHERE username='$username' OR email='$email'");
    
    if ($check->num_rows > 0) {
        $error = "Username atau email sudah terdaftar!";
    } else {
        $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
            header('Location: login.php');
            exit();
        } else {
            $error = "Gagal registrasi: " . $conn->error;
        }
    }
    $conn->close();
}
?>

<style>
    html, body {
        height: 100%;
        margin: 0;
    }
    
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    .content-wrapper {
        flex: 1 0 auto;
        display: flex;
        align-items: center;
        padding: 20px 0;
    }
    
    .footer {
        flex-shrink: 0;
        width: 100%;
    }
</style>

<div class="content-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-dark text-white text-center py-4">
                        <h3 class="mb-0"><i class="bi bi-check2-square me-2"></i>Collabs</h3>
                        <p class="small mb-0 mt-2">Team Collaborator Platform</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person me-1"></i>Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope me-1"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-1"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-lock-fill me-1"></i>Konfirmasi Password
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-dark btn-lg">
                                    <i class="bi bi-person-plus me-2"></i>Register
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">
                                Sudah punya akun? 
                                <a href="login.php" class="text-primary fw-bold">
                                    Login di sini
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    var password = document.getElementById('password').value;
    var confirm = document.getElementById('confirm_password').value;
    
    if (password !== confirm) {
        e.preventDefault();
        alert('Password dan konfirmasi password tidak cocok!');
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>