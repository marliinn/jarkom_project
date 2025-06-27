<?php
// Sertakan file koneksi database
include 'koneksi.php';

// 1. Ambil data total dari tabel `total_data`
$query_total = "SELECT total_masuk, total_keluar, status_gerbang FROM total_data WHERE id = 1";
$result_total = mysqli_query($koneksi, $query_total);
$data_total = mysqli_fetch_assoc($result_total);

$total_masuk = $data_total['total_masuk'] ?? 0;
$total_keluar = $data_total['total_keluar'] ?? 0;
$status_gerbang_awal = $data_total['status_gerbang'] ?? 'Tidak diketahui'; // Status gerbang awal dari DB

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
    <!-- Chart.js CDN untuk grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <div class="container">
        <header>
            <h1>Dashboard Monitoring Gate Otomatis</h1>
            <p>Data diperbarui secara otomatis. Status gerbang dan log aktivitas real-time.</p>
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
                        <p class="count" id="totalMasukCount"><?php echo $total_masuk; ?></p>
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
                        <p class="count" id="totalKeluarCount"><?php echo $total_keluar; ?></p>
                    </div>
                </div>
            </div>

            <div class="control-section">
                <h2>Kontrol Gerbang</h2>
                <div class="gate-status">
                    Status Gerbang: <span id="gateStatus" class="<?php echo $status_gerbang_awal == 'terbuka' ? 'status-terbuka' : 'status-tertutup'; ?>"><?php echo htmlspecialchars(ucfirst($status_gerbang_awal)); ?></span>
                </div>
                <div class="control-buttons">
                    <button id="openGateBtn" class="btn btn-open">Buka Gerbang</button>
                    <button id="closeGateBtn" class="btn btn-close">Tutup Gerbang</button>
                </div>
            </div>

            <div class="chart-section">
                <h2>Statistik Pergerakan Harian (7 Hari Terakhir)</h2>
                <div class="chart-container">
                    <canvas id="pergerakanChart"></canvas>
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
                    <tbody id="logTableBody">
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

    <script>
        // Variabel global untuk grafik
        let pergerakanChart;

        // Fungsi untuk mengambil dan memperbarui data total dan log
        async function updateDashboardData() {
            try {
                // Ambil data total dan log dari index.php itu sendiri (karena sudah di-render)
                // Sebenarnya lebih baik membuat endpoint API terpisah untuk ini juga,
                // tapi untuk kesederhanaan PHP native, kita bisa parse dari HTML jika perlu,
                // atau cukup mengandalkan pembaruan log aktivitas.
                // Untuk demo ini, kita akan fokus pada update status gerbang dan grafik via AJAX.
                
                // Ambil data log terbaru
                const logResponse = await fetch('get_latest_logs.php'); // Kita akan buat file ini juga!
                const logData = await logResponse.json();
                const logTableBody = document.getElementById('logTableBody');
                logTableBody.innerHTML = ''; // Kosongkan tabel log
                if (logData.length > 0) {
                    logData.forEach((log, index) => {
                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td><span class="status status-${log.status}">${capitalizeFirstLetter(log.status)}</span></td>
                                <td>${formatDateTime(log.waktu)}</td>
                            </tr>
                        `;
                        logTableBody.innerHTML += row;
                    });
                } else {
                    logTableBody.innerHTML = '<tr><td colspan="3">Belum ada data aktivitas.</td></tr>';
                }

                // Ambil dan perbarui total masuk/keluar
                const totalResponse = await fetch('get_total_counts.php'); // Kita akan buat file ini juga!
                const totalData = await totalResponse.json();
                document.getElementById('totalMasukCount').textContent = totalData.total_masuk;
                document.getElementById('totalKeluarCount').textContent = totalData.total_keluar;


                // Ambil dan perbarui status gerbang
                const gateStatusResponse = await fetch('get_gate_status.php');
                const gateStatusData = await gateStatusResponse.json();
                const gateStatusElement = document.getElementById('gateStatus');
                gateStatusElement.textContent = capitalizeFirstLetter(gateStatusData.status_gerbang);
                gateStatusElement.className = ''; // Reset class
                gateStatusElement.classList.add(gateStatusData.status_gerbang === 'terbuka' ? 'status-terbuka' : 'status-tertutup');
                
                // Perbarui grafik
                updateChartData();

            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            }
        }

        // Fungsi untuk memperbarui grafik
        async function updateChartData() {
            try {
                const response = await fetch('get_chart_data.php');
                const data = await response.json();

                if (pergerakanChart) {
                    pergerakanChart.data.labels = data.labels;
                    pergerakanChart.data.datasets[0].data = data.masuk;
                    pergerakanChart.data.datasets[1].data = data.keluar;
                    pergerakanChart.update();
                } else {
                    // Inisialisasi grafik jika belum ada
                    const ctx = document.getElementById('pergerakanChart').getContext('2d');
                    pergerakanChart = new Chart(ctx, {
                        type: 'bar', // Bisa juga 'line'
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Jumlah Masuk',
                                    data: data.masuk,
                                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1,
                                    borderRadius: 5
                                },
                                {
                                    label: 'Jumlah Keluar',
                                    data: data.keluar,
                                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    borderWidth: 1,
                                    borderRadius: 5
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Jumlah Orang'
                                    },
                                    ticks: {
                                        stepSize: 1 // Pastikan nilai Y adalah integer
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Tanggal'
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += context.raw + ' orang';
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error fetching chart data:', error);
            }
        }

        // Fungsi untuk mengirim perintah kontrol gerbang
        async function sendGateCommand(action) {
            const formData = new FormData();
            formData.append('action', action);

            try {
                const response = await fetch('kontrol_gerbang.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    alert('Sukses: ' + result.message);
                    // Setelah perintah dikirim, perbarui status dan data dashboard
                    updateDashboardData(); 
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error sending gate command:', error);
                alert('Terjadi kesalahan saat mengirim perintah. Pastikan ESP32 aktif dan IP benar.');
            }
        }

        // Event listener untuk tombol Buka Gerbang
        document.getElementById('openGateBtn').addEventListener('click', () => sendGateCommand('buka'));

        // Event listener untuk tombol Tutup Gerbang
        document.getElementById('closeGateBtn').addEventListener('click', () => sendGateCommand('tutup'));

        // Fungsi helper untuk kapitalisasi huruf pertama
        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        // Fungsi helper untuk format tanggal dan waktu
        function formatDateTime(dateTimeString) {
            const options = { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            return new Date(dateTimeString).toLocaleDateString('id-ID', options);
        }

        // Panggil fungsi pertama kali saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            updateDashboardData();
            // Atur interval untuk memperbarui data setiap 5 detik
            setInterval(updateDashboardData, 5000); 
        });

    </script>

</body>
</html>
<?php
// Tutup koneksi setelah selesai
mysqli_close($koneksi);
?>
