<?php
// LOKASI: /myitstutor/manajemen_daftar_ruangan.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'sarpras') {
    header("Location: login.php?error=access_denied");
    exit();
}

$status_msg = '';

// --- Tambah atau Hapus ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $id       = 'RNG' . str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
        $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
        $dept     = mysqli_real_escape_string($conn, $_POST['departemen']);
        $lokasi   = mysqli_real_escape_string($conn, $_POST['lokasi']);

        $stmt = $conn->prepare("
            INSERT INTO Ruangan (ID_Ruangan, Nama_Ruangan, Departemen, Lokasi)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $id, $nama, $dept, $lokasi);
        if ($stmt->execute()) {
            $status_msg = "<div class='alert alert-success'>Ruangan berhasil ditambahkan.</div>";
        } else {
            $status_msg = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();

    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id_ruangan'];
        $stmt = $conn->prepare("DELETE FROM Ruangan WHERE ID_Ruangan = ?");
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) {
            $status_msg = "<div class='alert alert-success'>Ruangan berhasil dihapus.</div>";
        } else {
            $status_msg = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Ambil daftar ruangan
$result = $conn->query("SELECT * FROM Ruangan ORDER BY Nama_Ruangan");
?>

<div class="container mt-5">
    <h2>Manajemen Daftar Ruangan</h2>
    <?php echo $status_msg; ?>

    <!-- Form Tambah Ruangan -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">Tambah Ruangan Baru</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label">Nama Ruangan</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Departemen</label>
                    <input type="text" name="departemen" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="lokasi" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Tambah Ruangan</button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Ruangan -->
    <div class="card shadow-sm">
        <div class="card-header">Daftar Ruangan</div>
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID Ruangan</th>
                        <th>Nama</th>
                        <th>Departemen</th>
                        <th>Lokasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_Ruangan']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nama_Ruangan']); ?></td>
                        <td><?php echo htmlspecialchars($row['Departemen']); ?></td>
                        <td><?php echo htmlspecialchars($row['Lokasi']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_ruangan" value="<?php echo $row['ID_Ruangan']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus ruangan ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows === 0): ?>
                    <tr><td colspan="5" class="text-center text-muted">Belum ada ruangan terdaftar.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
