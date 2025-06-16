<?php
// proses_lapor.php
// Memproses dan menyimpan data laporan pelanggaran dengan aman.

include('config/db_connect.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Validasi: Pastikan form disubmit dan user adalah mahasiswa
if (isset($_POST['submit']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'mahasiswa') {

    // 1. Sanitasi dan ambil data input
    $kelas_id = $_POST['kelas_id'];
    $tipe_pelanggaran = mysqli_real_escape_string($conn, $_POST['tipe_pelanggaran']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $pelapor_nrp = $_SESSION['user_nrp'];
    $admin_id = 'ADM001'; // ID Admin default, bisa disesuaikan

    // 2. Dapatkan NRP Tutor berdasarkan kelas yang dipilih
    $query_tutor = "SELECT Tutor_NRP_Tutor FROM Kelas WHERE ID_Kelas = ?";
    $stmt_tutor = $conn->prepare($query_tutor);
    $stmt_tutor->bind_param("s", $kelas_id);
    $stmt_tutor->execute();
    $result_tutor = $stmt_tutor->get_result();
    if ($result_tutor->num_rows === 0) {
        die("Error: Data kelas tidak valid.");
    }
    $terlapor_tutor_nrp = $result_tutor->fetch_assoc()['Tutor_NRP_Tutor'];
    $stmt_tutor->close();

    // 3. Mulai transaksi untuk memastikan integritas data
    $conn->begin_transaction();

    try {
        // Buat ID Pelaporan unik, contoh: PLG + 3 digit acak
        $id_pelaporan = 'PLG' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);

        // a. Insert ke tabel utama `Pelanggaran`
        $query_pelanggaran = "INSERT INTO Pelanggaran (ID_Pelaporan, Tipe_Pelanggaran, Deskripsi_Pelanggaran, admin_ID_admin) VALUES (?, ?, ?, ?)";
        $stmt_pelanggaran = $conn->prepare($query_pelanggaran);
        $stmt_pelanggaran->bind_param("ssss", $id_pelaporan, $tipe_pelanggaran, $deskripsi, $admin_id);
        $stmt_pelanggaran->execute();

        // b. Insert ke tabel penghubung `mahasiswa_pelanggaran`
        $query_mhs = "INSERT INTO mahasiswa_pelanggaran (Mahasiswa_NRP, Pelanggaran_ID) VALUES (?, ?)";
        $stmt_mhs = $conn->prepare($query_mhs);
        $stmt_mhs->bind_param("ss", $pelapor_nrp, $id_pelaporan);
        $stmt_mhs->execute();

        // c. Insert ke tabel penghubung `tutor_pelanggaran`
        $query_tutor_link = "INSERT INTO tutor_pelanggaran (Tutor_NRP, Pelanggaran_ID) VALUES (?, ?)";
        $stmt_tutor_link = $conn->prepare($query_tutor_link);
        $stmt_tutor_link->bind_param("ss", $terlapor_tutor_nrp, $id_pelaporan);
        $stmt_tutor_link->execute();

        // Jika semua query berhasil, simpan perubahan
        $conn->commit();
        header('Location: dashboard_mahasiswa.php?status=laporan_sukses');
        exit();

    } catch (mysqli_sql_exception $exception) {
        // Jika terjadi kesalahan, batalkan semua perubahan
        $conn->rollback();
        // Tampilkan pesan error yang lebih informatif (bisa dimatikan di production)
        error_log("Gagal menyimpan laporan: " . $exception->getMessage());
        header('Location: laporkan_pelanggaran.php?status=gagal');
        exit();
    } finally {
        // Selalu tutup statement
        if (isset($stmt_pelanggaran)) $stmt_pelanggaran->close();
        if (isset($stmt_mhs)) $stmt_mhs->close();
        if (isset($stmt_tutor_link)) $stmt_tutor_link->close();
    }

} else {
    // Jika akses tidak sah, redirect ke halaman login
    header('Location: login.php');
    exit();
}

$conn->close();
?>
