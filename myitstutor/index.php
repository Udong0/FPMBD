<?php
require_once 'includes/header.php';
?>

<div class="p-5 mb-4 bg-body-tertiary rounded-3 text-center">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold">Selamat Datang di myITSTutor</h1>
        <p class="fs-4">Platform bimbingan belajar dari mahasiswa untuk mahasiswa. Temukan kelas yang Anda butuhkan atau jadilah tutor untuk berbagi ilmu.</p>
        <a href="login.php" class="btn btn-primary btn-lg mx-2">Login</a>
        <a href="register.php" class="btn btn-secondary btn-lg mx-2">Daftar Sekarang</a>
    </div>
</div>

<div class="row align-items-md-stretch">
    <div class="col-md-6 mb-4">
        <div class="h-100 p-5 text-bg-dark rounded-3">
            <h2>Jadi Mahasiswa</h2>
            <p>Akses ratusan kelas yang diajarkan oleh tutor-tutor berpengalaman di bidangnya. Tingkatkan pemahaman materi kuliah Anda dengan mudah.</p>
            <a href="register.php" class="btn btn-outline-light">Lihat Daftar Kelas</a>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="h-100 p-5 bg-body-tertiary border rounded-3">
            <h2>Jadi Tutor</h2>
            <p>Punya keahlian di mata kuliah tertentu? Bagikan ilmu Anda, bantu teman-teman, dan dapatkan penghasilan tambahan dengan menjadi tutor di myITSTutor.</p>
            <a href="register.php" class="btn btn-outline-secondary">Daftar Jadi Tutor</a>
        </div>
    </div>
</div>


<?php
require_once 'includes/footer.php';
?>
