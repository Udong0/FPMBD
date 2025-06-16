<?php
// LOKASI: /myitstutor/proses_edit_profil.php

session_start();
require_once 'config/db_connect.php';

// Keamanan: Pastikan user login dan metode POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$nama = $_POST['nama'];
$email = $_POST['email'];
$password = $_POST['password'];

// Update data dasar di tabel Mahasiswa
$sql_mhs = "UPDATE Mahasiswa SET Nama_mahasiswa = ?, Email = ? WHERE NRP_Mahasiswa = ?";
$stmt_mhs = mysqli_prepare($conn, $sql_mhs);
mysqli_stmt_bind_param($stmt_mhs, "sss", $nama, $email, $user_id);
mysqli_stmt_execute($stmt_mhs);
mysqli_stmt_close($stmt_mhs);

// Jika pengguna juga seorang tutor, update spesialisasi
if (isset($_POST['spesialisasi'])) {
    $spesialisasi = $_POST['spesialisasi'];
    $sql_tutor = "UPDATE Tutor SET Spesialisasi = ?, Nama_Tutor = ? WHERE NRP_Tutor = ?";
    $stmt_tutor = mysqli_prepare($conn, $sql_tutor);
    mysqli_stmt_bind_param($stmt_tutor, "sss", $spesialisasi, $nama, $user_id);
    mysqli_stmt_execute($stmt_tutor);
    mysqli_stmt_close($stmt_tutor);
}

// Jika kolom password diisi, update password
if (!empty($password)) {
    // Di aplikasi nyata, HASH password ini!
    // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    // Karena permintaan Anda, kita simpan sebagai plain text
    $plain_password = $password;

    // Update password di semua tabel yang mungkin
    $sql_pass_mhs = "UPDATE Mahasiswa SET Password = ? WHERE NRP_Mahasiswa = ?";
    $stmt_pass_mhs = mysqli_prepare($conn, $sql_pass_mhs);
    mysqli_stmt_bind_param($stmt_pass_mhs, "ss", $plain_password, $user_id);
    mysqli_stmt_execute($stmt_pass_mhs);
    mysqli_stmt_close($stmt_pass_mhs);

    // Cek dan update password di tabel Tutor jika ada
    $sql_pass_tutor_check = "SELECT NRP_Tutor FROM Tutor WHERE NRP_Tutor = ?";
    $stmt_pass_tutor_check = mysqli_prepare($conn, $sql_pass_tutor_check);
    mysqli_stmt_bind_param($stmt_pass_tutor_check, "s", $user_id);
    mysqli_stmt_execute($stmt_pass_tutor_check);
    if(mysqli_stmt_get_result($stmt_pass_tutor_check)->num_rows > 0) {
        $sql_pass_tutor = "UPDATE Tutor SET Password = ? WHERE NRP_Tutor = ?";
        $stmt_pass_tutor = mysqli_prepare($conn, $sql_pass_tutor);
        mysqli_stmt_bind_param($stmt_pass_tutor, "ss", $plain_password, $user_id);
        mysqli_stmt_execute($stmt_pass_tutor);
        mysqli_stmt_close($stmt_pass_tutor);
    }
    mysqli_stmt_close($stmt_pass_tutor_check);
}

// Update nama di session agar langsung berubah di header
$_SESSION['user_nama'] = $nama;

// Redirect kembali ke halaman profil dengan pesan sukses
header("Location: profil.php?id=" . $user_id . "&status=update_sukses");
exit();

?>
