<?php
require '../includes/koneksi.php';

$id = $_GET['id'];

$stmt = $koneksi->prepare("DELETE FROM brands WHERE brand_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: brand_list.php");