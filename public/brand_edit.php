<?php
require '../includes/koneksi.php';

$id = $_GET['id'];

$stmt = $koneksi->prepare("SELECT * FROM brands WHERE brand_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['brand_name'];
    $desc = $_POST['description'];

    $update = $koneksi->prepare("
        UPDATE brands SET brand_name=?, description=? WHERE brand_id=?
    ");
    $update->bind_param("ssi", $name, $desc, $id);
    $update->execute();

    header("Location: brand_list.php");
}
?>

<form method="POST">
    <input type="text" name="brand_name" value="<?= $data['brand_name'] ?>">
    <textarea name="description"><?= $data['description'] ?></textarea>
    <button type="submit">Update</button>
</form>