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
                    $sql_pendaftar = "SELECT pt.ID_pendaftaran, m.Nama_mahasiswa, m.NRP_Mahasiswa, pt.Matkul_didaftar FROM Pendaftaran_Tutor pt JOIN Mahasiswa m ON pt.Mahasiswa_NRP_Mahasiswa = m.NRP_Mahasiswa WHERE pt.Status_pendaftar = 'Menunggu'";
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
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="text-center text-muted">Tidak ada pendaftaran tutor baru.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h4>Laporan Pendapatan dan Kinerja Tutor</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Nama Tutor</th>
                        <th>Jumlah Transaksi Sukses</th>
                        <th>Total Pendapatan</th>
                        <th>Total Tips</th>
                        <th>Jml Pelanggaran</th>
                        <th>Disetujui Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // PERUBAHAN 2: Memodifikasi SQL untuk memanggil fungsi SQL kustom Anda
                    // Asumsi: View v_Pendapatan_Tutor memiliki kolom NRP_Tutor
                    $sql_view_pendapatan = "
                        SELECT 
                            vpt.*, 
                            fn_Hitung_Jumlah_Pelanggaran_Tutor(vpt.NRP_Tutor) AS Jumlah_Pelanggaran,
                            fn_Get_Admin_Penyetuju_Tutor(vpt.NRP_Tutor) AS Admin_Penyetuju
                        FROM 
                            v_Pendapatan_Tutor vpt
                    ";
                    $result_view_pendapatan = mysqli_query($conn, $sql_view_pendapatan);

                    if ($result_view_pendapatan && mysqli_num_rows($result_view_pendapatan) > 0):
                        while($row = mysqli_fetch_assoc($result_view_pendapatan)):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Nama_Tutor']); ?></td>
                        <td><?php echo htmlspecialchars($row['Jumlah_Transaksi_Sukses']); ?></td>
                        <td>Rp <?php echo number_format($row['Total_Pendapatan'] ?? 0, 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($row['Total_Tips'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($row['Jumlah_Pelanggaran']); ?></td>
                        <td><?php echo htmlspecialchars($row['Admin_Penyetuju'] ?? 'N/A'); ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" class="text-center text-muted">Tidak ada data pendapatan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="card mb-4">
    <div class="card-header"><h4>Jadwal Kelas Lengkap</h4></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Matkul</th>
                        <th>Jadwal</th>
                        <th>Tutor</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $limit_jadwal = 10;
                    $page_jadwal = isset($_GET['page_jadwal']) ? (int)$_GET['page_jadwal'] : 1;
                    $offset_jadwal = ($page_jadwal - 1) * $limit_jadwal;
                    $sql_total_jadwal = "SELECT COUNT(*) FROM v_Jadwal_Kelas_Lengkap";
                    $result_total_jadwal = mysqli_query($conn, $sql_total_jadwal);
                    $total_records_jadwal = mysqli_fetch_array($result_total_jadwal)[0];
                    $total_pages_jadwal = ceil($total_records_jadwal / $limit_jadwal);
                    $sql_view = "SELECT * FROM v_Jadwal_Kelas_Lengkap ORDER BY Tanggal_Kelas DESC, kelas_start ASC LIMIT ?, ?";
                    $stmt_view = mysqli_prepare($conn, $sql_view);
                    mysqli_stmt_bind_param($stmt_view, "ii", $offset_jadwal, $limit_jadwal);
                    mysqli_stmt_execute($stmt_view);
                    $result_view = mysqli_stmt_get_result($stmt_view);
                    if ($result_view && mysqli_num_rows($result_view) > 0):
                        while($row = mysqli_fetch_assoc($result_view)):
                            $jadwal = date("d M Y", strtotime($row['Tanggal_Kelas'])) . ' (' . date("H:i", strtotime($row['kelas_start'])) . ' - ' . date("H:i", strtotime($row['kelas_end'])) . ')';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Matkul']); ?></td>
                        <td><?php echo $jadwal; ?></td>
                        <td><?php echo htmlspecialchars($row['Nama_Tutor']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nama_Ruangan']); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['Status_kelas']); ?></span></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="text-center text-muted">Tidak ada data jadwal kelas.</td></tr>
                    <?php endif; mysqli_stmt_close($stmt_view); ?>
                </tbody>
            </table>
        </div>
        <nav>
            <ul class="pagination justify-content-end">
                <li class="page-item <?php if($page_jadwal <= 1){ echo 'disabled'; } ?>"><a class="page-link" href="<?php if($page_jadwal <= 1){ echo '#'; } else { echo "?page_jadwal=" . ($page_jadwal - 1); } ?>">Sebelumnya</a></li>
                <li class="page-item <?php if($page_jadwal >= $total_pages_jadwal){ echo 'disabled'; } ?>"><a class="page-link" href="<?php if($page_jadwal >= $total_pages_jadwal){ echo '#'; } else { echo "?page_jadwal=" . ($page_jadwal + 1); } ?>">Selanjutnya</a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h4>Laporan Pendaftaran Tutor yang Ditolak</h4></div>
    <div class="card-body">
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
                    <?php endwhile; else: ?>
                    <tr><td colspan="3" class="text-center text-muted">Tidak ada data pendaftaran yang ditolak.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h4>Laporan Transaksi Lunas</h4></div>
    <div class="card-body">
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
                    $limit_transaksi = 10;
                    $page_transaksi = isset($_GET['page_transaksi']) ? (int)$_GET['page_transaksi'] : 1;
                    $offset_transaksi = ($page_transaksi - 1) * $limit_transaksi;
                    $sql_total_transaksi = "SELECT COUNT(*) FROM Transaksi WHERE Status_Pembayaran = 1";
                    $result_total_transaksi = mysqli_query($conn, $sql_total_transaksi);
                    $total_records_transaksi = mysqli_fetch_array($result_total_transaksi)[0];
                    $total_pages_transaksi = ceil($total_records_transaksi / $limit_transaksi);
                    $sql_transaksi = "SELECT t.ID_Transaksi, m.Nama_mahasiswa AS Mahasiswa_Pembayar, tr.Nama_Tutor AS Tutor_Penerima, k.Matkul, t.Total_Bayar FROM Transaksi t JOIN Mahasiswa m ON t.Mahasiswa_NRP_mahasiswa = m.NRP_Mahasiswa JOIN Tutor tr ON t.Tutor_NRP_Tutor = tr.NRP_Tutor JOIN Kelas k ON t.Kelas_ID_Kelas = k.ID_Kelas WHERE t.Status_Pembayaran = 1 ORDER BY t.Tanggal_Bayar DESC LIMIT ?, ?";
                    $stmt_transaksi = mysqli_prepare($conn, $sql_transaksi);
                    mysqli_stmt_bind_param($stmt_transaksi, "ii", $offset_transaksi, $limit_transaksi);
                    mysqli_stmt_execute($stmt_transaksi);
                    $result_transaksi = mysqli_stmt_get_result($stmt_transaksi);
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
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="text-center text-muted">Belum ada transaksi yang lunas.</td></tr>
                    <?php endif; mysqli_stmt_close($stmt_transaksi); ?>
                </tbody>
            </table>
        </div>
        <nav>
            <ul class="pagination justify-content-end">
                <li class="page-item <?php if($page_transaksi <= 1){ echo 'disabled'; } ?>"><a class="page-link" href="<?php if($page_transaksi <= 1){ echo '#'; } else { echo "?page_transaksi=" . ($page_transaksi - 1); } ?>">Sebelumnya</a></li>
                <li class="page-item <?php if($page_transaksi >= $total_pages_transaksi){ echo 'disabled'; } ?>"><a class="page-link" href="<?php if($page_transaksi >= $total_pages_transaksi){ echo '#'; } else { echo "?page_transaksi=" . ($page_transaksi + 1); } ?>">Selanjutnya</a></li>
            </ul>
        </nav>
    </div>
</div>

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
