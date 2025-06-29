<?php
// kontrol_gerbang.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $esp32_ip = "192.168.131.206"; // <-- PASTIKAN INI IP ESP32 YANG BENAR
        $command_url = "";
        $new_gate_status = "";

        if ($action == 'buka') {
            $command_url = "http://" . $esp32_ip . "/control?action=open";
            $new_gate_status = "terbuka";
        } elseif ($action == 'tutup') {
            $command_url = "http://" . $esp32_ip . "/control?action=close";
            $new_gate_status = "tertutup";
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Aksi tidak valid."]);
            mysqli_close($koneksi);
            exit();
        }

        // --- TAMBAHKAN LOGGING INI ---
        error_log("Mencoba mengirim perintah ke ESP32: " . $command_url);
        $context = stream_context_create([
            'http' => [
                'timeout' => 5, // Timeout 5 detik
            ]
        ]);
        $response_from_esp32 = @file_get_contents($command_url, false, $context); 
        // --- AKHIR LOGGING TAMBAHAN ---

        if ($response_from_esp32 === FALSE) {
            error_log("Gagal mendapatkan respons dari ESP32. Mungkin timeout atau koneksi gagal.");
            // Tetap coba update DB, tapi beri pesan peringatan
        } else {
            error_log("Respons dari ESP32: " . $response_from_esp32);
        }

        // Perbarui status gerbang di database terlepas dari respon ESP32
        $query_update_status = "UPDATE total_data SET status_gerbang = ? WHERE id = 1";
        $stmt_update_status = mysqli_prepare($koneksi, $query_update_status);
        mysqli_stmt_bind_param($stmt_update_status, "s", $new_gate_status);
        $update_success = mysqli_stmt_execute($stmt_update_status);

        if ($update_success) {
            echo json_encode(["success" => true, "message" => "Perintah '$action' berhasil dikirim. Status gerbang diperbarui."]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Gagal memperbarui status gerbang di database."]);
        }

    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Parameter 'action' tidak ditemukan."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Metode request harus POST."]);
}

mysqli_close($koneksi);
?>