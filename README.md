# Sistem Prediksi Kelulusan Mahasiswa (Data Mining)

Aplikasi web berbasis **Laravel** untuk memprediksi probabilitas kelulusan tepat waktu seorang mahasiswa berdasarkan riwayat data akademik (Data Training). Proyek ini merupakan implementasi nyata dari teknik *Data Mining* dan *Machine Learning* untuk mengklasifikasikan data.

## Fitur Utama (Multi-Algoritma)

Sistem ini tidak hanya menggunakan satu model, melainkan membandingkan **3 Algoritma Klasifikasi** sekaligus yang bisa dipilih secara dinamis oleh pengguna:

1. **Naive Bayes (dengan Laplace Smoothing)**
   Algoritma probabilistik yang menghitung probabilitas setiap kriteria (IPK, Kehadiran, SKS, Status Kerja) untuk menebak persentase kemungkinan Lulus atau Tidak Lulus secara independen.
2. **K-Nearest Neighbors (KNN)**
   Algoritma berbasis jarak (*Euclidean Distance*) yang mencari 5 mahasiswa pendahulu (K=5) dengan profil nilai paling mirip dengan inputan pengguna, lalu mengambil keputusan berdasarkan sistem *Voting* (Suara Terbanyak).
3. **Decision Tree (Pohon Keputusan - Information Gain/Entropy)**
   Algoritma yang membangun pohon keputusan secara otomatis dari seluruh data *training* yang ada. Sistem menelusuri ranting-ranting logika (*Rules/If-Then*) dari kondisi IPK, Kehadiran, SKS, dan Status Kerja untuk mencapai kesimpulan akhir.

## Teknologi yang Digunakan
- **Framework:** Laravel 11.x (PHP 8.x)
- **Frontend:** Bootstrap 5 (Responsive Layout)
- **Database:** MySQL / SQLite
- **Alerts:** SweetAlert2 (untuk pop-up interaktif hasil prediksi)

## Cara Menjalankan Project

1. Pastikan Anda telah menginstal PHP, Composer, dan *database server* (seperti MySQL/Laragon/XAMPP).
2. Lakukan *clone repository* ini:
   ```bash
   git clone https://github.com/Dimpraaa/TUGASANALITIKP11.git
   ```
3. Masuk ke dalam direktori project dan jalankan perintah install dependency:
   ```bash
   cd TUGASANALITIKP11
   composer install
   ```
4. Salin konfigurasi environment:
   ```bash
   cp .env.example .env
   ```
5. Sesuaikan konfigurasi *Database* Anda di dalam file `.env` (misalnya `DB_CONNECTION=mysql` dan `DB_DATABASE=mahasiswa`).
6. Jalankan migrasi database (jika ada):
   ```bash
   php artisan migrate
   ```
7. Generate *App Key* Laravel:
   ```bash
   php artisan key:generate
   ```
8. Jalankan *local development server*:
   ```bash
   php artisan serve
   ```
9. Buka browser dan akses ke `http://localhost:8000`.

---
*Proyek ini dibuat untuk memenuhi Tugas Kuliah - Semester 6: Analitik & Visualisasi Data.*
