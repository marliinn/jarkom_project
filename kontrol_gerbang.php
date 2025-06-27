<?php
// kontrol_gerbang.php
include 'koneksi.php';

// Pastikan request yang datang adalah metode POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Periksa apakah parameter 'action' ada
    if (isset($_POST['action'])) {
        $action = $_POST['action']; // Bisa 'buka' atau 'tutup'
        $esp32_ip = "YOUR_ESP32_IP_ADDRESS"; // <-- GANTI DENGAN IP ADDRESS ESP32 ANDA
        $command_url = "";
        $new_gate_status = "";

        if ($action == 'buka') {
            // URL yang akan dipanggil di ESP32 untuk membuka gerbang
            // Pastikan ESP32 Anda memiliki endpoint seperti ini
            $command_url = "http://" . $esp32_ip . "/control?action=open";
            $new_gate_status = "terbuka";
        } elseif ($action == 'tutup') {
            // URL yang akan dipanggil di ESP32 untuk menutup gerbang
            // Pastikan ESP32 Anda memiliki endpoint seperti ini
            $command_url = "http://" . $esp32_ip . "/control?action=close";
            $new_gate_status = "tertutup";
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(["success" => false, "message" => "Aksi tidak valid."]);
            mysqli_close($koneksi);
            exit();
        }

        // Coba kirim perintah ke ESP32
        $response_from_esp32 = @file_get_contents($command_url); // Menggunakan @ untuk menekan peringatan jika gagal

        // Perbarui status gerbang di database terlepas dari respon ESP32
        // Anda bisa menambahkan logika penanganan error yang lebih kompleks di sini
        // jika Anda ingin memverifikasi respons dari ESP32 sebelum memperbarui DB.
        $query_update_status = "UPDATE total_data SET status_gerbang = ? WHERE id = 1";
        $stmt_update_status = mysqli_prepare($koneksi, $query_update_status);
        mysqli_stmt_bind_param($stmt_update_status, "s", $new_gate_status);
        $update_success = mysqli_stmt_execute($stmt_update_status);

        if ($update_success) {
            echo json_encode(["success" => true, "message" => "Perintah '$action' berhasil dikirim. Status gerbang diperbarui."]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(["success" => false, "message" => "Gagal memperbarui status gerbang di database."]);
        }

    } else {
        http_response_code(400); // Bad Request
        echo json_encode(["success" => false, "message" => "Parameter 'action' tidak ditemukan."]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Metode request harus POST."]);
}

mysqli_close($koneksi);
?>
