Untuk databasenya: 

-- Membuat database jika belum ada (opsional, bisa dibuat manual)
CREATE DATABASE IF NOT EXISTS db_gate_counter;

-- Menggunakan database yang telah dibuat
USE db_gate_counter;

-- --------------------------------------------------------

--
-- Struktur tabel untuk `log_pergerakan`
-- Tabel ini akan menyimpan setiap kejadian masuk atau keluar.
--
CREATE TABLE `log_pergerakan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(10) NOT NULL COMMENT '"masuk" atau "keluar"',
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur tabel untuk `total_data`
-- Tabel ini hanya akan berisi satu baris untuk menyimpan total agregat.
-- Ini lebih efisien daripada menghitung semua baris di `log_pergerakan` setiap kali halaman dimuat.
--
CREATE TABLE `total_data` (
  `id` int(11) NOT NULL DEFAULT 1,
  `total_masuk` int(11) NOT NULL DEFAULT 0,
  `total_keluar` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Memasukkan data awal untuk tabel `total_data`
-- Kita hanya butuh satu baris data di tabel ini.
--
INSERT INTO `total_data` (`id`, `total_masuk`, `total_keluar`) VALUES
(1, 0, 0);
