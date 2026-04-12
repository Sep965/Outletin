<?php
include '../includes/auth_admin.php';
require __DIR__ . '/../includes/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    die("Akses ditolak");
}

$brandId = (int) ($_GET['id'] ?? 0);
$aksi = $_GET['aksi'] ?? '';

if (!$brandId || !in_array($aksi, ['approve', 'reject'])) {
    die("Data tidak valid");
}

$status = ($aksi === 'approve') ? 'verified' : 'rejected';

$stmt = $koneksi->prepare("
    UPDATE verifications 
    SET status = ?, verified_at = NOW()
    WHERE brand_id = ?
");
$stmt->bind_param("si", $status, $brandId);
$stmt->execute();

header("Location: admin_verification_brand.php");
exit;