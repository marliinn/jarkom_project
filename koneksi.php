<?php
// --- Konfigurasi Database ---
$host = "localhost";        // Biasanya "localhost"
$username = "root";         // Ganti dengan username database Anda
$password = "";             // Ganti dengan password database Anda
$database = "db_gate_counter"; // Ganti dengan nama database yang Anda buat

// Membuat koneksi ke database
$koneksi = mysqli_connect($host, $username, $password, $database);

// Memeriksa koneksi
if (!$koneksi) {
    // Jika koneksi gagal, hentikan skrip dan tampilkan pesan error
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Set timezone agar sesuai dengan waktu di Indonesia (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');
?>
