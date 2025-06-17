<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: hanya admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?error=access_denied");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='alert alert-danger'>ID Kelas tidak valid.</div>";
    require_once 'includes/footer.php';
    exit();
}
$id_kelas = $_GET['id'];

// Ambil data kelas
$sql = "SELECT * FROM Kelas WHERE ID_Kelas = '$id_kelas'";
$result = mysqli_query($conn, $sql);
$kelas = mysqli_fetch_assoc($result);

// Ambil data ruangan untuk dropdown
$ruangan_q = mysqli_query($conn, "SELECT ID_Ruangan, Nama_Ruangan FROM Ruangan ORDER BY Nama_Ruangan");
?>

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Edit Jadwal Kelas</h3>
        </div>
        <div class="card-body">
            <?php if ($kelas): ?>
                <form action="proses_edit_jadwal_kelas.php" method="POST">
                    <input type="hidden" name="id_kelas" value="<?php echo htmlspecialchars($kelas['ID_Kelas']); ?>">

                    <div class="mb-3">
                        <label class="form-label">Mata Kuliah</label>
                        <input type="text" name="matkul" class="form-control" value="<?php echo htmlspecialchars($kelas['Matkul']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Kelas</label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo htmlspecialchars($kelas['Tanggal_Kelas']); ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Mulai</label>
                            <input type="time" name="jam_mulai" class="form-control" value="<?php echo htmlspecialchars($kelas['kelas_start']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Selesai</label>
                            <input type="time" name="jam_selesai" class="form-control" value="<?php echo htmlspecialchars($kelas['kelas_end']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ruangan</label>
                        <select name="id_ruangan" class="form-select" required>
                            <?php while ($r = mysqli_fetch_assoc($ruangan_q)): ?>
                                <option value="<?php echo $r['ID_Ruangan']; ?>"
                                    <?php if ($kelas['Ruangan_ID_Ruangan'] == $r['ID_Ruangan']) echo "selected"; ?>>
                                    <?php echo htmlspecialchars($r['Nama_Ruangan']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="view_kelas.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-danger">Data kelas tidak ditemukan.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
