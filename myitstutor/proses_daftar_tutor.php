<?php

session_start();
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

$nrp_mahasiswa = $_SESSION['user_id'];
$matkul = $_POST['matkul'];

$sql_check = "SELECT ID_pendaftaran FROM Pendaftaran_Tutor WHERE Mahasiswa_NRP_Mahasiswa = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "s", $nrp_mahasiswa);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if(mysqli_num_rows($result_check) > 0) {
    header("Location: dashboard_mahasiswa.php?status=daftar_gagal_sudah_ada");
    exit();
}

$id_pendaftaran = 'REG' . rand(100, 999);
$status_pendaftar = 'Menunggu';
$admin_default = 'ADM001';

$sql = "INSERT INTO Pendaftaran_Tutor (ID_pendaftaran, Matkul_didaftar, Status_pendaftar, admin_ID_admin, Mahasiswa_NRP_Mahasiswa) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssss", $id_pendaftaran, $matkul, $status_pendaftar, $admin_default, $nrp_mahasiswa);

if (mysqli_stmt_execute($stmt)) {
    header("Location: dashboard_mahasiswa.php?status=daftar_tutor_sukses");
    exit();
} else {
    echo "Error: Gagal mengajukan pendaftaran. " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
