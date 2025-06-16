<?php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa') {
    echo "<div class='alert alert-danger'>Akses Ditolak. Halaman ini hanya untuk Mahasiswa.</div>";
    require_once 'includes/footer.php';
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header"><h3 class="text-center font-weight-light my-4">Formulir Pendaftaran Tutor</h3></div>
            <div class="card-body">
                <p class="text-muted">Isi formulir di bawah ini untuk mengajukan diri sebagai tutor. Pengajuan Anda akan diverifikasi oleh Admin.</p>
                <form action="proses_daftar_tutor.php" method="POST">
                    <div class="mb-3">
                        <label for="matkul" class="form-label">Mata Kuliah Keahlian</label>
                        <input type="text" class="form-control" id="matkul" name="matkul" placeholder="Contoh: Kalkulus, Basis Data, Fisika Dasar" required>
                        <div class="form-text">Tuliskan mata kuliah yang menjadi keahlian Anda, pisahkan dengan koma jika lebih dari satu.</div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Ajukan Pendaftaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
