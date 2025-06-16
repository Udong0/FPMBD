<?php
// LOKASI: /myitstutor/proses_gabung_kelas.php

session_start();
require_once 'config/db_connect.php';

// Keamanan: Pastikan user adalah mahasiswa dan data dikirim via POST
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

// Ambil data dari form dan session
$id_kelas = $_POST['id_kelas'];
$nrp_tutor = $_POST['nrp_tutor'];
$nrp_mahasiswa = $_SESSION['user_id'];

// Cek apakah mahasiswa sudah terdaftar di kelas ini
$sql_check = "SELECT * FROM mahasiswa_kelas WHERE Mahasiswa_NRP = ? AND Kelas_ID = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ss", $nrp_mahasiswa, $id_kelas);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {
    // Jika sudah terdaftar, kembalikan ke dashboard dengan pesan error
    header("Location: dashboard_mahasiswa.php?status=sudah_terdaftar");
    exit();
}
mysqli_stmt_close($stmt_check);

// Jika belum terdaftar, lanjutkan proses
// 1. Mendaftarkan mahasiswa ke kelas (INSERT ke mahasiswa_kelas)
$sql_daftar = "INSERT INTO mahasiswa_kelas (Mahasiswa_NRP, Kelas_ID) VALUES (?, ?)";
$stmt_daftar = mysqli_prepare($conn, $sql_daftar);
mysqli_stmt_bind_param($stmt_daftar, "ss", $nrp_mahasiswa, $id_kelas);

if (mysqli_stmt_execute($stmt_daftar)) {
    mysqli_stmt_close($stmt_daftar);
    
    $id_transaksi = 'TRN' . rand(100, 999);
    $total_bayar = 75000.00; 
    
    $sql_transaksi = "INSERT INTO Transaksi (ID_Transaksi, Total_Bayar, Status_Pembayaran, Metode_Bayar, Tutor_NRP_Tutor, Mahasiswa_NRP_mahasiswa, Kelas_ID_Kelas) 
                      VALUES (?, ?, FALSE, 'Belum Bayar', ?, ?, ?)";
    $stmt_transaksi = mysqli_prepare($conn, $sql_transaksi);
    mysqli_stmt_bind_param($stmt_transaksi, "sdsss", $id_transaksi, $total_bayar, $nrp_tutor, $nrp_mahasiswa, $id_kelas);
    
    if(mysqli_stmt_execute($stmt_transaksi)) {
        header("Location: dashboard_mahasiswa.php?status=gabung_sukses");
        exit();
    } else {
        echo "Error: Gagal membuat transaksi. " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt_transaksi);

} else {
    echo "Error: Gagal mendaftarkan ke kelas. " . mysqli_error($conn);
    mysqli_stmt_close($stmt_daftar);
}

mysqli_close($conn);
?>
