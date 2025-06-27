<?php
// get_gate_status.php
include 'koneksi.php';

header('Content-Type: application/json');

$query = "SELECT status_gerbang FROM total_data WHERE id = 1";
$result = mysqli_query($koneksi, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    echo json_encode(["status_gerbang" => $data['status_gerbang']]);
} else {
    echo json_encode(["status_gerbang" => "Tidak diketahui"]); // Default jika tidak ada data
}

mysqli_close($koneksi);
?>
