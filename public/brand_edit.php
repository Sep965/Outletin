<?php
include '../includes/auth.php';
require '../includes/koneksi.php';

$userId = (int) ($_SESSION['user_id'] ?? 0);
$id = (int) ($_GET['id'] ?? 0);
$error = '';

if ($id <= 0) {
    header("Location: brand_list.php");
    exit;
}

$stmt = $koneksi->prepare("
    SELECT * FROM brands
    WHERE brand_id = ? AND franchisor_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $id, $userId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: brand_list.php");
    exit;
}

$brandName = $data['brand_name'] ?? '';
$description = $data['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brandName = trim($_POST['brand_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($brandName === '' || $description === '') {
        $error = 'Nama brand dan deskripsi wajib diisi.';
    } else {
        $update = $koneksi->prepare("
            UPDATE brands
            SET brand_name = ?, description = ?
            WHERE brand_id = ? AND franchisor_id = ?
        ");
        $update->bind_param("ssii", $brandName, $description, $id, $userId);

        if ($update->execute()) {
            header("Location: brand_list.php");
            exit;
        }

        $error = 'Gagal memperbarui brand. Coba lagi.';
    }
}
?>

<?php include 'partials/header.php'; ?>
<div class="mx-auto mt-9 max-w-6xl px-4 sm:px-6">
    <div class="grid gap-7 lg:grid-cols-[0.95fr_1.05fr] lg:items-stretch">
        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#6f1020] via-[#8a1830] to-[#af3450] p-8 text-rose-50 shadow-[0_22px_60px_rgba(111,16,32,0.22)] sm:p-9">
            <div class="pointer-events-none absolute -right-10 -top-14 h-[210px] w-[210px] rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-14 -left-8 h-[170px] w-[170px] rounded-full bg-rose-100/10"></div>

            <span class="relative z-10 inline-flex rounded-full border border-white/20 bg-white/10 px-3.5 py-2 text-xs font-bold uppercase tracking-[0.18em]">
                Kelola Brand
            </span>
            <h1 class="relative z-10 mt-4 text-3xl font-extrabold leading-tight sm:text-[34px]">
                Edit identitas brand dengan tampilan yang lebih rapi.
            </h1>
            <p class="relative z-10 mt-3 text-[15px] leading-8 text-rose-50/90">
                Perbarui nama brand dan deskripsi agar informasi yang tampil ke tim dan calon mitra tetap jelas, konsisten, dan meyakinkan.
            </p>

            <div class="relative z-10 mt-6 grid gap-3.5">
                <div class="rounded-[20px] border border-white/15 bg-white/10 p-[18px] backdrop-blur-sm">
                    <strong class="mb-1.5 block text-sm">Brand saat ini</strong>
                    <span class="text-[13px] leading-7 text-rose-50/85"><?= htmlspecialchars($data['brand_name'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="rounded-[20px] border border-white/15 bg-white/10 p-[18px] backdrop-blur-sm">
                    <strong class="mb-1.5 block text-sm">Tips cepat</strong>
                    <span class="text-[13px] leading-7 text-rose-50/85">Gunakan deskripsi singkat yang menjelaskan keunggulan utama dan karakter brand kamu.</span>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[28px] border border-rose-100 bg-white shadow-[0_18px_50px_rgba(127,29,29,0.12)]">
            <div class="border-b border-rose-100 bg-gradient-to-b from-rose-50 to-rose-50/60 px-5 py-6 sm:px-[30px]">
                <h2 class="text-2xl font-extrabold text-red-900 sm:text-[26px]">Update Brand</h2>
                <p class="mt-2 text-sm leading-7 text-rose-700">
                    Pastikan data brand sudah sesuai sebelum disimpan agar daftar brand terlihat lebih profesional.
                </p>
            </div>

            <form method="POST" class="px-5 py-7 sm:px-[30px] sm:py-[30px]">
                <?php if ($error): ?>
                    <div class="mb-[18px] rounded-2xl border border-red-200 bg-rose-50 px-4 py-3 text-sm font-bold text-red-700">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <div class="mb-5">
                    <label for="brand_name" class="mb-2 block text-sm font-bold text-[#4a0d18]">Nama Brand</label>
                    <input
                        id="brand_name"
                        type="text"
                        name="brand_name"
                        class="w-full rounded-[18px] border border-rose-200 bg-rose-50 px-4 py-3.5 text-[15px] text-slate-800 outline-none transition focus:border-rose-700 focus:bg-white focus:ring-4 focus:ring-rose-200/60"
                        value="<?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Masukkan nama brand"
                        required
                    >
                </div>

                <div class="mb-5">
                    <label for="description" class="mb-2 block text-sm font-bold text-[#4a0d18]">Deskripsi Brand</label>
                    <textarea
                        id="description"
                        name="description"
                        class="min-h-[160px] w-full rounded-[18px] border border-rose-200 bg-rose-50 px-4 py-3.5 text-[15px] text-slate-800 outline-none transition focus:border-rose-700 focus:bg-white focus:ring-4 focus:ring-rose-200/60"
                        placeholder="Jelaskan brand Anda secara singkat dan menarik"
                        required
                    ><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-[18px] bg-gradient-to-r from-red-900 to-rose-700 px-[18px] py-[13px] text-sm font-bold text-white shadow-[0_12px_28px_rgba(159,18,57,0.2)] transition hover:-translate-y-0.5"
                    >
                        Simpan Perubahan
                    </button>
                    <a
                        href="brand_list.php"
                        class="inline-flex items-center justify-center rounded-[18px] border border-rose-200 bg-rose-50 px-[18px] py-[13px] text-sm font-bold text-red-900 transition hover:-translate-y-0.5"
                    >
                        Kembali ke Daftar Brand
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
