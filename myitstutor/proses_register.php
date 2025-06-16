<?php
require_once 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = htmlspecialchars($_POST['nama']);
    $nrp = htmlspecialchars($_POST['nrp']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    if (empty($nama) || empty($nrp) || empty($email) || empty($password)) {
        die("Error: Semua kolom wajib diisi.");
    }

    $plain_password = $password;

    $sql = "INSERT INTO Mahasiswa (NRP_Mahasiswa, Nama_mahasiswa, Email, Password) VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $nrp, $nama, $email, $plain_password);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: login.php?status=register_success");
            exit();
        } else {
            echo "Error: Gagal mendaftarkan akun. Kemungkinan NRP atau Email sudah terdaftar. " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error: Gagal mempersiapkan statement. " . mysqli_error($conn);
    }

    mysqli_close($conn);
} else {
    header("Location: index.php");
    exit();
}
?>
