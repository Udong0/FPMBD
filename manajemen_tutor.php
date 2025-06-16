<?php
// manajemen_tutor.php
// Halaman untuk admin menambah dan menghapus role tutor.

include('includes/header.php');
include('config/db_connect.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Keamanan: Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php?error=access_denied");
    exit();
}

// Pesan status untuk notifikasi
$status_msg = '';

// === LOGIKA PROSES FORM ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- Proses Hapus Role Tutor (Diperbaiki dengan Transaksi) ---
    if (isset($_POST['hapus_tutor'])) {
        $nrp_tutor_hapus = $_POST['nrp_tutor'];
        
        // Mulai transaksi untuk memastikan semua operasi penghapusan aman
        $conn->begin_transaction();
        
        try {
            // 1. Temukan semua ID kelas yang diajar oleh tutor ini.
            $query_kelas_ids = "SELECT ID_Kelas FROM Kelas WHERE Tutor_NRP_Tutor = ?";
            $stmt_kelas = $conn->prepare($query_kelas_ids);
            $stmt_kelas->bind_param("s", $nrp_tutor_hapus);
            $stmt_kelas->execute();
            $result_kelas = $stmt_kelas->get_result();
            $kelas_ids = [];
            while ($row = $result_kelas->fetch_assoc()) {
                $kelas_ids[] = $row['ID_Kelas'];
            }
            $stmt_kelas->close();

            // 2. Jika tutor ini memiliki kelas, hapus data transaksi yang terkait dengan kelas-kelas tersebut.
            // Ini adalah langkah kunci untuk mengatasi error foreign key.
            if (!empty($kelas_ids)) {
                $placeholders = implode(',', array_fill(0, count($kelas_ids), '?'));
                $types = str_repeat('s', count($kelas_ids));
                
                $query_delete_transaksi = "DELETE FROM Transaksi WHERE Kelas_ID_Kelas IN ($placeholders)";
                $stmt_transaksi = $conn->prepare($query_delete_transaksi);
                $stmt_transaksi->bind_param($types, ...$kelas_ids);
                $stmt_transaksi->execute();
                $stmt_transaksi->close();
            }

            // 3. Setelah data yang menghalangi dihapus, barulah hapus tutor.
            // ON DELETE CASCADE pada tabel Kelas akan bekerja sekarang.
            $query_hapus_tutor = "DELETE FROM Tutor WHERE NRP_Tutor = ?";
            $stmt_hapus = $conn->prepare($query_hapus_tutor);
            $stmt_hapus->bind_param("s", $nrp_tutor_hapus);
            $stmt_hapus->execute();
            $stmt_hapus->close();

            // Jika semua langkah berhasil, simpan perubahan
            $conn->commit();
            $status_msg = "<div class='alert alert-success'>Role tutor dan semua data terkait (kelas, transaksi, dll.) berhasil dihapus.</div>";

        } catch (mysqli_sql_exception $e) {
            // Jika terjadi error di salah satu langkah, batalkan semua perubahan
            $conn->rollback();
            $status_msg = "<div class='alert alert-danger'>Gagal menghapus role tutor. Error: " . $e->getMessage() . "</div>";
        }
    }

    // --- Proses Tambah Role Tutor ---
    if (isset($_POST['tambah_tutor'])) {
        $nrp_mahasiswa_tambah = $_POST['nrp_mahasiswa'];

        // 1. Ambil data mahasiswa dari tabel Mahasiswa
        $query_mhs = "SELECT Nama_mahasiswa, Password FROM Mahasiswa WHERE NRP_Mahasiswa = ?";
        $stmt_mhs = $conn->prepare($query_mhs);
        $stmt_mhs->bind_param("s", $nrp_mahasiswa_tambah);
        $stmt_mhs->execute();
        $result_mhs = $stmt_mhs->get_result();
        
        if ($result_mhs->num_rows > 0) {
            $mahasiswa = $result_mhs->fetch_assoc();
            
            // 2. Insert data ke tabel Tutor
            $query_tambah = "INSERT INTO Tutor (NRP_Tutor, Nama_Tutor, Password, Spesialisasi, Status_tutor) VALUES (?, ?, ?, 'Umum', 'Aktif')";
            $stmt_tambah = $conn->prepare($query_tambah);
            $stmt_tambah->bind_param("sss", $nrp_mahasiswa_tambah, $mahasiswa['Nama_mahasiswa'], $mahasiswa['Password']);
            
            if ($stmt_tambah->execute()) {
                $status_msg = "<div class='alert alert-success'>Mahasiswa berhasil ditambahkan sebagai tutor.</div>";
            } else {
                $status_msg = "<div class='alert alert-danger'>Gagal menambahkan mahasiswa sebagai tutor. Mungkin NRP sudah terdaftar.</div>";
            }
            $stmt_tambah->close();
        }
        $stmt_mhs->close();
    }
}

