<?php
$page_title = 'Login';
require_once '../../includes/header.php';

// Cek apakah sudah login
if (isLoggedIn()) {
    header('Location: ' . base_url() . '/modules/dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getConnection();
    
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $result = $conn->query("SELECT * FROM users WHERE username='$username' OR email='$username'");
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            $conn->query("UPDATE users SET last_login = NOW() WHERE id = " . $user['id']);
            logActivity($user['id'], 'Login');
            
            header('Location: ' . base_url() . '/modules/dashboard.php');
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username/Email tidak ditemukan!";
    }
    $conn->close();
}

$base_url = base_url();
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
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person me-1"></i>Username atau Email
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Masukkan username atau email" required autofocus>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-1"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Masukkan password" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-dark btn-lg">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">
                                Belum punya akun? 
                                <a href="register.php" class="text-primary fw-bold">
                                    Register di sini
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>