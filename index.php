<?php
// index.php

// Aktifkan pelaporan error PHP untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sertakan file koneksi database
include 'koneksi.php';

// Pastikan koneksi database berhasil sebelum melanjutkan
if (!$koneksi) {
    die("<h1>Koneksi database gagal!</h1><p>Pastikan file koneksi.php sudah benar dan server database berjalan.</p><p>Error: " . mysqli_connect_error() . "</p>");
}

// 1. Ambil data total dari tabel `total_data`
$query_total = "SELECT total_masuk, total_keluar, status_gerbang FROM total_data WHERE id = 1";
$result_total = mysqli_query($koneksi, $query_total);

// Tambahkan pengecekan jika query gagal
if (!$result_total) {
    die("<h1>Error Query Data Total!</h1><p>Terjadi kesalahan saat mengambil data dari tabel 'total_data'.</p><p>Error: " . mysqli_error($koneksi) . "</p>");
}

$data_total = mysqli_fetch_assoc($result_total);

$total_masuk = $data_total['total_masuk'] ?? 0;
$total_keluar = $data_total['total_keluar'] ?? 0;
$status_gerbang_awal = $data_total['status_gerbang'] ?? 'tertutup'; // Default ke 'tertutup'

// 2. Ambil 10 data log terakhir dari tabel `log_pergerakan`
$query_log = "SELECT status, waktu FROM log_pergerakan ORDER BY id DESC LIMIT 10";
$result_log = mysqli_query($koneksi, $query_log);

