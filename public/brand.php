<?php
include '../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';

$userId = (int) ($_SESSION['user_id'] ?? 0);
$userRole = $_SESSION['user_role'] ?? 'user';

if ($userRole === 'franchisor') {
    header("Location: brand_list.php");
    exit;
}

if ($userRole !== 'franchisee') {
    header("Location: dashboard.php");
    exit;
}

$koneksi->query("
    CREATE TABLE IF NOT EXISTS brand_applications (
        application_id INT AUTO_INCREMENT PRIMARY KEY,
        brand_id INT NOT NULL,
        franchisee_id INT NOT NULL,
        request_type VARCHAR(30) NOT NULL,
        notes TEXT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brandId = (int) ($_POST['brand_id'] ?? 0);
    $requestType = trim($_POST['request_type'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $allowedRequestTypes = ['join_brand', 'buka_outlet'];

    if ($brandId <= 0 || !in_array($requestType, $allowedRequestTypes, true)) {
        $error = 'Pengajuan tidak valid.';
    } else {
        $brandStmt = $koneksi->prepare("
            SELECT b.brand_id, b.brand_name
            FROM brands b
            LEFT JOIN verifications v ON b.brand_id = v.brand_id
            WHERE b.brand_id = ?
            AND COALESCE(v.status, 'pending') = 'verified'
            LIMIT 1
        ");
        $brandStmt->bind_param("i", $brandId);
        $brandStmt->execute();
        $brand = $brandStmt->get_result()->fetch_assoc();

        if (!$brand) {
            $error = 'Brand yang dipilih tidak tersedia untuk diajukan.';
        } else {
            $checkStmt = $koneksi->prepare("
                SELECT application_id
                FROM brand_applications
                WHERE brand_id = ?
                AND franchisee_id = ?
                AND request_type = ?
                AND status = 'pending'
                LIMIT 1
            ");
            $checkStmt->bind_param("iis", $brandId, $userId, $requestType);
            $checkStmt->execute();
            $existingApplication = $checkStmt->get_result()->fetch_assoc();

            if ($existingApplication) {
                $error = 'Anda sudah punya pengajuan pending untuk brand dan tipe pengajuan ini.';
            } else {
                $insertStmt = $koneksi->prepare("
                    INSERT INTO brand_applications (brand_id, franchisee_id, request_type, notes)
                    VALUES (?, ?, ?, ?)
                ");
                $insertStmt->bind_param("iiss", $brandId, $userId, $requestType, $notes);

                if ($insertStmt->execute()) {
                    $success = 'Pengajuan berhasil dikirim. Silakan tunggu proses tindak lanjut.';
                } else {
                    $error = 'Pengajuan gagal disimpan. Silakan coba lagi.';
                }
            }
        }
    }
}

$brandsStmt = $koneksi->prepare("
    SELECT
        b.brand_id,
        b.brand_name,
        b.description,
        u.name AS franchisor_name
    FROM brands b
    JOIN users u ON b.franchisor_id = u.user_id
    LEFT JOIN verifications v ON b.brand_id = v.brand_id
    WHERE COALESCE(v.status, 'pending') = 'verified'
    ORDER BY b.brand_name ASC
");
$brandsStmt->execute();
$brands = $brandsStmt->get_result();

$applicationStmt = $koneksi->prepare("
    SELECT brand_id, request_type, status
    FROM brand_applications
    WHERE franchisee_id = ?
");
$applicationStmt->bind_param("i", $userId);
$applicationStmt->execute();
$applicationResult = $applicationStmt->get_result();

$applicationMap = [];
while ($application = $applicationResult->fetch_assoc()) {
    $applicationMap[$application['brand_id']][$application['request_type']] = $application['status'];
}

function applicationBadgeClass(string $status): string
{
    return match ($status) {
        'approved' => 'bg-emerald-100 text-emerald-700',
        'rejected' => 'bg-red-100 text-red-700',
        default => 'bg-amber-100 text-amber-700',
    };
}

function requestTypeLabel(string $type): string
{
    return $type === 'buka_outlet' ? 'Buka Outlet' : 'Join Brand';
}
?>

<?php include 'partials/header.php'; ?>

<section class="rounded-[2rem] bg-gradient-to-r from-red-950 via-red-900 to-red-800 p-8 text-white shadow-xl">
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-50">Katalog Brand</p>
    <h1 class="mt-3 text-3xl font-black md:text-4xl">Pilih brand yang ingin Anda ajukan</h1>
    <p class="mt-3 max-w-3xl text-white/90">
        Anda bisa melihat brand yang sudah terverifikasi, lalu mengajukan kerja sama untuk join brand atau pembukaan outlet.
    </p>
</section>

<?php if ($error): ?>
    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<section class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    <?php if ($brands->num_rows > 0): ?>
        <?php while ($brand = $brands->fetch_assoc()): ?>
            <?php
            $brandApplications = $applicationMap[$brand['brand_id']] ?? [];
            ?>
            <article class="card flex h-full flex-col">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-red-700">Brand Terverifikasi</p>
                        <h2 class="mt-2 text-2xl font-black text-red-900"><?= htmlspecialchars($brand['brand_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Verified</span>
                </div>

                <p class="mt-3 text-sm text-slate-500">Franchisor: <?= htmlspecialchars($brand['franchisor_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mt-3 flex-1 text-sm leading-7 text-slate-600"><?= htmlspecialchars($brand['description'], ENT_QUOTES, 'UTF-8'); ?></p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <?php foreach ($brandApplications as $type => $status): ?>
                        <span class="rounded-full px-3 py-1 text-xs font-bold <?= applicationBadgeClass($status); ?>">
                            <?= requestTypeLabel($type); ?>: <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <form method="post" class="mt-5 space-y-3 rounded-2xl border border-red-100 bg-red-50/60 p-4">
                    <input type="hidden" name="brand_id" value="<?= (int) $brand['brand_id']; ?>">

                    <div>
                        <label for="request_type_<?= (int) $brand['brand_id']; ?>" class="mb-2 block text-sm font-bold text-red-900">Tipe Pengajuan</label>
                        <select
                            id="request_type_<?= (int) $brand['brand_id']; ?>"
                            name="request_type"
                            class="input !mt-0"
                            required
                        >
                            <option value="">Pilih pengajuan</option>
                            <option value="join_brand">Join Brand</option>
                            <option value="buka_outlet">Buka Outlet</option>
                        </select>
                    </div>

                    <div>
                        <label for="notes_<?= (int) $brand['brand_id']; ?>" class="mb-2 block text-sm font-bold text-red-900">Catatan</label>
                        <textarea
                            id="notes_<?= (int) $brand['brand_id']; ?>"
                            name="notes"
                            rows="4"
                            class="input !mt-0 min-h-[110px]"
                            placeholder="Tulis singkat alasan atau rencana kerja sama Anda"
                        ></textarea>
                    </div>

                    <button type="submit" class="btn w-full">Kirim Pengajuan</button>
                </form>
            </article>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card md:col-span-2 xl:col-span-3">
            <h2 class="text-xl font-bold text-red-900">Belum ada brand yang bisa diajukan</h2>
            <p class="mt-3 text-slate-600">Saat ini belum ada brand dengan status terverifikasi. Coba cek lagi nanti.</p>
        </div>
    <?php endif; ?>
</section>

<?php include 'partials/footer.php'; ?>
