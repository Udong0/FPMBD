<?php
// laporkan_pelanggaran.php
// Halaman bagi mahasiswa untuk melaporkan tutor terkait sebuah kelas.

include('includes/header.php');
include('config/db_connect.php');

// Pastikan session sudah aktif
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Keamanan: Pastikan hanya mahasiswa yang login yang bisa mengakses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa') {
    header("Location: login.php?error=access_denied");
    exit();
}

// Ambil NRP mahasiswa dari session
$mahasiswa_nrp = $_SESSION['user_nrp'];

// Query untuk mengambil kelas yang diikuti oleh mahasiswa yang sedang login.
// Ini penting agar mahasiswa hanya bisa melaporkan kelas yang benar-benar mereka ikuti.
$query_kelas = "
    SELECT k.ID_Kelas, k.Matkul, t.Nama_Tutor 
    FROM Kelas k
    JOIN mahasiswa_kelas mk ON k.ID_Kelas = mk.Kelas_ID
    JOIN Tutor t ON k.Tutor_NRP_Tutor = t.NRP_Tutor
    WHERE mk.Mahasiswa_NRP = ? AND t.Status_tutor = 'Aktif'
    ORDER BY k.Tanggal_Kelas DESC
";

$stmt = $conn->prepare($query_kelas);
$stmt->bind_param("s", $mahasiswa_nrp);
$stmt->execute();
$result_kelas = $stmt->get_result();
$kelas_diikuti = $result_kelas->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h3 class="card-title mb-0">Form Laporan Pelanggaran</h3>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted">Laporan Anda bersifat rahasia dan akan ditinjau langsung oleh administrator. Harap isi data dengan sebenar-benarnya.</p>
                    
                    <form action="proses_lapor.php" method="POST">
                        <div class="mb-3">
                            <label for="kelas_id" class="form-label fw-bold">Pilih Kelas yang Bermasalah</label>
                            <select name="kelas_id" id="kelas_id" class="form-select" required>
                                <option value="" disabled selected>-- Pilih dari daftar kelas yang Anda ikuti --</option>
                                <?php foreach ($kelas_diikuti as $kelas) : ?>
                                    <option value="<?php echo htmlspecialchars($kelas['ID_Kelas']); ?>">
                                        <?php echo htmlspecialchars($kelas['Matkul']) . ' (Tutor: ' . htmlspecialchars($kelas['Nama_Tutor']) . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if (empty($kelas_diikuti)): ?>
                                    <option value="" disabled>Anda tidak terdaftar di kelas aktif manapun.</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tipe_pelanggaran" class="form-label fw-bold">Subjek Pelanggaran</label>
                            <input type="text" name="tipe_pelanggaran" id="tipe_pelanggaran" class="form-control" placeholder="Contoh: Tutor tidak hadir, Materi tidak relevan, Perilaku tidak pantas" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label fw-bold">Deskripsi Rinci</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5" placeholder="Jelaskan kronologi kejadian selengkap mungkin." required></textarea>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" name="submit" class="btn btn-danger btn-lg" <?php if (empty($kelas_diikuti)) echo 'disabled'; ?>>Kirim Laporan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
