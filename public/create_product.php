<?php
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/auth.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['product_name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);

    if ($name === '' || $price <= 0 || $stock < 0) {
        $error = "Data tidak valid";
    } else {

        $stmt = $koneksi->prepare(
            "INSERT INTO products (product_name, price, stock) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sdi", $name, $price, $stock);

        if ($stmt->execute()) $success = true;
        else $error = "Gagal menyimpan";
    }
}

include 'partials/header.php';
?>

<div class="card max-w-md">

<h2 class="text-lg font-bold text-red-900 mb-4">Tambah Produk</h2>

<?php if($success): ?>
<div class="mb-3 rounded bg-emerald-100 p-2 text-emerald-700">Berhasil</div>
<?php endif; ?>

<?php if($error): ?>
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
