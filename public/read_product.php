<?php
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/auth.php';

$result = $koneksi->query("SELECT * FROM products ORDER BY id DESC");

include 'partials/header.php';
?>

<div class="flex justify-between mb-4">
<h2 class="text-xl font-bold text-red-900">Produk</h2>

<a href="create_product.php" class="btn">
+ Tambah
</a>
</div>

<div class="grid gap-4">

<?php while($row = $result->fetch_assoc()): ?>

<div class="card p-4">
<h3 class="font-semibold"><?= htmlspecialchars($row['product_name']) ?></h3>
<p class="text-gray-600">Rp <?= number_format($row['price']) ?></p>
<p class="text-sm">Stok: <?= $row['stock'] ?></p>
</div>

<?php endwhile; ?>

</div>

<?php include 'partials/footer.php'; ?>
