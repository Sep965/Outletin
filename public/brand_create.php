<?php
include '../includes/auth.php';
require '../includes/koneksi.php';

if ($_SESSION['user_role'] !== 'franchisor') {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_name = trim($_POST['brand_name']);
    $description = trim($_POST['description']);
    $franchisor_id = $_SESSION['user_id'];

    if ($brand_name == '' || $description == '') {
        $error = "Semua field wajib diisi";
    } else {
        $stmt = $koneksi->prepare("
            INSERT INTO brands (franchisor_id, brand_name, description)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $franchisor_id, $brand_name, $description);

        if ($stmt->execute()) {
            header("Location: brand_list.php");
            exit;
        } else {
            $error = "Gagal tambah brand";
        }
    }
}
?>

<form method="POST">
    <input type="text" name="brand_name" placeholder="Nama Brand">
    <textarea name="description"></textarea>
    <button type="submit">Simpan</button>
</form>

<p><?= $error ?></p>