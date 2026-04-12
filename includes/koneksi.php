<?php
$koneksi = mysqli_connect("localhost", "root", "", "outletin");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
