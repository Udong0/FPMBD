<?php
// view_tutors.php
// Halaman untuk admin melihat daftar semua tutor terdaftar.

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
$query_tutors = "SELECT NRP_Tutor, Nama_Tutor, Spesialisasi, Status_tutor FROM Tutor ORDER BY Nama_Tutor ASC";
$result = $conn->query($query_tutors);
$tutor_list = $result->fetch_all(MYSQLI_ASSOC);

?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Daftar Semua Tutor</h2>
        <span class="badge bg-success p-2 fs-6">Total: <?php echo count($tutor_list); ?> Tutor</span>
    </div>
    <p class="text-muted">Berikut adalah daftar lengkap semua tutor yang terdaftar di platform beserta status mereka.</p>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>NRP Tutor</th>
                            <th>Nama Lengkap</th>
                            <th>Spesialisasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tutor_list) > 0): ?>
                            <?php foreach ($tutor_list as $tutor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tutor['NRP_Tutor']); ?></td>
                                <td><?php echo htmlspecialchars($tutor['Nama_Tutor']); ?></td>
                                <td><?php echo htmlspecialchars($tutor['Spesialisasi']); ?></td>
                                <td>
                                    <span class="badge <?php echo ($tutor['Status_tutor'] == 'Aktif') ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo htmlspecialchars($tutor['Status_tutor']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center p-4">Belum ada tutor yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
