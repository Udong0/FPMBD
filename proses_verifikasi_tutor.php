<?php
// LOKASI: /myitstutor/proses_verifikasi_tutor.php

session_start();
require_once 'config/db_connect.php';

// Keamanan: Pastikan hanya admin yang login & metode POST yang diizinkan
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

$id_pendaftaran = $_POST['id_pendaftaran'];
$nrp_mahasiswa = $_POST['nrp_mahasiswa'];
$matkul = $_POST['matkul'];
$action = $_POST['action'];

if ($action == 'terima') {
    // ---- PROSES PERSETUJUAN ----
    
    // 1. Ambil data mahasiswa (nama & password) untuk dijadikan data tutor
    $sql_get_mhs = "SELECT Nama_mahasiswa, Password FROM Mahasiswa WHERE NRP_Mahasiswa = ?";
    $stmt_get_mhs = mysqli_prepare($conn, $sql_get_mhs);
    mysqli_stmt_bind_param($stmt_get_mhs, "s", $nrp_mahasiswa);
    mysqli_stmt_execute($stmt_get_mhs);
    $result_mhs = mysqli_stmt_get_result($stmt_get_mhs);
    $mahasiswa_data = mysqli_fetch_assoc($result_mhs);
    mysqli_stmt_close($stmt_get_mhs);

    $nama_tutor = $mahasiswa_data['Nama_mahasiswa'];
    $password_tutor = $mahasiswa_data['Password']; // Mengambil password plain text

    // Gunakan transaction untuk memastikan semua query berhasil atau semua gagal
    mysqli_begin_transaction($conn);

    try {
        // 2. Update status pendaftaran menjadi 'Diterima'
        $sql_update = "UPDATE Pendaftaran_Tutor SET Status_pendaftar = 'Diterima' WHERE ID_pendaftaran = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "s", $id_pendaftaran);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);

        // 3. Masukkan data ke tabel Tutor
        $sql_insert = "INSERT INTO Tutor (NRP_Tutor, Nama_Tutor, Spesialisasi, Password) VALUES (?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($conn, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "ssss", $nrp_mahasiswa, $nama_tutor, $matkul, $password_tutor);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
        
        // Jika semua query berhasil, commit transaction
        mysqli_commit($conn);
        header("Location: dashboard_admin.php?status=verifikasi_sukses");
        exit();

    } catch (mysqli_sql_exception $exception) {
        // Jika ada error, batalkan semua perubahan
        mysqli_rollback($conn);
        die("Verifikasi Gagal: " . $exception->getMessage());
    }

} elseif ($action == 'tolak') {
    // ---- PROSES PENOLAKAN ----
    $sql_update = "UPDATE Pendaftaran_Tutor SET Status_pendaftar = 'Ditolak' WHERE ID_pendaftaran = ?";
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "s", $id_pendaftaran);
    
    if (mysqli_stmt_execute($stmt_update)) {
        header("Location: dashboard_admin.php?status=verifikasi_ditolak");
        exit();
    } else {
        echo "Error: Gagal menolak pendaftaran.";
    }
    mysqli_stmt_close($stmt_update);
}

mysqli_close($conn);

?>
