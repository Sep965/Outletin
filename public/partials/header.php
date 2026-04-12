<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/koneksi.php';

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$isAuthPage = in_array($currentPage, ['login.php', 'register.php'], true);
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? 'user';
$showRegisterBrandMenu = false;

if ($isLoggedIn && $userRole === 'franchisor') {
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $brandQuery = $koneksi->prepare("SELECT brand_id FROM brands WHERE franchisor_id = ? LIMIT 1");
    $brandQuery->bind_param("i", $userId);
    $brandQuery->execute();
    $showRegisterBrandMenu = !(bool) $brandQuery->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outletin</title>

    <!-- Tailwind FIX -->
    <link rel="stylesheet" href="/outletin/scr/output-build.css">
</head>

<body class="bg-red-50">

<header class="bg-red-800 text-white">
    <div class="max-w-6xl mx-auto flex justify-between items-center p-4">
        <a href="/outletin/index.html" class="font-bold text-lg">Outletin</a>

        <?php if ($isLoggedIn && !$isAuthPage): ?>
        <nav class="space-x-4">
            <a href="dashboard.php">Dashboard</a>
            <?php if ($userRole === 'franchisor'): ?>
                <a href="brand_list.php">Brand</a>
                <?php if ($showRegisterBrandMenu): ?>
                <a href="register_brand.php">Daftarkan Brand</a>
                <?php endif; ?>
                <a href="outlet.php">Outlet</a>
                <a href="products.php">Produk</a>
            <?php elseif ($userRole === 'franchisee'): ?>
                <a href="brand.php">Brand</a>
            <?php endif; ?>
            <a href="logout.php" class="inline-flex items-center rounded-lg bg-white px-3 py-1 font-medium text-red-800">Logout</a>
        </nav>
        <?php else: ?>
        <nav class="space-x-4 text-sm">
            <a href="/outletin/index.html">Home</a>
            <a href="/outletin/public/register.php">Daftar</a>
        </nav>
        <?php endif; ?>
    </div>
</header>

<main class="max-w-6xl mx-auto p-4 mt-6">
