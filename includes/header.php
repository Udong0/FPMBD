<?php
// LOKASI: /myitstutor/includes/header.php

session_start();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>myITSTutor - Bimbingan Belajar</title>

    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">myITSTutor</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])): ?>
                    <!-- Menu jika user sudah login -->
                    <li class="nav-item">
                        <?php
                            // Membuat link dashboard yang dinamis
                            $dashboard_link = "dashboard_" . $_SESSION['user_role'] . ".php";
                        ?>
                        <a class="nav-link" href="<?php echo $dashboard_link; ?>">Dashboard</a>
                    </li>
                    <!-- FITUR YANG HILANG DITAMBAHKAN KEMBALI DI SINI -->
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php?id=<?php echo $_SESSION['user_id']; ?>">Profil Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <!-- Menu jika user belum login -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Container utama untuk konten halaman -->
<main class="container mt-4">
