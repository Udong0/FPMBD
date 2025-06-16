<?php
// LOKASI: /myitstutor/proses_pencairan.php

session_start();
require_once 'config/db_connect.php';

// Keamanan: Pastikan hanya tutor yang login & metode POST
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

$nrp_tutor = $_SESSION['user_id'];
$jumlah = $_POST['jumlah'];

// Di sini Anda bisa menambahkan validasi lebih lanjut,
// misalnya memeriksa apakah jumlah yang diminta tidak melebihi saldo.
// Untuk saat ini, kita langsung proses.

$id_pencairan = 'WDR' . rand(100, 999); // WDR = Withdraw
$status = 'Menunggu';

$sql = "INSERT INTO Pencairan (ID_Pencairan, Jumlah, Status, Tutor_NRP_Tutor) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sdss", $id_pencairan, $jumlah, $status, $nrp_tutor);

if (mysqli_stmt_execute($stmt)) {
    header("Location: pencairan_dana.php?status=sukses");
    exit();
} else {
    echo "Error: Gagal mengajukan permintaan. " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
