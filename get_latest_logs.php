<?php
// get_latest_logs.php
include 'koneksi.php';

header('Content-Type: application/json');

$query_log = "SELECT status, waktu FROM log_pergerakan ORDER BY id DESC LIMIT 10";
$result_log = mysqli_query($koneksi, $query_log);

$logs = [];
if ($result_log && mysqli_num_rows($result_log) > 0) {
    while ($row = mysqli_fetch_assoc($result_log)) {
        $logs[] = $row;
    }
}

echo json_encode($logs);

mysqli_close($koneksi);
?>
