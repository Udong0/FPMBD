<?php
// LOKASI: /myitstutor/proses_beri_review.php

session_start();
require_once 'config/db_connect.php';

// Keamanan: Pastikan hanya mahasiswa yang login & metode POST
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

$id_kelas = $_POST['id_kelas'];
$rating = $_POST['rating'];
$review_text = htmlspecialchars($_POST['review']); // Amankan input teks
$nrp_mahasiswa = $_SESSION['user_id'];

// --- PERUBAHAN UTAMA: Gabungkan rating dan review ---
$combined_review = $rating . "_||_" . $review_text;

// Keamanan tambahan: Pastikan mahasiswa ini benar-benar terdaftar di kelas tersebut
$sql_check = "SELECT * FROM mahasiswa_kelas WHERE Mahasiswa_NRP = ? AND Kelas_ID = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ss", $nrp_mahasiswa, $id_kelas);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) == 0) {
    // Jika tidak terdaftar, jangan proses
    die("Error: Anda tidak terdaftar di kelas ini.");
}
mysqli_stmt_close($stmt_check);


// Query untuk update kolom Review_kelas dengan string gabungan
$sql_update = "UPDATE Kelas SET Review_kelas = ? WHERE ID_Kelas = ?";
$stmt_update = mysqli_prepare($conn, $sql_update);
mysqli_stmt_bind_param($stmt_update, "ss", $combined_review, $id_kelas);

if (mysqli_stmt_execute($stmt_update)) {
    // Jika berhasil, arahkan kembali ke riwayat transaksi
    header("Location: riwayat_transaksi.php?status=review_sukses");
    exit();
} else {
    echo "Error: Gagal mengirim review. " . mysqli_error($conn);
}

mysqli_stmt_close($stmt_update);
mysqli_close($conn);

?>
