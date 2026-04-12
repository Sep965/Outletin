<?php
include '../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';

$userId = (int) ($_SESSION['user_id'] ?? 0);
$userRole = $_SESSION['user_role'] ?? 'user';

if ($userRole !== 'franchisor') {
    header("Location: dashboard.php");
    exit;
}

/* =========================
   CEK LOCK TAMBAH BRAND
========================= */
$stmt = $koneksi->prepare("
    SELECT v.status
    FROM brands b
    LEFT JOIN verifications v ON b.brand_id = v.brand_id
    WHERE b.franchisor_id = ?
    AND (v.status IS NULL OR v.status != 'verified')
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$check = $stmt->get_result()->fetch_assoc();

$lockTambah = false;
$status = '';

if ($check) {
    $lockTambah = true;
    $status = $check['status'] ?? 'pending';
}

/* =========================
   FORM INPUT
========================= */
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 🔒 PROTEKSI TAMBAHAN (ANTI BYPASS)
    if ($lockTambah) {
        die("Tidak bisa menambah brand sebelum verifikasi selesai.");
    }

    $brandName = trim($_POST['brand_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($brandName === '' || $description === '') {
        $error = 'Semua field wajib diisi.';
    } else {

        try {
            $stmt = $koneksi->prepare("
                INSERT INTO brands (franchisor_id, brand_name, description, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->bind_param("iss", $userId, $brandName, $description);
            $stmt->execute();

            $brandId = $koneksi->insert_id;

            // AUTO MASUK VERIFIKASI
            $stmt = $koneksi->prepare("
                INSERT INTO verifications (brand_id, status, verified_at)
                VALUES (?, 'pending', NOW())
            ");
            $stmt->bind_param("i", $brandId);
            $stmt->execute();

            header("Location: brand_list.php");
            exit;

        } catch (Throwable $e) {
            $error = 'Gagal menyimpan brand.';
        }
    }
}
?>

<?php include 'partials/header.php'; ?>

<div class="mx-auto max-w-2xl card">

    <h1 class="text-2xl font-bold text-red-900">Daftarkan Brand</h1>

    <?php if ($error): ?>
        <div class="mt-4 bg-red-100 p-3 text-red-700 rounded"><?= $error ?></div>
    <?php endif; ?>

    <!-- 🔒 JIKA MASIH ADA BRAND BELUM VERIFIED -->
    <?php if ($lockTambah): ?>

        <div class="mt-4 bg-yellow-100 text-yellow-800 p-4 rounded">
            🚫 Anda masih memiliki brand dengan status 
            <b><?= htmlspecialchars($status) ?></b>.  
            Silakan tunggu verifikasi admin sebelum menambah brand baru.
        </div>

    <?php else: ?>

        <!-- ✅ FORM MUNCUL HANYA JIKA BOLEH TAMBAH -->
        <form method="post" class="mt-6 space-y-4">

            <input type="text" name="brand_name" placeholder="Nama Brand" class="input" required>

            <textarea name="description" placeholder="Deskripsi" class="input" required></textarea>

            <button type="submit" class="btn w-full">Simpan</button>
        </form>

    <?php endif; ?>

</div>

<?php include 'partials/footer.php'; ?>