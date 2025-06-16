<?php
// LOKASI: /myitstutor/proses_verifikasi_pencairan.php

session_start();
require_once 'config/db_connect.php';

// Keamanan: Pastikan hanya admin yang login & metode POST yang diizinkan
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

$id_pencairan = $_POST['id_pencairan'];
$action = $_POST['action']; // 'setuju' atau 'tolak'
$admin_id = $_SESSION['user_id'];
$new_status = '';

if ($action == 'setuju') {
    $new_status = 'Disetujui';
} elseif ($action == 'tolak') {
    $new_status = 'Ditolak';
} else {
    die("Aksi tidak valid.");
}

// Query untuk update status permintaan pencairan
$sql = "UPDATE Pencairan SET Status = ?, admin_ID_admin = ?, Tanggal_Persetujuan = NOW() WHERE ID_Pencairan = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $new_status, $admin_id, $id_pencairan);

if (mysqli_stmt_execute($stmt)) {
    // Jika berhasil, arahkan kembali ke dashboard admin
    header("Location: dashboard_admin.php?status=pencairan_sukses");
    exit();
} else {
    echo "Error: Gagal memproses permintaan. " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
