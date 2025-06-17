<?php
// LOKASI: /myitstutor/dashboard_sarpras.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'sarpras') {
    echo "<div class='alert alert-danger'>Akses Ditolak. Anda harus login sebagai Sarpras untuk mengakses halaman ini.</div>";
    require_once 'includes/footer.php';
    exit();
}

$user_nama = $_SESSION['user_nama'];
?>

<h1 class="mt-4">Dashboard Sarpras</h1>
<p class="lead">Selamat datang, Staf Sarpras <?php echo htmlspecialchars($user_nama); ?>!</p>
<hr>

<h3>Manajemen Ruangan</h3>
<div class="list-group mb-5">
    <a href="manajemen_peminjaman_sarpras.php" class="list-group-item list-group-item-action">
        Persetujuan Peminjaman Ruangan
    </a>
    <a href="jadwal_penggunaan_ruangan.php" class="list-group-item list-group-item-action">
        Jadwal Penggunaan Ruangan
    </a>
    <a href="manajemen_daftar_ruangan.php" class="list-group-item list-group-item-action">
        Manajemen Daftar Ruangan
    </a>
</div>

<?php require_once 'includes/footer.php'; ?>
