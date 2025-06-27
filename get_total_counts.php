<?php
// get_total_counts.php
include 'koneksi.php';

header('Content-Type: application/json');

$query_total = "SELECT total_masuk, total_keluar FROM total_data WHERE id = 1";
$result_total = mysqli_query($koneksi, $query_total);
$data_total = mysqli_fetch_assoc($result_total);

echo json_encode([
    "total_masuk" => $data_total['total_masuk'] ?? 0,
    "total_keluar" => $data_total['total_keluar'] ?? 0
]);

mysqli_close($koneksi);
?>
