<?php
include '../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';

$userId = (int) $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Pengguna', ENT_QUOTES);
$userRole = htmlspecialchars($_SESSION['user_role'] ?? 'user', ENT_QUOTES);
$rawUserRole = $_SESSION['user_role'] ?? 'user';

/* =========================
   DEFAULT COUNT
========================= */
$outletCount = 0;
$productCount = 0;
$brandCount = 0;
$verifiedBrandCount = 0;

/* =========================
   KHUSUS FRANCHISOR
========================= */
if ($rawUserRole === 'franchisor') {

    // ✅ HITUNG BRAND (TANPA LIMIT)
    $stmt = $koneksi->prepare("
        SELECT COUNT(*) AS total_brand
        FROM brands b
        LEFT JOIN verifications v ON b.brand_id = v.brand_id
        WHERE b.franchisor_id = ?
          AND COALESCE(NULLIF(v.status, ''), 'pending') = 'verified'
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $brandData = $stmt->get_result()->fetch_assoc();
    $brandCount = $brandData['total_brand'] ?? 0;

    // ❗ Kalau belum punya brand → redirect
    if ($brandCount == 0) {
        header("Location: register_brand.php?msg=belum_punya_brand");
        exit;
    }

    // ✅ HITUNG PRODUK (JOIN BRAND)
    $stmt = $koneksi->prepare("
        SELECT COUNT(p.produk_id) as total_produk
        FROM produk p
        JOIN brands b ON p.brand_id = b.brand_id
        WHERE b.franchisor_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $productData = $stmt->get_result()->fetch_assoc();
    $productCount = $productData['total_produk'] ?? 0;

    // ✅ HITUNG OUTLET (JOIN BRAND)
    $stmt = $koneksi->prepare("
        SELECT COUNT(o.outlet_id) as total_outlet
        FROM outlets o
        JOIN brands b ON o.brand_id = b.brand_id
        WHERE b.franchisor_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $outletData = $stmt->get_result()->fetch_assoc();
    $outletCount = $outletData['total_outlet'] ?? 0;
}

if ($rawUserRole === 'franchisee') {
    $stmt = $koneksi->prepare("
        SELECT COUNT(*) as total_brand
        FROM brands b
        LEFT JOIN verifications v ON b.brand_id = v.brand_id
        WHERE COALESCE(v.status, 'pending') = 'verified'
    ");
    $stmt->execute();
    $brandData = $stmt->get_result()->fetch_assoc();
    $verifiedBrandCount = $brandData['total_brand'] ?? 0;
}
?>

<?php include 'partials/header.php'; ?>

<section class="rounded-[2rem] bg-gradient-to-r from-red-950 via-red-900 to-red-800 p-8 text-white shadow-xl">
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-50">Dashboard</p>
    <h1 class="mt-3 text-3xl font-black md:text-4xl">
        Halo, selamat datang <?= $userName; ?>
    </h1>
    <p class="mt-3 max-w-2xl text-white/90">
        Anda login sebagai <span class="font-bold capitalize text-white"><?= $userRole; ?></span>.
    </p>
</section>

<section class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

    <div class="card">
        <p class="text-sm text-slate-500">Brand</p>
        <h2 class="text-3xl font-black text-red-900"><?= $rawUserRole === 'franchisee' ? $verifiedBrandCount : $brandCount; ?></h2>
        <p class="text-sm text-slate-600">
            <?= $rawUserRole === 'franchisee' ? 'Brand terverifikasi yang bisa Anda ajukan' : 'Total brand yang Anda miliki'; ?>
        </p>
    </div>

    <div class="card">
        <p class="text-sm text-slate-500">Outlet</p>
        <h2 class="text-3xl font-black text-red-900"><?= $outletCount; ?></h2>
        <p class="text-sm text-slate-600">Jumlah outlet aktif</p>
    </div>

    <div class="card">
        <p class="text-sm text-slate-500">Produk</p>
        <h2 class="text-3xl font-black text-red-900"><?= $productCount; ?></h2>
        <p class="text-sm text-slate-600">Total produk terdaftar</p>
    </div>

    <div class="card">
        <p class="text-sm text-slate-500">Role</p>
        <h2 class="text-xl font-black text-red-900 capitalize"><?= $userRole; ?></h2>
    </div>

</section>

<section class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-2">

    <div class="card">
        <h3 class="text-xl font-bold text-red-900">Ringkasan</h3>
        <p class="mt-3 text-slate-600">
            <?= $rawUserRole === 'franchisee'
                ? 'Lihat brand yang tersedia lalu ajukan join brand atau pembukaan outlet dari satu tempat.'
                : 'Kelola brand, produk, dan outlet Anda dengan mudah dari dashboard ini.'; ?>
        </p>
    </div>

    <div class="card">
        <h3 class="text-xl font-bold text-red-900">Aksi Cepat</h3>
        <div class="mt-4 flex flex-wrap gap-3">
            <?php if ($rawUserRole === 'franchisee'): ?>
                <a href="brand.php" class="btn">Lihat Brand</a>
            <?php else: ?>
                <a href="brand_list.php" class="btn">Kelola Brand</a>
                <a href="outlet.php" class="btn">Kelola Outlet</a>
                <a href="products.php" class="border border-red-300 px-4 py-2 rounded-lg text-red-800 hover:bg-red-100">
                    Kelola Produk
                </a>
            <?php endif; ?>
        </div>
    </div>

</section>

<?php include 'partials/footer.php'; ?>
