<?php
// Sertakan file koneksi database
include 'koneksi.php';

// Pastikan request yang datang adalah metode POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Periksa apakah parameter 'status' ada di dalam request
    if (isset($_POST['status'])) {
        
        $status = $_POST['status'];
        
        // Mulai transaksi database untuk memastikan integritas data
        mysqli_begin_transaction($koneksi);
        
        try {
            // 1. Masukkan data ke log pergerakan
            $query_log = "INSERT INTO log_pergerakan (status) VALUES (?)";
            $stmt_log = mysqli_prepare($koneksi, $query_log);
            mysqli_stmt_bind_param($stmt_log, "s", $status);
            mysqli_stmt_execute($stmt_log);

            // 2. Perbarui tabel total_data
            if ($status == 'masuk') {
                $query_total = "UPDATE total_data SET total_masuk = total_masuk + 1 WHERE id = 1";
            } elseif ($status == 'keluar') {
                $query_total = "UPDATE total_data SET total_keluar = total_keluar + 1 WHERE id = 1";
            } else {
                // Jika status tidak valid, batalkan transaksi
                throw new Exception("Status tidak valid.");
            }
            
            $stmt_total = mysqli_prepare($koneksi, $query_total);
            mysqli_stmt_execute($stmt_total);
            
            // Jika semua query berhasil, commit transaksi
            mysqli_commit($koneksi);
            echo "Data berhasil diterima dan disimpan.";
            
        } catch (Exception $e) {
            // Jika terjadi error, rollback semua perubahan
            mysqli_rollback($koneksi);
            // Kirim response error
            http_response_code(500);
            echo "Gagal menyimpan data: " . $e->getMessage();
        }
        
    } else {
        // Jika parameter 'status' tidak ada
        http_response_code(400); // Bad Request
        echo "Error: Parameter 'status' tidak ditemukan.";
    }
    
} else {
    // Jika metode request bukan POST
    http_response_code(405); // Method Not Allowed
    echo "Error: Metode request harus POST.";
}

// Tutup koneksi
mysqli_close($koneksi);
?>
