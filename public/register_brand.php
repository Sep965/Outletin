<?php
include '../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';

$userId = (int) ($_SESSION['user_id'] ?? 0);
$userRole = $_SESSION['user_role'] ?? 'user';

if ($userRole !== 'franchisor') {
    header("Location: dashboard.php");
    exit;
}

$brandStmt = $koneksi->prepare("SELECT brand_id, brand_name, description FROM brands WHERE user_id = ? LIMIT 1");
$brandStmt->bind_param("i", $userId);
$brandStmt->execute();
$existingBrand = $brandStmt->get_result()->fetch_assoc();

if ($existingBrand) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$brandName = '';
$description = '';
$productName = '';
$price = '';
$stock = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brandName = trim($_POST['brand_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $productName = trim($_POST['product_name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');

    if ($brandName === '' || $description === '' || $productName === '' || $price === '' || $stock === '') {
        $error = 'Nama brand, deskripsi, dan data produk wajib diisi.';
    } elseif (!is_numeric($price) || (float) $price <= 0 || filter_var($stock, FILTER_VALIDATE_INT) === false || (int) $stock < 0) {
        $error = 'Harga dan stok produk tidak valid.';
    } else {
        $priceValue = (float) $price;
        $stockValue = (int) $stock;

        $koneksi->begin_transaction();

        try {
            $insertStmt = $koneksi->prepare(
                "INSERT INTO brands (user_id, brand_name, description) VALUES (?, ?, ?)"
            );
            $insertStmt->bind_param("iss", $userId, $brandName, $description);

            if (!$insertStmt->execute()) {
                throw new Exception('Brand gagal disimpan.');
            }

            $productStmt = $koneksi->prepare(
                "INSERT INTO products (product_name, price, stock, user_id) VALUES (?, ?, ?, ?)"
            );
            $productStmt->bind_param("sdii", $productName, $priceValue, $stockValue, $userId);

            if (!$productStmt->execute()) {
                throw new Exception('Produk gagal disimpan.');
            }

            $koneksi->commit();
            header("Location: products.php");
            exit;
        } catch (Throwable $e) {
            $koneksi->rollback();
            $error = 'Brand atau produk gagal disimpan. Silakan coba lagi.';
        }
    }
}
?>

<?php include 'partials/header.php'; ?>

<div class="mx-auto max-w-2xl card">
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-700">Daftarkan Brand</p>
    <h1 class="mt-3 text-2xl font-black text-red-900">Lengkapi brand dan produk pertama Anda</h1>
    <p class="mt-3 text-slate-600">
        Isi data brand dan satu produk pertama untuk memulai katalog Anda.
    </p>

    <?php if ($error): ?>
        <div class="mt-4 rounded bg-red-100 p-3 text-red-700"><?= htmlspecialchars($error, ENT_QUOTES); ?></div>
    <?php endif; ?>

    <form method="post" class="mt-6 space-y-4">

        <div>
            <label for="brand_name" class="mb-1 block text-sm font-medium text-slate-700">Brand Name</label>
            <input
                id="brand_name"
                type="text"
                name="brand_name"
                value="<?= htmlspecialchars($brandName, ENT_QUOTES); ?>"
                placeholder="Contoh: Outletin Coffee"
                class="input !mt-0"
                required
            >
        </div>

        <div>
            <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Description</label>
            <textarea
                id="description"
                name="description"
                rows="5"
                placeholder="Jelaskan singkat tentang brand Anda"
                class="input !mt-0 min-h-[140px]"
                required
            ><?= htmlspecialchars($description, ENT_QUOTES); ?></textarea>
        </div>

        <div class="rounded-2xl border border-red-100 bg-red-50/50 p-4">
            <h2 class="text-lg font-bold text-red-900">Tambah Produk</h2>
            <p class="mt-1 text-sm text-slate-600">Masukkan satu produk awal yang akan tampil di katalog Anda.</p>

            <div class="mt-4 space-y-4">
                <div>
                    <label for="product_name" class="mb-1 block text-sm font-medium text-slate-700">Nama Produk</label>
                    <input
                        id="product_name"
                        type="text"
                        name="product_name"
                        value="<?= htmlspecialchars($productName, ENT_QUOTES); ?>"
                        placeholder="Contoh: Kopi Susu Gula Aren"
                        class="input !mt-0"
                        required
                    >
                </div>

                <div>
                    <label for="price" class="mb-1 block text-sm font-medium text-slate-700">Harga</label>
                    <input
                        id="price"
                        type="number"
                        name="price"
                        value="<?= htmlspecialchars($price, ENT_QUOTES); ?>"
                        placeholder="Contoh: 18000"
                        min="1"
                        step="0.01"
                        class="input !mt-0"
                        required
                    >
                </div>

                <div>
                    <label for="stock" class="mb-1 block text-sm font-medium text-slate-700">Stok</label>
                    <input
                        id="stock"
                        type="number"
                        name="stock"
                        value="<?= htmlspecialchars($stock, ENT_QUOTES); ?>"
                        placeholder="Contoh: 50"
                        min="0"
                        step="1"
                        class="input !mt-0"
                        required
                    >
                </div>
            </div>
        </div>

        <button type="submit" class="btn w-full">Simpan Brand dan Produk</button>
    </form>
</div>

<?php include 'partials/footer.php'; ?>
