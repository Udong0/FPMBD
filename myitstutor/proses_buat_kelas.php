<?php
// LOKASI: /myitstutor/proses_buat_kelas.php

session_start();
require_once 'config/db_connect.php';

// Keamanan: Pastikan hanya tutor yang bisa mengakses dan data dikirim via POST
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

// Ambil data dari form
$matkul = $_POST['matkul'];
$tanggal = $_POST['tanggal'];
$jam_mulai = $_POST['jam_mulai'];
$jam_selesai = $_POST['jam_selesai'];
$id_ruangan = $_POST['id_ruangan'];

$nrp_tutor = $_SESSION['user_id'];
$id_admin_default = 'ADM001'; 

$id_kelas = 'KLS' . rand(100, 999);
$status_kelas = 'Dijadwalkan';

$sql = "INSERT INTO Kelas (ID_Kelas, Tanggal_Kelas, kelas_start, kelas_end, Matkul, Status_kelas, Tutor_NRP_Tutor, Ruangan_ID_Ruangan, admin_ID_admin) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssss", 
        $id_kelas, 
        $tanggal, 
        $jam_mulai, 
        $jam_selesai, 
        $matkul, 
        $status_kelas, 
        $nrp_tutor, 
        $id_ruangan, 
        $id_admin_default
    );

    if (mysqli_stmt_execute($stmt)) {

        header("Location: dashboard_tutor.php?status=kelas_sukses");
        exit();
    } 
    else {
        echo "Error: Gagal membuat kelas. " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error: Gagal mempersiapkan statement. " . mysqli_error($conn);
}

mysqli_close($conn);

?>
