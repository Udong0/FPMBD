<?php
// LOKASI: /myitstutor/proses_login.php

session_start();
require_once 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$identifier     = $_POST['nrp'];
$password_input = $_POST['password'];
$role_selected  = $_POST['role'] ?? '';

// 1) Jika Mahasiswa atau Tutor dipilih:
if ($role_selected === 'mahasiswa' || $role_selected === 'tutor') {
    if ($role_selected === 'mahasiswa') {
        $table     = 'Mahasiswa';
        $id_col    = 'NRP_Mahasiswa';
        $name_col  = 'Nama_mahasiswa';
        $dashboard = 'dashboard_mahasiswa.php';
    } else {
        $table     = 'Tutor';
        $id_col    = 'NRP_Tutor';
        $name_col  = 'Nama_Tutor';
        $dashboard = 'dashboard_tutor.php';
    }

    $sql  = "SELECT * FROM {$table} WHERE {$id_col} = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if ($password_input === $user['Password']) {
            $_SESSION['user_id']   = $user[$id_col];
            $_SESSION['user_nama'] = $user[$name_col];
            $_SESSION['user_role'] = $role_selected;
            header("Location: {$dashboard}");
            exit();
        }
    }

    // Gagal login Mahasiswa/Tutor
    header("Location: login.php?status=login_failed");
    exit();
}

// 2) Kalau tidak memilih Mahasiswa/Tutor, coba Admin → Sarpras
$check_roles = [
    'admin'   => ['table'=>'admin',   'id_col'=>'ID_Admin',    'name_col'=>'Nama_admin',   'dash'=>'dashboard_admin.php'],
    'sarpras' => ['table'=>'Sarpras', 'id_col'=>'ID_sarpras',  'name_col'=>'Nama_Sarpras', 'dash'=>'dashboard_sarpras.php'],
];

foreach ($check_roles as $role => $cfg) {
    $sql  = "SELECT * FROM {$cfg['table']} WHERE {$cfg['id_col']} = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if ($password_input === $user['Password']) {
            $_SESSION['user_id']   = $user[$cfg['id_col']];
            $_SESSION['user_nama'] = $user[$cfg['name_col']];
            $_SESSION['user_role'] = $role;
            header("Location: " . $cfg['dash']);
            exit();
        }
    }
}

// Jika semua cek gagal
header("Location: login.php?status=login_failed");
exit();
