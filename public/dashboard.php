<?php
include '../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';

$userId = (int) $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Pengguna', ENT_QUOTES);
$userRole = htmlspecialchars($_SESSION['user_role'] ?? 'user', ENT_QUOTES);
$rawUserRole = $_SESSION['user_role'] ?? 'user';

if ($rawUserRole === 'franchisor') {
    $brandCheckQuery = $koneksi->prepare("SELECT brand_id FROM brands WHERE user_id = ? LIMIT 1");
    $brandCheckQuery->bind_param("i", $userId);
    $brandCheckQuery->execute();
    $brandExists = (bool) $brandCheckQuery->get_result()->fetch_assoc();

    if (!$brandExists) {
        header("Location: register_brand.php");
        exit;
    }
}

$outletCount = 0;
$productCount = 0;

if ($rawUserRole === 'franchisor') {
    $outletQuery = $koneksi->prepare(
        "SELECT COUNT(*) AS total
         FROM outlets o
         INNER JOIN brands b ON o.brand_id = b.brand_id
         WHERE b.user_id = ?"
    );
    $outletQuery->bind_param("i", $userId);
    $outletQuery->execute();
    $outletCount = (int) ($outletQuery->get_result()->fetch_assoc()['total'] ?? 0);

    $productQuery = $koneksi->prepare(
        "SELECT COUNT(*) AS total
         FROM products p
         INNER JOIN brands b ON p.brand_id = b.brand_id
         WHERE b.user_id = ?"
    );
    $productQuery->bind_param("i", $userId);
    $productQuery->execute();
    $productCount = (int) ($productQuery->get_result()->fetch_assoc()['total'] ?? 0);
} else {
    $outletQuery = $koneksi->prepare("SELECT COUNT(*) AS total FROM outlets WHERE user_id = ?");
    $outletQuery->bind_param("i", $userId);
    $outletQuery->execute();
    $outletCount = (int) ($outletQuery->get_result()->fetch_assoc()['total'] ?? 0);
}
?>

<?php include 'partials/header.php'; ?>

<section class="rounded-[2rem] bg-gradient-to-r from-red-950 via-red-900 to-red-800 p-8 text-white shadow-xl">
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-50">Dashboard</p>
    <h1 class="mt-3 text-3xl font-black md:text-4xl">
        Halo, selamat datang <?= $userName; ?>
    </h1>
    <p class="mt-3 max-w-2xl text-white/90">
        Anda login sebagai <span class="font-bold capitalize text-white"><?= $userRole; ?></span>. Kelola outlet dan produk Anda dari satu dashboard.
    </p>
</section>

<section class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
    <div class="card">
        <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Outlet</p>
        <h2 class="mt-3 text-3xl font-black text-red-900"><?= $outletCount; ?></h2>
        <p class="mt-2 text-sm text-slate-600">Jumlah outlet yang Anda kelola saat ini.</p>
    </div>

    <div class="card">
        <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Produk</p>
        <h2 class="mt-3 text-3xl font-black text-red-900"><?= $productCount; ?></h2>
        <p class="mt-2 text-sm text-slate-600">Total produk yang sudah terdaftar di sistem.</p>
    </div>

    <div class="card">
        <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Role</p>
        <h2 class="mt-3 text-2xl font-black capitalize text-red-900"><?= $userRole; ?></h2>
        <p class="mt-2 text-sm text-slate-600">Hak akses akun Anda mengikuti data role di database.</p>
    </div>
</section>

<section class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="card">
        <h3 class="text-xl font-bold text-red-900">Ringkasan</h3>
        <p class="mt-3 leading-7 text-slate-600">
            Dashboard ini menampilkan informasi akun Anda secara ringkas. Gunakan menu Outlet untuk mengelola cabang dan menu Produk untuk mengatur katalog barang.
        </p>
    </div>

    <div class="card">
        <h3 class="text-xl font-bold text-red-900">Aksi Cepat</h3>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="outlet.php" class="btn">Kelola Outlet</a>
            <a href="products.php" class="inline-flex items-center justify-center rounded-lg border border-red-300 px-4 py-2 font-medium text-red-800 transition hover:bg-red-100">
                Kelola Produk
            </a>
        </div>
    </div>
</section>

<div class="hidden max-w-6xl mx-auto p-4 mt-6">

    <div class="bg-white p-6 rounded-xl shadow border border-red-100">
        <h2 class="text-xl font-bold text-red-900">
            Selamat datang, <?= $_SESSION['user_name']; ?> 👋
        </h2>
        <p class="text-gray-500">
            Role: <b><?= $_SESSION['user_role']; ?></b>
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">

        <div class="bg-white p-5 rounded-xl shadow border border-red-100">
            <h3 class="font-semibold">Outlet</h3>
            <p class="text-sm text-gray-500">Kelola outlet</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border border-red-100">
            <h3 class="font-semibold">Produk</h3>
            <p class="text-sm text-gray-500">Kelola produk</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border border-red-100">
            <h3 class="font-semibold">Transaksi</h3>
            <p class="text-sm text-gray-500">Kelola transaksi</p>
        </div>

    </div>

</div>

<?php include 'partials/footer.php'; ?>
