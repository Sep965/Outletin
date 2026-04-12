<?php
include '../includes/auth.php';
require '../includes/koneksi.php';

$userId = $_SESSION['user_id'];

$stmt = $koneksi->prepare("
    SELECT * FROM brands WHERE franchisor_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'partials/header.php'; ?>

<div class="max-w-6xl mx-auto mt-8">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-red-900">Daftar Brand</h1>
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