<?php
include '../includes/auth.php';
require '../includes/koneksi.php';

$brand_id = $_GET['brand_id']; // ambil dari URL

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk_name = $_POST['produk_name'];
    $price = $_POST['price'];

    $stmt = $koneksi->prepare("
        INSERT INTO produk (brand_id, produk_name, price)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("isd", $brand_id, $produk_name, $price);
    $stmt->execute();

    header("Location: read_product.php?brand_id=$brand_id");
}
?>

<form method="POST">
    <input type="text" name="produk_name" placeholder="Nama Produk">
    <input type="number" name="price" placeholder="Harga">
    <button type="submit">Tambah Produk</button>
</form>

<div class="card max-w-md">

    <h2 class="text-lg font-bold text-red-900 mb-4">Tambah Produk</h2>

    <?php if ($success): ?>
        <div class="mb-3 rounded bg-emerald-100 p-2 text-emerald-700">Berhasil</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="mb-3 rounded bg-red-100 p-2 text-red-700"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-3">

        <input name="product_name" placeholder="Nama produk" class="input !mt-0">

        <input name="price" type="number" placeholder="Harga" class="input !mt-0">

        <input name="stock" type="number" placeholder="Stok" class="input !mt-0">

        <button class="btn">
            Simpan
        </button>

    </form>
</div>

<?php include 'partials/footer.php'; ?>