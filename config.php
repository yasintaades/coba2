<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kalkulator_db";

$conn = new mysqli($host, $user, $pass, $db);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
