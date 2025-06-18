<?php
// LOKASI: /myitstutor/dashboard_admin.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: Cek apakah user sudah login dan perannya adalah admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    echo "<div class='alert alert-danger'>Akses Ditolak. Anda harus login sebagai Admin untuk mengakses halaman ini.</div>";
    require_once 'includes/footer.php';
    exit();
}

$user_nama = $_SESSION['user_nama'];
?>

<h1 class="mt-4">Dashboard Admin</h1>
<p class="lead">Selamat datang, Admin <?php echo htmlspecialchars($user_nama); ?>!</p>
<hr>

<!-- Panel Verifikasi Pendaftaran Tutor (dari file Anda) -->
<div class="card mb-4">
    <div class="card-header">
        <h4>Verifikasi Pendaftaran Tutor</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID Pendaftaran</th>
                        <th>Nama Mahasiswa</th>
                        <th>NRP</th>
                        <th>Keahlian Diajukan</th>
                        <th style="width: 20%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_pendaftar = "SELECT pt.ID_pendaftaran, m.Nama_mahasiswa, m.NRP_Mahasiswa, pt.Matkul_didaftar 
                                      FROM Pendaftaran_Tutor pt
                                      JOIN Mahasiswa m ON pt.Mahasiswa_NRP_Mahasiswa = m.NRP_Mahasiswa
                                      WHERE pt.Status_pendaftar = 'Menunggu'";
                    $result_pendaftar = mysqli_query($conn, $sql_pendaftar);

                    if ($result_pendaftar && mysqli_num_rows($result_pendaftar) > 0):
                        while($pendaftar = mysqli_fetch_assoc($result_pendaftar)):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pendaftar['ID_pendaftaran']); ?></td>
                        <td><?php echo htmlspecialchars($pendaftar['Nama_mahasiswa']); ?></td>
                        <td><?php echo htmlspecialchars($pendaftar['NRP_Mahasiswa']); ?></td>
                        <td><?php echo htmlspecialchars($pendaftar['Matkul_didaftar']); ?></td>
                        <td>
                            <form action="proses_verifikasi_tutor.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_pendaftaran" value="<?php echo $pendaftar['ID_pendaftaran']; ?>">
                                <input type="hidden" name="nrp_mahasiswa" value="<?php echo $pendaftar['NRP_Mahasiswa']; ?>">
                                <input type="hidden" name="matkul" value="<?php echo $pendaftar['Matkul_didaftar']; ?>">
                                <button type="submit" name="action" value="terima" class="btn btn-success btn-sm">Setujui</button>
                                <button type="submit" name="action" value="tolak" class="btn btn-danger btn-sm">Tolak</button>
                            </form>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Tidak ada pendaftaran tutor baru yang perlu diverifikasi.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Panel Laporan Pendaftaran Tutor yang Ditolak -->
<div class="card mb-4">
    <div class="card-header">
        <h4>Laporan Pendaftaran Tutor yang Ditolak</h4>
    </div>
    <div class="card-body">
        <p class="text-muted">Laporan ini dibuat menggunakan query JOIN antara tabel `Pendaftaran_Tutor` dan `Mahasiswa`.</p>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Nama Mahasiswa</th>
                        <th>Mata Kuliah Diajukan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_join = "SELECT m.Nama_mahasiswa, pt.Matkul_didaftar, pt.Status_pendaftar FROM Pendaftaran_Tutor pt JOIN Mahasiswa m ON pt.Mahasiswa_NRP_Mahasiswa = m.NRP_Mahasiswa WHERE pt.Status_pendaftar = 'Ditolak'";
                    $result_join = mysqli_query($conn, $sql_join);
                    if ($result_join && mysqli_num_rows($result_join) > 0):
                        while($row = mysqli_fetch_assoc($result_join)):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Nama_mahasiswa']); ?></td>
                        <td><?php echo htmlspecialchars($row['Matkul_didaftar']); ?></td>
                        <td><span class="badge bg-danger"><?php echo htmlspecialchars($row['Status_pendaftar']); ?></span></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">Tidak ada data pendaftaran yang ditolak.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- FITUR BARU: Panel Laporan Transaksi Lunas dengan Paginasi -->
