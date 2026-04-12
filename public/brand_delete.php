<?php
include '../includes/auth.php';
require '../includes/koneksi.php';

$userRole = $_SESSION['user_role'] ?? 'user';
if ($userRole !== 'franchisor') {
    header("Location: brand.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($id <= 0) {
    header("Location: brand_list.php?msg=hapus_invalid");
    exit;
}

$checkStmt = $koneksi->prepare("SELECT brand_id FROM brands WHERE brand_id = ? AND franchisor_id = ?");
$checkStmt->bind_param("ii", $id, $userId);
$checkStmt->execute();
$brand = $checkStmt->get_result()->fetch_assoc();

if (!$brand) {
    header("Location: brand_list.php?msg=hapus_tidak_ditemukan");
    exit;
}

try {
    $stmt = $koneksi->prepare("DELETE FROM brands WHERE brand_id = ? AND franchisor_id = ?");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: brand_list.php?msg=hapus_berhasil");
        exit;
    }

    header("Location: brand_list.php?msg=hapus_tidak_ditemukan");
    exit;
} catch (mysqli_sql_exception $e) {
    header("Location: brand_list.php?msg=hapus_gagal&err=" . urlencode($e->getMessage()));
    exit;
}
