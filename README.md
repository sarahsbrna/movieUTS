# Refactoring Sistem Manajemen Film

Dokumen ini menguraikan refactoring yang dilakukan pada kelas MovieController dalam aplikasi tiMovie untuk meningkatkan kualitas kode, kemudahan perawatan, dan mengurangi duplikasi.

## Tinjauan Umum Refaktoring

Kelas MovieController direfaktorisasi untuk mengatasi beberapa masalah:

1. **Duplikasi Kode Validasi**: Mengekstrak logika validasi berulang ke dalam metode terpusat
2. **Logika Penanganan File**: Mengkonsolidasi logika pengunggahan, penamaan, dan penyimpanan file ke dalam metode yang dapat digunakan kembali

## Detail Refactoring

### 1. Refactoring Logika Validasi

**Masalah:**
- Aturan validasi diduplikasi di seluruh metode `store()` dan `update()`
- Sedikit perbedaan dalam aturan validasi antara operasi create dan update ditangani dengan kode yang diduplikasi
- Perubahan pada aturan validasi memerlukan pembaruan di beberapa tempat

**Solusi:**
- Membuat metode privat `getValidationRules()` yang memusatkan semua aturan validasi
- Menambahkan parameter untuk menyesuaikan aturan secara kondisional berdasarkan jenis operasi (membuat vs memperbarui)
- Menerapkan logika kondisional untuk kolom `foto_sampul` (diperlukan untuk catatan baru, opsional untuk pembaruan)
- Menambahkan validasi ID hanya untuk catatan baru

**Manfaat:**
- Sumber kebenaran tunggal untuk aturan validasi
- Pemeliharaan lebih mudah saat persyaratan validasi berubah
- Perbedaan yang lebih jelas antara persyaratan validasi buat dan perbarui
- Pengurangan duplikasi kode

### 2.Pemfaktoran Ulang Penanganan Berkas

**Masalah:**
- Logika pengunggahan berkas diduplikasi dalam metode `store()` dan `update()`
- Penanganan ekstensi berkas tidak konsisten (dikodekan sebagai 'jpg' dalam `store()` tetapi ditentukan secara dinamis dalam `update()`)
- Logika penamaan dan penyimpanan berkas berulang

**Solusi:**
- Membuat metode privat `handleFileUpload()` untuk merangkum semua logika penanganan file
- Secara konsisten menggunakan ekstensi file aktual dari file yang diunggah
- Memusatkan pembuatan UUID untuk nama file
- Menggunakan kembali metode yang sama untuk operasi pembuatan dan pembaruan

**Manfaat:**
- Penanganan berkas yang konsisten di semua operasi
- Menghilangkan masalah ekstensi berkas yang dikodekan secara keras
- Mengurangi duplikasi kode
- Menyederhanakan metode pengontrol utama

### 3. Peningkatan Tambahan

- **Peningkatan Organisasi Kode**: Menambahkan komentar PHPDoc untuk meningkatkan keterbacaan kode
- **Penamaan Variabel yang Lebih Baik**: Menggunakan nama variabel yang lebih deskriptif
- **Logika Pembaruan Terstruktur**: Membuat array `$updateData` terpisah untuk kejelasan
- **Penanganan File Bersyarat**: Hanya memproses operasi file saat diperlukan

## Implementasi

Refaktorisasi diimplementasikan dengan:

1. Mengekstrak aturan validasi umum ke metode privat
2. Membuat metode penanganan unggahan file khusus
3. Memperbarui metode pengontrol untuk menggunakan metode pembantu baru ini
4. Menambahkan dokumentasi yang sesuai

## Peluang Peningkatan di Masa Depan

Meskipun pemfaktoran ulang ini mengatasi masalah validasi dan penanganan berkas, peningkatan di masa mendatang dapat mencakup:

1. Menerapkan transaksi basis data untuk integritas data
2. Menambahkan penanganan kesalahan yang kuat untuk operasi berkas
3. Memindahkan validasi ke kelas Permintaan Formulir khusus
4. Mengekstrak penanganan berkas ke kelas layanan khusus
5. Menerapkan pemeriksaan otorisasi
6. Mengoptimalkan kueri basis data
7. Menstandarkan pola respons

## Kesimpulan

Pemfaktoran ulang ini meningkatkan kemudahan pemeliharaan MovieController dengan mengurangi duplikasi kode dan memusatkan logika umum. Perubahan tersebut membuat kode lebih tangguh sekaligus mempertahankan semua fungsi asli.
