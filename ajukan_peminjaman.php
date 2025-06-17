<?php
// ajukan_peminjaman.php
// Halaman bagi admin untuk mengajukan permohonan peminjaman ruangan.

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

$admin_id = $_SESSION['user_id'];
$status_msg = '';

// --- Logika Proses Form Pengajuan ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajukan_peminjaman'])) {
    $ruangan_id = $_POST['ruangan_id'];
    $tujuan_peminjaman = mysqli_real_escape_string($conn, $_POST['tujuan_peminjaman']);
    $tanggal_pinjam = $_POST['tanggal_pinjam']; // Format Y-m-d\TH:i dari input datetime-local

    // Generate ID Peminjaman unik, contoh: PMJXXX
    $id_peminjaman = 'PMJ' . str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
    
    // Asumsi ada ID Sarpras default untuk ditugaskan, atau bisa dibuat null dulu
    // Untuk contoh ini, kita butuh setidaknya satu user Sarpras di database
    $sarpras_id_default = 'SPR001'; // Ganti dengan ID Sarpras yang valid

   $query_insert = "
    INSERT INTO Peminjaman_Ruangan 
    (ID_Peminjaman, Ruangan_tujuan, Tanggal_Pinjam, admin_ID_admin, Ruangan_ID_Ruangan, Sarpras_ID_sarpras, Status_peminja) 
    VALUES (?, ?, ?, ?, ?, ?, 'Menunggu')
";

    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bind_param("ssssss", $id_peminjaman, $tujuan_peminjaman, $tanggal_pinjam, $admin_id, $ruangan_id, $sarpras_id_default);

    if ($stmt_insert->execute()) {
        $status_msg = "<div class='alert alert-success'>Permohonan peminjaman ruangan berhasil diajukan.</div>";
    } else {
        $status_msg = "<div class='alert alert-danger'>Gagal mengajukan permohonan. Error: " . $stmt_insert->error . "</div>";
    }
    $stmt_insert->close();
}


// --- Logika Ambil Data untuk Form ---
// Ambil daftar semua ruangan
$query_ruangan = "SELECT ID_Ruangan, Nama_Ruangan FROM Ruangan ORDER BY Nama_Ruangan";
$result_ruangan = $conn->query($query_ruangan);
$ruangan_list = $result_ruangan->fetch_all(MYSQLI_ASSOC);

?>

<div class="container mt-5">
    <h2>Formulir Peminjaman Ruangan</h2>
    <p class="text-muted">Ajukan permohonan peminjaman ruangan untuk kegiatan tutoring atau acara lainnya.</p>

    <?php echo $status_msg; ?>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Detail Permohonan</h5>
        </div>
        <div class="card-body">
            <form action="ajukan_peminjaman.php" method="POST">
                <div class="mb-3">
                    <label for="tujuan_peminjaman" class="form-label">Tujuan Peminjaman / Nama Kegiatan</label>
                    <input type="text" name="tujuan_peminjaman" id="tujuan_peminjaman" class="form-control" placeholder="Contoh: Kelas Tambahan Kalkulus, Rapat Tutor" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ruangan_id" class="form-label">Pilih Ruangan</label>
                        <select name="ruangan_id" id="ruangan_id" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Ruangan --</option>
                            <?php foreach ($ruangan_list as $ruangan): ?>
                                <option value="<?php echo htmlspecialchars($ruangan['ID_Ruangan']); ?>">
                                    <?php echo htmlspecialchars($ruangan['Nama_Ruangan']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_pinjam" class="form-label">Tanggal & Waktu Peminjaman</label>
                        <input type="datetime-local" name="tanggal_pinjam" id="tanggal_pinjam" class="form-control" required>
                    </div>
                </div>
                <button type="submit" name="ajukan_peminjaman" class="btn btn-primary">Ajukan Permohonan</button>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
