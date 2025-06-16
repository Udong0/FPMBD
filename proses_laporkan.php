<?php
// LOKASI: /myitstutor/proses_laporkan.php

session_start();
require_once 'config/db_connect.php';

// Keamanan: Pastikan user login dan metode POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

$id_terlapor = $_POST['id_terlapor'];
$tipe_pelanggaran = $_POST['tipe_pelanggaran'];
$deskripsi = htmlspecialchars($_POST['deskripsi']);
$id_pelapor = $_SESSION['user_id'];
$admin_default = 'ADM001'; // Asumsi admin default

// Mulai transaction untuk memastikan semua query berhasil
mysqli_begin_transaction($conn);

try {
    // 1. Masukkan data ke tabel utama Pelanggaran
    $id_pelaporan = 'PLG' . rand(100, 999);
    $sql_pelanggaran = "INSERT INTO Pelanggaran (ID_Pelaporan, Tipe_Pelanggaran, Deskripsi_Pelanggaran, admin_ID_admin) VALUES (?, ?, ?, ?)";
    $stmt_pelanggaran = mysqli_prepare($conn, $sql_pelanggaran);
    mysqli_stmt_bind_param($stmt_pelanggaran, "ssss", $id_pelaporan, $tipe_pelanggaran, $deskripsi, $admin_default);
    mysqli_stmt_execute($stmt_pelanggaran);
    mysqli_stmt_close($stmt_pelanggaran);

    // 2. Kaitkan laporan dengan pengguna yang MELAPORKAN
    // (Asumsi pelapor adalah mahasiswa)
    $sql_pelapor = "INSERT INTO mahasiswa_pelanggaran (Mahasiswa_NRP, Pelanggaran_ID) VALUES (?, ?)";
    $stmt_pelapor = mysqli_prepare($conn, $sql_pelapor);
    mysqli_stmt_bind_param($stmt_pelapor, "ss", $id_pelapor, $id_pelaporan);
    mysqli_stmt_execute($stmt_pelapor);
    mysqli_stmt_close($stmt_pelapor);
    
    // 3. Kaitkan laporan dengan pengguna yang DILAPORKAN
    // Cek apakah yang dilaporkan adalah seorang tutor
    $sql_tutor_check = "SELECT NRP_Tutor FROM Tutor WHERE NRP_Tutor = ?";
    $stmt_tutor_check = mysqli_prepare($conn, $sql_tutor_check);
    mysqli_stmt_bind_param($stmt_tutor_check, "s", $id_terlapor);
    mysqli_stmt_execute($stmt_tutor_check);
    if(mysqli_stmt_get_result($stmt_tutor_check)->num_rows > 0) {
        $sql_terlapor_tutor = "INSERT INTO tutor_pelanggaran (Tutor_NRP, Pelanggaran_ID) VALUES (?, ?)";
        $stmt_terlapor_tutor = mysqli_prepare($conn, $sql_terlapor_tutor);
        mysqli_stmt_bind_param($stmt_terlapor_tutor, "ss", $id_terlapor, $id_pelaporan);
        mysqli_stmt_execute($stmt_terlapor_tutor);
        mysqli_stmt_close($stmt_terlapor_tutor);
    }
    mysqli_stmt_close($stmt_tutor_check);
    
    // Commit transaction jika semua berhasil
    mysqli_commit($conn);
    header("Location: profil.php?id=" . $id_terlapor . "&status=laporan_sukses");
    exit();

} catch (mysqli_sql_exception $exception) {
    mysqli_rollback($conn);
    die("Gagal mengirim laporan: " . $exception->getMessage());
}

?>
