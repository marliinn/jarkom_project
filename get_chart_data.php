<?php
// get_chart_data.php
include 'koneksi.php';

header('Content-Type: application/json');

// Ambil data log pergerakan untuk 7 hari terakhir
$query_chart = "
    SELECT
        DATE(waktu) as tanggal,
        SUM(CASE WHEN status = 'masuk' THEN 1 ELSE 0 END) as total_masuk_harian,
        SUM(CASE WHEN status = 'keluar' THEN 1 ELSE 0 END) as total_keluar_harian
    FROM
        log_pergerakan
    WHERE
        waktu >= CURDATE() - INTERVAL 6 DAY
    GROUP BY
        tanggal
    ORDER BY
        tanggal ASC
";

$result_chart = mysqli_query($koneksi, $query_chart);

$dates = [];
$masuk_data = [];
$keluar_data = [];

// Inisialisasi data untuk 7 hari terakhir, termasuk hari-hari tanpa aktivitas
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('d M', strtotime($date)); // Format tanggal untuk label grafik
    $masuk_data[$date] = 0;
    $keluar_data[$date] = 0;
}

if ($result_chart && mysqli_num_rows($result_chart) > 0) {
    while ($row = mysqli_fetch_assoc($result_chart)) {
        $tanggal_data = $row['tanggal'];
        if (isset($masuk_data[$tanggal_data])) { // Pastikan tanggal ada dalam rentang 7 hari
            $masuk_data[$tanggal_data] = (int)$row['total_masuk_harian'];
            $keluar_data[$tanggal_data] = (int)$row['total_keluar_harian'];
        }
    }
}

echo json_encode([
    "labels" => array_values($dates), // Labels untuk sumbu X (tanggal)
    "masuk" => array_values($masuk_data), // Data masuk per hari
    "keluar" => array_values($keluar_data) // Data keluar per hari
]);

mysqli_close($koneksi);
?>
