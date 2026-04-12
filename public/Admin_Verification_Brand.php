<?php
include '../includes/auth_admin.php';
require __DIR__ . '/../includes/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_superadmin.php");
    exit;
}
$stmt = $koneksi->prepare("
    SELECT 
        b.brand_id,
        b.brand_name,
        b.description,
        u.name AS owner,
        v.status
    FROM brands b
    JOIN users u ON b.franchisor_id = u.user_id
    LEFT JOIN verifications v ON b.brand_id = v.brand_id
    ORDER BY b.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'partials/header.php'; ?>

<div class="max-w-6xl mx-auto mt-8">

    <h1 class="text-2xl font-bold text-red-900 mb-6">Verifikasi Brand</h1>

    <div class="bg-white shadow rounded-xl overflow-hidden border">

        <table class="w-full text-left">
            <thead class="bg-red-900 text-white">
                <tr>
                    <th class="p-4">Brand</th>
                    <th class="p-4">Owner</th>
                    <th class="p-4">Deskripsi</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y">

            <?php while($row = $result->fetch_assoc()): ?>

                <tr class="hover:bg-red-50">

                    <td class="p-4 font-semibold">
                        <?= htmlspecialchars($row['brand_name']) ?>
                    </td>

                    <td class="p-4">
                        <?= htmlspecialchars($row['owner']) ?>
                    </td>

                    <td class="p-4 text-gray-600">
                        <?= htmlspecialchars($row['description']) ?>
                    </td>

                    <td class="p-4 text-center">
                        <?php
                            $status = $row['status'] ?? 'pending';

                            if ($status == 'pending') {
                                echo '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">Pending</span>';
                            } elseif ($status == 'approved') {
                                echo '<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">Approved</span>';
                            } elseif ($status == 'verified') {
                                echo '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Verified</span>';
                            } else {
                                echo '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">Rejected</span>';
                            }
                        ?>
                    </td>

                    <td class="p-4 text-center space-x-2">

                        <?php if ($status !== 'verified'): ?>

                            <a href="proses_verifikasi.php?id=<?= $row['brand_id'] ?>&aksi=approve"
                               class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
                               ✔ Approve
                            </a>

                            <a href="proses_verifikasi.php?id=<?= $row['brand_id'] ?>&aksi=reject"
                               class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm"
                               onclick="return confirm('Yakin reject brand ini?')">
                               ✖ Reject
                            </a>

                        <?php else: ?>

                            <span class="text-gray-400 text-sm">Sudah Verified</span>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>
        </table>

    </div>

</div>

<?php include 'partials/footer.php'; ?>