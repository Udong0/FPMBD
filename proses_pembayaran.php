<?php
// LOKASI: /myitstutor/proses_pembayaran.php

session_start();
require_once 'config/db_connect.php';

// Keamanan: Pastikan hanya mahasiswa yang login & metode POST yang diizinkan
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

$id_transaksi = $_POST['id_transaksi'];
$nrp_mahasiswa = $_SESSION['user_id'];

// Query untuk mengupdate status pembayaran menjadi 1 (Lunas)
// Ditambahkan klausa WHERE untuk memastikan mahasiswa hanya bisa membayar transaksinya sendiri
$sql = "UPDATE Transaksi SET Status_Pembayaran = 1, Metode_Bayar = 'Simulasi Online' WHERE ID_Transaksi = ? AND Mahasiswa_NRP_mahasiswa = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $id_transaksi, $nrp_mahasiswa);

if (mysqli_stmt_execute($stmt)) {
    // Jika berhasil, arahkan kembali ke halaman riwayat transaksi dengan pesan sukses
    header("Location: riwayat_transaksi.php?status=bayar_sukses");
    exit();
} else {
    // Jika gagal
    echo "Error: Gagal memproses pembayaran. " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
