<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: hanya admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?error=access_denied");
    exit();
}

// Ambil data dari view
$sql = "SELECT * FROM vw_kelas_participant_count ORDER BY tanggal_kelas";
$result = mysqli_query($conn, $sql);
$kelas_list = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
?>

<div class="container mt-5">
    <h2>Daftar Kelas & Jumlah Peserta</h2>
    <p class="text-muted">Semua kelas beserta detail dan berapa banyak peserta yang terdaftar.</p>
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID Kelas</th>
                        <th>Mata Kuliah</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Tutor</th>
                        <th>Ruangan</th>
                        <th>Peserta</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($kelas_list) > 0): ?>
                    <?php foreach ($kelas_list as $k): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($k['id_kelas']); ?></td>
                        <td><?php echo htmlspecialchars($k['matkul']); ?></td>
                        <td><?php echo date('d M Y', strtotime($k['tanggal_kelas'])); ?></td>
                        <td><?php echo date('H:i', strtotime($k['kelas_start'])) . " – " . date('H:i', strtotime($k['kelas_end'])); ?></td>
                        <td><?php echo htmlspecialchars($k['status_kelas']); ?></td>
                        <td><?php echo htmlspecialchars($k['nama_tutor']); ?></td>
                        <td><?php echo htmlspecialchars($k['nama_ruangan']); ?></td>
                        <td><?php echo htmlspecialchars($k['jumlah_peserta']); ?></td>
                        <td>
                            <a href="edit_jadwal_kelas.php?id=<?php echo urlencode($k['id_kelas']); ?>" class="btn btn-sm btn-primary">Edit Jadwal</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">Belum ada data kelas.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
