<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db_connect.php';
session_start();

// Keamanan: hanya admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?error=access_denied");
    exit();
}

// Validasi input
if (
    !isset($_POST['id_kelas']) ||
    !isset($_POST['matkul']) ||
    !isset($_POST['tanggal']) ||
    !isset($_POST['jam_mulai']) ||
    !isset($_POST['jam_selesai']) ||
    !isset($_POST['id_ruangan'])
) {
    header("Location: view_kelas.php?error=invalid_input");
    exit();
}

$id_kelas = $_POST['id_kelas'];
$matkul = $_POST['matkul'];
$tanggal = $_POST['tanggal'];
$jam_mulai = $_POST['jam_mulai'];
$jam_selesai = $_POST['jam_selesai'];
$id_ruangan = $_POST['id_ruangan'];

// Panggil PROCEDURE
$stmt = $conn->prepare("CALL sp_update_kelas(?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $id_kelas, $matkul, $tanggal, $jam_mulai, $jam_selesai, $id_ruangan);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: view_kelas.php?success=update");
    exit();
} else {
    $error_msg = $stmt->error;
    $stmt->close();
    header("Location: edit_jadwal_kelas.php?id=$id_kelas&error=" . urlencode($error_msg));
    exit();
}
?>
