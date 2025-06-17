<?php
// jadwal_penggunaan_ruangan.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: hanya Sarpras
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'sarpras') {
    header("Location: login.php?error=access_denied");
    exit();
}
?>

<div class="container mt-5">
    <h2>Jadwal Peminjaman Ruangan Mendatang</h2>
    <p class="text-muted">Semua permohonan peminjaman ruangan dengan tanggal pinjam hari ini atau yang akan datang.</p>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID Peminjaman</th>
                        <th>Tujuan</th>
                        <th>Waktu Pinjam</th>
                        <th>Ruangan</th>
                        <th>Penanggung Jawab</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT id_peminjaman, ruangan_tujuan, tanggal_pinjam, nama_ruangan, penanggung_jawab
                        FROM vw_upcoming_peminjaman_ruangan";
                $res = mysqli_query($conn, $sql);
                if ($res && mysqli_num_rows($res) > 0):
                    while ($row = mysqli_fetch_assoc($res)):
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_peminjaman']); ?></td>
                        <td><?php echo htmlspecialchars($row['ruangan_tujuan']); ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($row['tanggal_pinjam'])); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_ruangan']); ?></td>
                        <td><?php echo htmlspecialchars($row['penanggung_jawab']); ?></td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Tidak ada jadwal peminjaman mendatang.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