<div class="card mb-4">
    <div class="card-header">
        <h4>Laporan Transaksi Lunas</h4>
    </div>
    <div class="card-body">
        <p class="text-muted">Laporan ini dibuat menggunakan query JOIN antara 4 tabel dan dilengkapi dengan sistem halaman (paginasi).</p>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-light">
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Mahasiswa Pembayar</th>
                        <th>Tutor Penerima</th>
                        <th>Mata Kuliah</th>
                        <th>Total Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // --- Logika Paginasi ---
                    $limit = 10; // Jumlah record per halaman
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($page - 1) * $limit;

                    // Query untuk menghitung total record
                    $sql_total = "SELECT COUNT(*) FROM Transaksi WHERE Status_Pembayaran = 1";
                    $result_total = mysqli_query($conn, $sql_total);
                    $total_records = mysqli_fetch_array($result_total)[0];
                    $total_pages = ceil($total_records / $limit);

                    // Query JOIN utama dengan LIMIT dan OFFSET
                    $sql_transaksi = "SELECT
                                        t.ID_Transaksi,
                                        m.Nama_mahasiswa AS Mahasiswa_Pembayar,
                                        tr.Nama_Tutor AS Tutor_Penerima,
                                        k.Matkul,
                                        t.Total_Bayar
                                    FROM
                                        Transaksi t
                                    JOIN
                                        Mahasiswa m ON t.Mahasiswa_NRP_mahasiswa = m.NRP_Mahasiswa
                                    JOIN
                                        Tutor tr ON t.Tutor_NRP_Tutor = tr.NRP_Tutor
                                    JOIN
                                        Kelas k ON t.Kelas_ID_Kelas = k.ID_Kelas
                                    WHERE
                                        t.Status_Pembayaran = 1
                                    ORDER BY t.Tanggal_Bayar DESC
                                    LIMIT ?, ?";
                    
                    $stmt = mysqli_prepare($conn, $sql_transaksi);
                    mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
                    mysqli_stmt_execute($stmt);
                    $result_transaksi = mysqli_stmt_get_result($stmt);

                    if ($result_transaksi && mysqli_num_rows($result_transaksi) > 0):
                        while($row = mysqli_fetch_assoc($result_transaksi)):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_Transaksi']); ?></td>
                        <td><?php echo htmlspecialchars($row['Mahasiswa_Pembayar']); ?></td>
                        <td><?php echo htmlspecialchars($row['Tutor_Penerima']); ?></td>
                        <td><?php echo htmlspecialchars($row['Matkul']); ?></td>
                        <td>Rp <?php echo number_format($row['Total_Bayar'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada transaksi yang lunas.</td>
                    </tr>
                    <?php endif; mysqli_stmt_close($stmt); ?>
                </tbody>
            </table>
        </div>
        
        <!-- Navigasi Paginasi -->
        <nav aria-label="Navigasi Halaman Laporan">
            <ul class="pagination justify-content-end">
                <!-- Tombol Sebelumnya -->
                <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>">
                    <a class="page-link" href="<?php if($page <= 1){ echo '#'; } else { echo "?page=" . ($page - 1); } ?>">Sebelumnya</a>
                </li>
                <!-- Tombol Selanjutnya -->
                <li class="page-item <?php if($page >= $total_pages){ echo 'disabled'; } ?>">
                    <a class="page-link" href="<?php if($page >= $total_pages){ echo '#'; } else { echo "?page=" . ($page + 1); } ?>">Selanjutnya</a>
                </li>
            </ul>
        </nav>

    </div>
</div>


<!-- Menu Panel Kontrol Lainnya (dari file Anda) -->
<h3>Menu Panel Kontrol Lainnya</h3>
<div class="list-group">
    <a href="manajemen_pelanggaran.php" class="list-group-item list-group-item-action"><i class="fas fa-gavel me-2"></i>Manajemen Pelanggaran</a>
    <a href="manajemen_tutor.php" class="list-group-item list-group-item-action">Manajemen Role Tutor</a>
    <a href="ajukan_peminjaman.php" class="list-group-item list-group-item-action">Ajukan Peminjaman Ruangan</a>
    <a href="#" class="list-group-item list-group-item-action">Manajemen Peminjaman Ruangan</a>
    <a href="manajemen_pencairan.php" class="list-group-item list-group-item-action">Persetujuan Pencairan Dana Tutor</a>
    <li class="list-group-item list-group-item-secondary">Lihat Semua Data</li>
    <a href="view_mahasiswa.php" class="list-group-item list-group-item-action">Daftar Mahasiswa</a>
    <a href="view_tutors.php" class="list-group-item list-group-item-action">Daftar Tutor</a>
    <a href="view_kelas.php" class="list-group-item list-group-item-action">Daftar Kelas</a>
    <a href="view_transaksi.php" class="list-group-item list-group-item-action">Riwayat Transaksi</a>
</div>

<?php
require_once 'includes/footer.php';
?>
