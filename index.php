<?php
// Sertakan file koneksi database
include 'koneksi.php';

// 1. Ambil data total dari tabel `total_data`
$query_total = "SELECT total_masuk, total_keluar FROM total_data WHERE id = 1";
$result_total = mysqli_query($koneksi, $query_total);
$data_total = mysqli_fetch_assoc($result_total);

$total_masuk = $data_total['total_masuk'] ?? 0;
$total_keluar = $data_total['total_keluar'] ?? 0;

// 2. Ambil 10 data log terakhir dari tabel `log_pergerakan`
$query_log = "SELECT status, waktu FROM log_pergerakan ORDER BY id DESC LIMIT 10";
$result_log = mysqli_query($koneksi, $query_log);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Gate Otomatis</title>
    <link rel="stylesheet" href="style.css">
    <!-- Meta tag untuk refresh halaman setiap 5 detik -->
    <meta http-equiv="refresh" content="5">
</head>
<body>

    <div class="container">
        <header>
            <h1>Dashboard Monitoring Gate Otomatis</h1>
            <p>Data diperbarui secara otomatis setiap 5 detik</p>
        </header>

        <main>
            <div class="summary-cards">
                <div class="card card-masuk">
                    <div class="card-icon">
                        <!-- SVG untuk ikon masuk -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16">
                          <path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z"/>
                          <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                        </svg>
                    </div>
                    <div class="card-content">
                        <h2>Total Masuk</h2>
                        <p class="count"><?php echo $total_masuk; ?></p>
                    </div>
                </div>
                <div class="card card-keluar">
                    <div class="card-icon">
                        <!-- SVG untuk ikon keluar -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                          <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                          <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                        </svg>
                    </div>
                    <div class="card-content">
                        <h2>Total Keluar</h2>
                        <p class="count"><?php echo $total_keluar; ?></p>
                    </div>
                </div>
            </div>

            <div class="log-section">
                <h2>Log Aktivitas Terakhir</h2>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Status</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result_log) > 0) {
                            $nomor = 1;
                            while ($row = mysqli_fetch_assoc($result_log)) {
                                $status_class = $row['status'] == 'masuk' ? 'status-masuk' : 'status-keluar';
                                echo "<tr>";
                                echo "<td>" . $nomor++ . "</td>";
                                echo "<td><span class='status " . $status_class . "'>" . htmlspecialchars(ucfirst($row['status'])) . "</span></td>";
                                echo "<td>" . date('d F Y, H:i:s', strtotime($row['waktu'])) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>Belum ada data aktivitas.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>
<?php
// Tutup koneksi setelah selesai
mysqli_close($koneksi);
?>
