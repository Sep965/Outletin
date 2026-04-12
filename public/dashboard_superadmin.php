<?php
include '../includes/auth_admin.php';
$conn = new mysqli("localhost", "root", "", "outletin");

function statusBadgeClass(string $status): string
{
    return match ($status) {
        'approved' => 'bg-emerald-600 text-white',
        'verified' => 'bg-blue-600 text-white',
        'rejected' => 'bg-red-600 text-white',
        default => 'bg-amber-500 text-white',
    };
}

$query = "
SELECT 
    b.brand_id,
    b.brand_name,
    b.description,
    u.email,
    v.status
FROM brands b
JOIN users u ON b.franchisor_id = u.user_id
LEFT JOIN verifications v ON b.brand_id = v.brand_id
";

$data = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Super Admin</title>
    <link rel="stylesheet" href="/outletin/scr/output-build.css">
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
<div class="min-h-screen lg:flex">
    <aside class="bg-red-900 px-6 py-6 text-white lg:min-h-screen lg:w-60 lg:flex-shrink-0">
        <h2 class="text-center text-xl font-extrabold tracking-wide">SUPER ADMIN</h2>
        <nav class="mt-6 space-y-2">
            <a href="dashboard_superadmin.php" class="block rounded-lg px-3 py-3 font-medium transition hover:bg-red-800">Dashboard</a>
            <a href="Admin_Verification_Brand.php" class="block rounded-lg px-3 py-3 font-medium transition hover:bg-red-800">Verifikasi Brand</a>
        </nav>
    </aside>

    <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <div class="rounded-2xl bg-red-900 px-5 py-4 text-white shadow-sm">
            <h1 class="text-2xl font-extrabold">Manajemen Brand</h1>
            <p class="mt-1 text-sm text-red-100">Kelola status verifikasi brand dari satu dashboard admin.</p>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="bg-red-900 text-white">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Brand</th>
                            <th class="px-4 py-3 font-semibold">Email</th>
                            <th class="px-4 py-3 font-semibold">Deskripsi</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php while ($row = $data->fetch_assoc()): ?>
                            <?php $status = $row['status'] ?? 'pending'; ?>
                            <tr class="align-top transition hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($row['brand_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide <?= statusBadgeClass($status); ?>">
                                        <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <form method="POST" action="proses_verifikasi.php" class="inline">
                                            <input type="hidden" name="brand_id" value="<?= (int) $row['brand_id']; ?>">
                                            <input type="hidden" name="action" value="approved">
                                            <button class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                                Approve
                                            </button>
                                        </form>

                                        <form method="POST" action="proses_verifikasi.php" class="inline">
                                            <input type="hidden" name="brand_id" value="<?= (int) $row['brand_id']; ?>">
                                            <input type="hidden" name="action" value="verified">
                                            <button class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                                Verify
                                            </button>
                                        </form>

                                        <form method="POST" action="proses_verifikasi.php" class="inline">
                                            <input type="hidden" name="brand_id" value="<?= (int) $row['brand_id']; ?>">
                                            <input type="hidden" name="action" value="rejected">
                                            <button class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