// === LOGIKA PENGAMBILAN DATA ===

// Ambil daftar semua tutor
$query_tutors = "SELECT NRP_Tutor, Nama_Tutor, Spesialisasi, Status_tutor FROM Tutor ORDER BY Nama_Tutor";
$result_tutors = $conn->query($query_tutors);
$tutor_list = $result_tutors->fetch_all(MYSQLI_ASSOC);

// Ambil daftar mahasiswa yang BUKAN tutor
$query_mahasiswa = "
    SELECT m.NRP_Mahasiswa, m.Nama_mahasiswa 
    FROM Mahasiswa m 
    LEFT JOIN Tutor t ON m.NRP_Mahasiswa = t.NRP_Tutor 
    WHERE t.NRP_Tutor IS NULL
    ORDER BY m.Nama_mahasiswa
";
$result_mahasiswa = $conn->query($query_mahasiswa);
$mahasiswa_list = $result_mahasiswa->fetch_all(MYSQLI_ASSOC);

?>

<div class="container mt-5">
    <h2>Manajemen Role Tutor</h2>
    <p class="text-muted">Kelola siapa yang memiliki akses sebagai tutor di platform ini.</p>
    
    <?php echo $status_msg; ?>

    <!-- Bagian Daftar Tutor Aktif -->
    <div class="card shadow-sm mb-5">
        <div class="card-header">
            <h5 class="mb-0">Daftar Tutor Aktif</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>NRP</th>
                            <th>Nama Tutor</th>
                            <th>Spesialisasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tutor_list) > 0): ?>
                            <?php foreach ($tutor_list as $tutor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tutor['NRP_Tutor']); ?></td>
                                <td><?php echo htmlspecialchars($tutor['Nama_Tutor']); ?></td>
                                <td><?php echo htmlspecialchars($tutor['Spesialisasi']); ?></td>
                                <td>
                                    <span class="badge <?php echo ($tutor['Status_tutor'] == 'Aktif') ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo htmlspecialchars($tutor['Status_tutor']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="manajemen_tutor.php" method="POST" onsubmit="return confirm('PERINGATAN: Menghapus role tutor ini akan menghapus semua data terkait, termasuk KELAS dan RIWAYAT TRANSAKSI. Anda yakin?');">
                                        <input type="hidden" name="nrp_tutor" value="<?php echo htmlspecialchars($tutor['NRP_Tutor']); ?>">
                                        <button type="submit" name="hapus_tutor" class="btn btn-sm btn-outline-danger">Hapus Role</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">Belum ada tutor yang terdaftar.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bagian Tambah Tutor dari Mahasiswa -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Tambah Tutor dari Daftar Mahasiswa</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>NRP</th>
                            <th>Nama Mahasiswa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($mahasiswa_list) > 0): ?>
                            <?php foreach ($mahasiswa_list as $mahasiswa): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mahasiswa['NRP_Mahasiswa']); ?></td>
                                <td><?php echo htmlspecialchars($mahasiswa['Nama_mahasiswa']); ?></td>
                                <td>
                                    <form action="manajemen_tutor.php" method="POST" onsubmit="return confirm('Anda yakin ingin menjadikan mahasiswa ini sebagai tutor?');">
                                        <input type="hidden" name="nrp_mahasiswa" value="<?php echo htmlspecialchars($mahasiswa['NRP_Mahasiswa']); ?>">
                                        <button type="submit" name="tambah_tutor" class="btn btn-sm btn-outline-primary">Jadikan Tutor</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center">Semua mahasiswa sudah menjadi tutor.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
