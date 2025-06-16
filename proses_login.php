<?php
// LOKASI: /myitstutor/proses_login.php
// Versi ini mengembalikan logika ke "satu peran, satu dashboard" untuk stabilitas.

session_start();
require_once 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $_POST['nrp']; // Bisa NRP atau ID
    $password_input = $_POST['password'];

    // Daftar peran untuk diperiksa, dari yang paling umum hingga spesifik
    $roles_to_check = [
        'mahasiswa' => ['table' => 'Mahasiswa', 'id_col' => 'NRP_Mahasiswa', 'name_col' => 'Nama_mahasiswa'],
        'tutor'     => ['table' => 'Tutor', 'id_col' => 'NRP_Tutor', 'name_col' => 'Nama_Tutor'],
        'admin'     => ['table' => 'admin', 'id_col' => 'ID_Admin', 'name_col' => 'Nama_admin'],
        'sarpras'   => ['table' => 'Sarpras', 'id_col' => 'ID_sarpras', 'name_col' => 'Nama_Sarpras']
    ];

    foreach ($roles_to_check as $role => $details) {
        $sql = "SELECT * FROM {$details['table']} WHERE {$details['id_col']} = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $identifier);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            // Verifikasi password (menggunakan perbandingan plain text sesuai permintaan Anda)
            if ($password_input === $user['Password']) {
                // Jika login berhasil:
                
                // 1. Simpan informasi penting ke dalam SESSION
                $_SESSION['user_id'] = $user[$details['id_col']];
                $_SESSION['user_nama'] = $user[$details['name_col']];
                $_SESSION['user_role'] = $role; // Simpan SATU peran saja

                // 2. Arahkan ke dashboard yang spesifik
                header("Location: dashboard_" . $role . ".php");
                exit(); // Hentikan script setelah redirect
            }
        }
        mysqli_stmt_close($stmt);
    }

    // Jika setelah semua pengecekan tidak ada yang cocok
    header("Location: login.php?status=login_failed");
    exit();

} else {
    header("Location: index.php");
    exit();
}
?>
