<?php
// laporan_lanjutan.php
// Halaman untuk menampilkan hasil query JOIN, VIEW, dan FUNCTION.

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php?error=access_denied");
    exit();
}

// --- Logika untuk memanggil FUNCTION ---
$hasil_tunggakan = null;
$mahasiswa_cek_nama = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cek_tunggakan'])) {
    $nrp_mhs = $_POST['nrp_mahasiswa'];
    // Memastikan ada mahasiswa dengan NRP tersebut sebelum memanggil fungsi
    $stmt_nama = $conn->prepare("SELECT Nama_mahasiswa FROM Mahasiswa WHERE NRP_Mahasiswa = ?");
    $stmt_nama->bind_param("s", $nrp_mhs);
    $stmt_nama->execute();
    $result_nama = $stmt_nama->get_result();
    if($result_nama->num_rows > 0) {
        $mahasiswa_cek_nama = $result_nama->fetch_assoc()['Nama_mahasiswa'];
        // Memanggil function
        $stmt_func = $conn->prepare("SELECT fn_Get_Total_Tunggakan_Mahasiswa(?) AS total");
        $stmt_func->bind_param("s", $nrp_mhs);
        $stmt_func->execute();
        $hasil_tunggakan = $stmt_func->get_result()->fetch_assoc()['total'];
    } else {
        $mahasiswa_cek_nama = "NRP tidak ditemukan";
    }
}

$hasil_kelas_tutor = null;
$tutor_cek_nama = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cek_kelas_tutor'])) {
    $nrp_tutor = $_POST['nrp_tutor'];
    // Memastikan ada tutor dengan NRP tersebut
    $stmt_nama = $conn->prepare("SELECT Nama_Tutor FROM Tutor WHERE NRP_Tutor = ?");
    $stmt_nama->bind_param("s", $nrp_tutor);
    $stmt_nama->execute();
    $result_nama = $stmt_nama->get_result();
     if($result_nama->num_rows > 0) {
        $tutor_cek_nama = $result_nama->fetch_assoc()['Nama_Tutor'];
        // Memanggil function
        $stmt_func = $conn->prepare("SELECT F_HitungTotalKelasTutor(?) AS total");
        $stmt_func->bind_param("s", $nrp_tutor);
        $stmt_func->execute();
        $hasil_kelas_tutor = $stmt_func->get_result()->fetch_assoc()['total'];
    } else {
         $tutor_cek_nama = "NRP tidak ditemukan";
    }
}
?>

<div class="container mt-5">
    <h2>Laporan Lanjutan & Statistik</h2>
    <p class="text-muted">Halaman ini mendemonstrasikan hasil dari query kompleks, view, dan function.</p>

    <!-- HASIL DARI QUERY JOIN -->
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h5>Query JOIN 1: Tutor yang Belum Pernah Mengajar</h5></div>
        <div class="card-body">
            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                <table class="table table-sm table-bordered">
                    <thead><tr><th>NRP Tutor</th><th>Nama Tutor</th></tr></thead>
                    <tbody>
                        <?php
                            $sql_join1 = "SELECT t.NRP_Tutor, t.Nama_Tutor FROM Tutor t LEFT JOIN Kelas k ON t.NRP_Tutor = k.Tutor_NRP_Tutor WHERE k.ID_Kelas IS NULL;";
                            $result_join1 = mysqli_query($conn, $sql_join1);
                            while($row = mysqli_fetch_assoc($result_join1)):
                        ?>
                        <tr><td><?php echo $row['NRP_Tutor']; ?></td><td><?php echo $row['Nama_Tutor']; ?></td></tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header"><h5>Query JOIN 2: Mahasiswa yang Pernah Melakukan Pelanggaran</h5></div>
        <div class="card-body">
            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                <table class="table table-sm table-bordered">
                    <thead><tr><th>NRP Mahasiswa</th><th>Nama Mahasiswa</th><th>Email</th></tr></thead>
                    <tbody>
                        <?php
                            $sql_join2 = "SELECT DISTINCT m.NRP_Mahasiswa, m.Nama_mahasiswa, m.Email FROM Mahasiswa m JOIN mahasiswa_pelanggaran mp ON m.NRP_Mahasiswa = mp.Mahasiswa_NRP;";
                            $result_join2 = mysqli_query($conn, $sql_join2);
                            while($row = mysqli_fetch_assoc($result_join2)):
                        ?>
                        <tr><td><?php echo $row['NRP_Mahasiswa']; ?></td><td><?php echo $row['Nama_mahasiswa']; ?></td><td><?php echo $row['Email']; ?></td></tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- HASIL DARI VIEW -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5>VIEW 1: Jumlah Kelas per Tutor</h5></div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Nama Tutor</th><th>Jumlah Kelas</th></tr></thead>
                            <tbody>
                                <?php
                                    $result_view1 = mysqli_query($conn, "SELECT * FROM V_JumlahKelasPerTutor");
                                    while($row = mysqli_fetch_assoc($result_view1)):
                                ?>
                                <tr><td><?php echo $row['Nama_Tutor']; ?></td><td><?php echo $row['Jumlah_Kelas_Ditangani']; ?></td></tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5>VIEW 2: Total Pelanggaran per Tutor</h5></div>
                <div class="card-body">
                     <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Nama Tutor</th><th>Jumlah Pelanggaran</th></tr></thead>
                            <tbody>
                                <?php
                                    $result_view2 = mysqli_query($conn, "SELECT * FROM V_TotalPelanggaranPerTutor");
                                    while($row = mysqli_fetch_assoc($result_view2)):
                                ?>
                                <tr><td><?php echo $row['Nama_Tutor']; ?></td><td><?php echo $row['Jumlah_Pelanggaran']; ?></td></tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- PENGGUNAAN FUNCTION -->
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h5>Demonstrasi FUNCTION</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Cek Tunggakan Mahasiswa</h6>
                    <form method="POST" action="laporan_lanjutan.php">
                        <div class="input-group">
                            <input type="text" name="nrp_mahasiswa" class="form-control" placeholder="Masukkan NRP Mahasiswa" required>
                            <button type="submit" name="cek_tunggakan" class="btn btn-warning">Cek</button>
                        </div>
                    </form>
                    <?php if($hasil_tunggakan !== null): ?>
                        <div class="alert alert-warning mt-2">Total tunggakan untuk <strong><?php echo htmlspecialchars($mahasiswa_cek_nama); ?></strong> adalah: <strong>Rp <?php echo number_format($hasil_tunggakan, 2, ',', '.'); ?></strong></div>
                    <?php elseif(isset($_POST['cek_tunggakan'])): ?>
                         <div class="alert alert-danger mt-2">Mahasiswa dengan NRP tersebut tidak ditemukan.</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h6>Cek Jumlah Kelas Tutor</h6>
                    <form method="POST" action="laporan_lanjutan.php">
                        <div class="input-group">
                            <input type="text" name="nrp_tutor" class="form-control" placeholder="Masukkan NRP Tutor" required>
                            <button type="submit" name="cek_kelas_tutor" class="btn btn-info">Cek</button>
                        </div>
                    </form>
                     <?php if($hasil_kelas_tutor !== null): ?>
                        <div class="alert alert-info mt-2">Total kelas yang diajar oleh <strong><?php echo htmlspecialchars($tutor_cek_nama); ?></strong> adalah: <strong><?php echo $hasil_kelas_tutor; ?> kelas</strong></div>
                    <?php elseif(isset($_POST['cek_kelas_tutor'])): ?>
                         <div class="alert alert-danger mt-2">Tutor dengan NRP tersebut tidak ditemukan.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