// Tambahkan pengecekan jika query gagal
if (!$result_log) {
    die("<h1>Error Query Log Pergerakan!</h1><p>Terjadi kesalahan saat mengambil data dari tabel 'log_pergerakan'.</p><p>Error: " . mysqli_error($koneksi) . "</p>");
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Monitoring Gerbang IoT</title>
    <link rel="stylesheet" href="style.css">
    <!-- Font Poppins dari Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js CDN untuk grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- OLD LOCATION for Lucide Icons Script was here -->
</head>
<body>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">Monitoring Gerbang Otomatis</h1>
            <p class="header-subtitle">Dashboard untuk memantau aktivitas dan mengontrol gerbang IoT.</p>
        </header>

        <main class="dashboard-main">
            <!-- Ringkasan Metrik -->
            <section class="metric-cards-section">
                <div class="metric-card card-masuk">
                    <div class="card-icon-wrapper">
                        <!-- Icon: log-in (sesuai lucide-icons) -->
                        <i data-lucide="log-in" class="lucide-icon"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-label">Total Masuk</span>
                        <span class="card-value" id="totalMasukCount"><?php echo $total_masuk; ?></span>
                    </div>
                </div>

                <div class="metric-card card-keluar">
                    <div class="card-icon-wrapper">
                        <!-- Icon: log-out (sesuai lucide-icons) -->
                        <i data-lucide="log-out" class="lucide-icon"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-label">Total Keluar</span>
                        <span class="card-value" id="totalKeluarCount"><?php echo $total_keluar; ?></span>
                    </div>
                </div>
            </section>

            <!-- Kontrol Gerbang -->
            <section class="control-panel-section">
                <h2 class="section-title">Kontrol Gerbang</h2>
                <div class="gate-status-display">
                    <span class="status-label">Status Gerbang:</span>
                    <span class="status-badge <?php echo $status_gerbang_awal == 'terbuka' ? 'status-open' : 'status-closed'; ?>" id="gateStatus">
                        <?php echo htmlspecialchars(ucfirst($status_gerbang_awal)); ?>
                    </span>
                </div>
                <div class="control-buttons-group">
                    <button id="openGateBtn" class="btn btn-primary btn-open">Buka Gerbang</button>
                    <button id="closeGateBtn" class="btn btn-danger btn-close">Tutup Gerbang</button>
                </div>
            </section>

            <!-- Statistik Pergerakan Harian -->
            <section class="chart-section">
                <h2 class="section-title">Statistik Pergerakan Harian (7 Hari Terakhir)</h2>
                <div class="chart-container">
                    <canvas id="pergerakanChart"></canvas>
                </div>
            </section>

            <!-- Log Aktivitas Terakhir -->
            <section class="log-section">
                <h2 class="section-title">Log Aktivitas Terakhir</h2>
                <div class="table-responsive">
                    <table class="activity-log-table">
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
                                    $status_class = $row['status'] == 'masuk' ? 'status-masuk-log' : 'status-keluar-log';
                                    echo "<tr>";
                                    echo "<td>" . $nomor++ . "</td>";
                                    echo "<td><span class='log-status-badge " . $status_class . "'>" . htmlspecialchars(ucfirst($row['status'])) . "</span></td>";
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
            </section>
        </main>
    </div>

    <!-- NEW LOCATION AND DEFER ATTRIBUTE FOR LUCIDE ICONS SCRIPT, PLUS ONLOAD HANDLER -->
    <!-- Removed defer and onload from here, as the main script will wait for window.onload -->
    <script src="https://unpkg.com/lucide@latest/dist/lucide.js"></script>

    <script>
        // --- TAMBAHKAN LOG INI DI AWAL SKRIP UNTUK MEMASTIKAN EKSEKUSI ---
        console.log('Skrip dashboard mulai dimuat dan dieksekusi...');
        // --- AKHIR LOG DEBUGGING AWAL ---

        // Variabel global untuk grafik
        let pergerakanChart;

        // --- DEFINISIKAN sendGateCommand DI SINI (SEBELUM EVENT LISTENERS) ---
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
        // --- AKHIR DEFINISI sendGateCommand ---

        // Fungsi untuk mengambil dan memperbarui data total dan log
        async function updateDashboardData() {
            try {
                // Ambil data log terbaru
                const logResponse = await fetch('get_latest_logs.php');
                const logData = await logResponse.json();
                const logTableBody = document.getElementById('logTableBody');
                logTableBody.innerHTML = ''; // Kosongkan tabel log
                if (logData.length > 0) {
                    logData.forEach((log, index) => {
                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td><span class="log-status-badge status-${log.status}-log">${capitalizeFirstLetter(log.status)}</span></td>
                                <td>${formatDateTime(log.waktu)}</td>
                            </tr>
                        `;
                        logTableBody.innerHTML += row;
                    });
                } else {
                    logTableBody.innerHTML = '<tr><td colspan="3">Belum ada data aktivitas.</td></tr>';
                }

                // Ambil dan perbarui total masuk/keluar
                const totalResponse = await fetch('get_total_counts.php');
                const totalData = await totalResponse.json();
                document.getElementById('totalMasukCount').textContent = totalData.total_masuk;
                document.getElementById('totalKeluarCount').textContent = totalData.total_keluar;


                // Ambil dan perbarui status gerbang
                const gateStatusResponse = await fetch('get_gate_status.php');
                const gateStatusData = await gateStatusResponse.json();
                const gateStatusElement = document.getElementById('gateStatus');
                gateStatusElement.textContent = capitalizeFirstLetter(gateStatusData.status_gerbang);
                // Hapus semua kelas status sebelumnya dan tambahkan yang baru
                gateStatusElement.classList.remove('status-open', 'status-closed');
                gateStatusElement.classList.add(gateStatusData.status_gerbang === 'terbuka' ? 'status-open' : 'status-closed');
                
                // Perbarui grafik
                updateChartData();

            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            }
        }

        // Fungsi untuk memperbarui grafik
        async function updateChartData() {
            try {
                console.log('Mencoba fetch data grafik dari get_chart_data.php...');
                const response = await fetch('get_chart_data.php');
                
                console.log('Response dari fetch:', response);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Network response was not ok:', response.status, response.statusText, errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Data JSON yang diterima:', data);

                // Pastikan data yang diterima tidak null atau kosong
                const labels = data.labels || [];
                const masukData = data.masuk || [];
                const keluarData = data.keluar || [];

                // --- Penanganan jika data kosong atau tidak valid ---
                if (labels.length === 0 || (masukData.length === 0 && keluarData.length === 0)) {
                    console.warn('Data grafik kosong atau tidak lengkap. Grafik mungkin tidak akan ditampilkan.');
                    // Opsional: Tampilkan pesan di UI bahwa tidak ada data grafik
                    const chartContainer = document.querySelector('.chart-container');
                    if (chartContainer) {
                        chartContainer.innerHTML = '<p class="no-chart-data-message">Tidak ada data untuk menampilkan grafik.</p>';
                        chartContainer.style.display = 'flex';
                        chartContainer.style.justifyContent = 'center';
                        chartContainer.style.alignItems = 'center';
                        chartContainer.style.height = '400px'; // Pertahankan tinggi
                    }
                    if (pergerakanChart) {
                        pergerakanChart.destroy(); // Hancurkan grafik lama jika ada
                        pergerakanChart = null;
                    }
                    return; // Hentikan eksekusi jika data tidak ada
                } else {
                     // Hapus pesan jika ada data
                    const noDataMessage = document.querySelector('.no-chart-data-message');
                    if (noDataMessage) {
                        noDataMessage.remove();
                        // Pastikan canvas ada lagi
                        const chartContainer = document.querySelector('.chart-container');
                        if (!chartContainer.querySelector('canvas')) {
                            const newCanvas = document.createElement('canvas');
                            newCanvas.id = 'pergerakanChart';
                            chartContainer.appendChild(newCanvas);
                            chartContainer.style.display = 'block'; // Kembalikan display
                        }
                    }
                }
                // --- Akhir penanganan data kosong ---


                if (pergerakanChart) {
                    pergerakanChart.data.labels = labels;
                    pergerakanChart.data.datasets[0].data = masukData;
                    pergerakanChart.data.datasets[1].data = keluarData;
                    pergerakanChart.update();
                    console.log('Grafik diperbarui.');
                } else {
                    // Inisialisasi grafik jika belum ada
                    const ctx = document.getElementById('pergerakanChart').getContext('2d');
                    if (!ctx) {
                        console.error("Canvas context tidak ditemukan. Pastikan elemen canvas dengan id 'pergerakanChart' ada.");
                        return;
                    }
                    pergerakanChart = new Chart(ctx, {
                        type: 'bar', // Menggunakan bar chart untuk tampilan yang bersih
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Jumlah Masuk',
                                    data: masukData,
                                    backgroundColor: 'rgba(52, 152, 219, 0.7)', // Biru Alibaba
                                    borderColor: 'rgba(52, 152, 219, 1)',
                                    borderWidth: 1,
                                    borderRadius: 4
                                },
                                {
                                    label: 'Jumlah Keluar',
                                    data: keluarData,
                                    backgroundColor: 'rgba(231, 76, 60, 0.7)', // Merah
                                    borderColor: 'rgba(231, 76, 60, 1)',
                                    borderWidth: 1,
                                    borderRadius: 4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: {
                                            family: 'Poppins',
                                            size: 14
                                        },
                                        color: '#555'
                                    }
                                },
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
                                    },
                                    titleFont: {
                                        family: 'Poppins'
                                    },
                                    bodyFont: {
                                        family: 'Poppins'
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Jumlah Orang',
                                        font: {
                                            family: 'Poppins',
                                            size: 14,
                                            weight: 'bold'
                                        },
                                        color: '#333'
                                    },
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            family: 'Poppins'
                                        },
                                        color: '#666'
                                    },
                                    grid: {
                                        color: 'rgba(238, 238, 238, 0.5)' // Garis grid vertikal lembut
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Tanggal',
                                        font: {
                                            family: 'Poppins',
                                            size: 14,
                                            weight: 'bold'
                                        },
                                        color: '#333'
                                    },
                                    ticks: {
                                        font: {
                                            family: 'Poppins'
                                        },
                                        color: '#666'
                                    },
                                    grid: {
                                        display: false // Hilangkan garis grid horizontal
                                    }
                                }
                            }
                        }
                    });
                    console.log('Grafik baru diinisialisasi.');
                }
            } catch (error) {
                console.error('Error saat memperbarui atau menginisialisasi grafik:', error);
                const chartContainer = document.querySelector('.chart-container');
                if (chartContainer) {
                    chartContainer.innerHTML = '<p class="no-chart-data-message error-message">Gagal memuat grafik. Periksa koneksi atau data.</p>';
                    chartContainer.style.display = 'flex';
                    chartContainer.style.justifyContent = 'center';
                    chartContainer.style.alignItems = 'center';
                    chartContainer.style.height = '400px';
                }
                if (pergerakanChart) {
                    pergerakanChart.destroy();
                    pergerakanChart = null;
                }
            }
        }

        // --- Event listener untuk tombol Buka Gerbang (dipindahkan ke dalam DOMContentLoaded) ---
        // document.getElementById('openGateBtn').addEventListener('click', () => sendGateCommand('buka'));

        // --- Event listener untuk tombol Tutup Gerbang (dipindahkan ke dalam DOMContentLoaded) ---
        // document.getElementById('closeGateBtn').addEventListener('click', () => sendGateCommand('tutup'));

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
            console.log('DOMContentLoaded event fired.'); // Log ini juga
            // Panggil updateDashboardData() segera setelah DOM siap
            updateDashboardData();
            
            // Atur interval untuk memperbarui data setiap 5 detik
            setInterval(updateDashboardData, 5000); 

            // --- PASTIKAN sendGateCommand SUDAH TERDEFINISI SEBELUM MENAMBAHKAN LISTENER ---
            // Event listener untuk tombol Buka Gerbang
            if (document.getElementById('openGateBtn')) {
                document.getElementById('openGateBtn').addEventListener('click', () => sendGateCommand('buka'));
                console.log('Event listener untuk Buka Gerbang ditambahkan.');
            } else {
                console.warn('Tombol Buka Gerbang tidak ditemukan.');
            }

            // Event listener untuk tombol Tutup Gerbang
            if (document.getElementById('closeGateBtn')) {
                document.getElementById('closeGateBtn').addEventListener('click', () => sendGateCommand('tutup'));
                console.log('Event listener untuk Tutup Gerbang ditambahkan.');
            } else {
                console.warn('Tombol Tutup Gerbang tidak ditemukan.');
            }
            // --- AKHIR PENAMBAHAN LISTENER DALAM DOMContentLoaded ---
        });

        // Panggil lucide.createIcons() setelah SEMUA aset (termasuk skrip eksternal) dimuat
        window.onload = function() {
            console.log('window.onload event fired. Trying to create Lucide icons...');
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
                console.log('Lucide icons created successfully on window.onload.');
            } else {
                console.warn('Lucide object still not found or createIcons method missing on window.onload. Icons may not render.');
            }
        };

    </script>

</body>
</html>
<?php
// Tutup koneksi setelah selesai
mysqli_close($koneksi);
?>
