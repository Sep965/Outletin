<?php
include '../includes/auth.php';
require '../includes/koneksi.php';

$userRole = $_SESSION['user_role'] ?? 'user';
if ($userRole !== 'franchisor') {
    header("Location: brand.php");
    exit;
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$message = '';

$stmt = $koneksi->prepare("
    SELECT
        b.*,
        COALESCE(NULLIF(v.status, ''), 'pending') AS verification_status
    FROM brands b
    LEFT JOIN verifications v ON b.brand_id = v.brand_id
    WHERE b.franchisor_id = ?
      AND COALESCE(NULLIF(v.status, ''), 'pending') = 'verified'
    ORDER BY b.brand_id DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$pendingStmt = $koneksi->prepare("
    SELECT COUNT(*) AS total_pending
    FROM brands b
    LEFT JOIN verifications v ON b.brand_id = v.brand_id
    WHERE b.franchisor_id = ?
      AND COALESCE(NULLIF(v.status, ''), 'pending') <> 'verified'
");
$pendingStmt->bind_param("i", $userId);
$pendingStmt->execute();
$pendingData = $pendingStmt->get_result()->fetch_assoc();
$pendingCount = (int) ($pendingData['total_pending'] ?? 0);

$msg = $_GET['msg'] ?? '';

if ($msg === 'menunggu_verifikasi') {
    $message = 'Brand berhasil dikirim dan sekarang menunggu verifikasi superadmin. Brand akan tampil di daftar setelah statusnya verified.';
} elseif ($msg === 'hapus_berhasil') {
    $message = 'Brand berhasil dihapus.';
} elseif ($msg === 'hapus_gagal_terkait') {
    $message = 'Brand tidak bisa dihapus karena masih terhubung dengan data verifikasi. Hapus atau ubah data terkait terlebih dahulu.';
} elseif ($msg === 'hapus_tidak_ditemukan') {
    $message = 'Brand tidak ditemukan atau Anda tidak punya akses untuk menghapusnya.';
} elseif ($msg === 'hapus_invalid') {
    $message = 'Permintaan hapus brand tidak valid.';
} elseif ($msg === 'hapus_gagal') {
    $message = 'Terjadi kesalahan saat menghapus brand.';
}
?>

<?php include 'partials/header.php'; ?>

<div class="max-w-6xl mx-auto mt-8">

    <?php if ($message): ?>
        <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($pendingCount > 0): ?>
        <div class="mb-5 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-800">
            Anda punya <?= $pendingCount ?> brand yang masih menunggu verifikasi admin, jadi belum tampil di daftar brand aktif.
        </div>
    <?php endif; ?>

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-red-900">Daftar Brand</h1>
            <p class="mt-1 text-sm text-slate-600">Hanya brand dengan status <span class="font-semibold text-red-800">verified</span> yang tampil di sini.</p>
        </div>
        <a href="register_brand.php" class="btn">+ Tambah Brand</a>
    </div>

    <!-- CARD TABLE -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-red-100">

        <table class="w-full text-left">
            <thead class="bg-red-900 text-white">
                <tr>
                    <th class="p-4">Nama Brand</th>
                    <th class="p-4">Deskripsi</th>
                    <th class="p-4 text-center">Produk</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y">

                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-red-50 transition">

                            <td class="p-4 font-semibold text-gray-800">
                                <?= htmlspecialchars($row['brand_name']) ?>
                            </td>

                            <td class="p-4 text-gray-600">
                                <?= htmlspecialchars($row['description']) ?>
                            </td>

                            <td class="p-4 text-center">
                                <a href="create_product.php?brand_id=<?= $row['brand_id'] ?>"
                                   class="text-sm bg-red-100 text-red-800 px-3 py-1 rounded hover:bg-red-200">
                                    + Produk
                                </a>
                                <br><br>
                                <a href="read_product.php?brand_id=<?= $row['brand_id'] ?>"
                                   class="text-sm text-blue-600 hover:underline">
                                    Lihat
                                </a>
                            </td>

                            <td class="p-4 text-center space-x-2">
                                <a href="brand_edit.php?id=<?= $row['brand_id'] ?>"
                                   class="px-3 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 text-sm">
                                    Edit
                                </a>

                                <a href="brand_delete.php?id=<?= $row['brand_id'] ?>"
                                   onclick="return confirm('Yakin hapus brand ini?')"
                                   class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                    Hapus
                                </a>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>

                    <tr>
                        <td colspan="4" class="p-6 text-center text-gray-500">
                            Belum ada brand. Silakan tambahkan brand pertama Anda 🚀
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>

<?php include 'partials/footer.php'; ?>
