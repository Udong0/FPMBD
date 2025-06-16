<?php
// view_mahasiswa.php
// Halaman untuk admin melihat daftar semua mahasiswa terdaftar.

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

// --- Logika Ambil Data ---
$query_mahasiswa = "SELECT NRP_Mahasiswa, Nama_mahasiswa, Email FROM Mahasiswa ORDER BY Nama_mahasiswa ASC";
$result = $conn->query($query_mahasiswa);
$mahasiswa_list = $result->fetch_all(MYSQLI_ASSOC);

?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Daftar Semua Mahasiswa</h2>
        <span class="badge bg-primary p-2 fs-6">Total: <?php echo count($mahasiswa_list); ?> Mahasiswa</span>
    </div>
    <p class="text-muted">Berikut adalah daftar lengkap semua mahasiswa yang telah terdaftar di platform MyITSTutor.</p>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>NRP Mahasiswa</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($mahasiswa_list) > 0): ?>
                            <?php foreach ($mahasiswa_list as $mahasiswa): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mahasiswa['NRP_Mahasiswa']); ?></td>
                                <td><?php echo htmlspecialchars($mahasiswa['Nama_mahasiswa']); ?></td>
                                <td><?php echo htmlspecialchars($mahasiswa['Email']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center p-4">Belum ada mahasiswa yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
