<?php
require_once 'includes/functions.php';

// Kalau sudah login, langsung ke dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collabs - Team Collaboration Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="display-4 mb-4">Collabs</h1>
                <p class="lead">Team Collaboration Platform</p>
                <hr class="my-4">
                <p>Kelola tugas, pantau progress, dan berkolaborasi dengan tim secara efektif.</p>
                <div class="mt-4">
                    <a href="modules/auth/login.php" class="btn btn-dark btn-lg me-2">Login</a>
                    <a href="modules/auth/register.php" class="btn btn-outline-dark btn-lg">Register</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>