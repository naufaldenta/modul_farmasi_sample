# Panduan Penggunaan Modul Farmasi SIMRS

Selamat datang di aplikasi Modul Farmasi SIMRS. Aplikasi ini dirancang untuk memudahkan manajemen pengelolaan obat-obatan mulai dari pencatatan master data, pembelian (PO), penerimaan barang, hingga distribusi ke unit-unit rumah sakit.

Berikut adalah alur (*flow*) atau tutorial urutan cara menggunakan aplikasi ini dari awal hingga akhir.

---

## 1. Persiapan Data Master (Master Data)
Sebelum mulai melakukan transaksi, pastikan Anda telah mengisi data master. Menu ini berada di *sidebar* sebelah kiri di bagian **Master Data**.

### A. Kategori Obat (Drug Categories)
1. Buka menu **Master Data** -> **Kategori Obat**.
2. Klik tombol **Tambah Kategori**.
3. Masukkan nama kategori (misalnya: *Antibiotik*, *Vitamin*, *Analgesik*).
4. Klik **Simpan**. Kategori ini akan digunakan saat mendaftarkan obat baru.

### B. Data Supplier (Suppliers)
1. Buka menu **Master Data** -> **Supplier**.
2. Klik tombol **Tambah Supplier**.
3. Isi detail supplier mulai dari Nama Supplier, Alamat, Nomor Telepon, hingga Nama Kontak (*Contact Person*).
4. Klik **Simpan**. Supplier ini nantinya akan digunakan saat Anda ingin memesan obat.

### C. Data Obat (Drugs)
1. Buka menu **Master Data** -> **Obat**.
2. Klik tombol **Tambah Obat**.
3. Isi informasi mengenai obat tersebut:
   - **Nama Obat**: Contoh "Paracetamol 500mg"
   - **SKU**: Kode unik obat (misal: `PRC-500`)
   - **Kategori**: Pilih dari kategori yang sudah dibuat sebelumnya
   - **Satuan**: Pilih satuan (Tablet, Sirup, Ampul, dll)
   - **Harga Beli**: Harga modal saat dibeli dari supplier
   - **Harga Jual**: Harga saat dijual ke pasien
   - **Stok Minimum**: Batas stok peringatan (misal: 100).
4. Klik **Simpan**. (Stok awal akan di set 0 dan harus ditambahkan lewat penerimaan barang).

---

## 2. Proses Pembelian Obat (Purchase Order / PO)
Ketika stok obat menipis, Anda harus membuat pesanan ke Supplier.

1. Buka menu **Transaksi** -> **Purchase Order**.
2. Klik tombol **Buat PO Baru**.
3. Pilih **Supplier** tujuan.
4. Pada bagian **Item Pesanan**, klik **Tambah Item**.
5. Pilih **Obat** yang ingin dibeli, lalu masukkan **Kuantitas** (Qty).
6. Jika ada lebih dari satu obat, klik **Tambah Item** lagi.
7. Setelah selesai, klik **Simpan PO**.
8. PO baru akan berstatus **Pending**. Untuk dapat menerima barang, Anda harus mengklik tombol **Approve** pada detail PO tersebut.

---

## 3. Proses Penerimaan Barang (Goods Receipt / GR)
Setelah PO di-*approve* dan fisik barang tiba dari Supplier, Anda harus mencatat penerimaan barang agar stok obat bertambah.

1. Buka menu **Transaksi** -> **Goods Receipt**.
2. Klik tombol **Terima Barang**.
3. Pilih **Purchase Order (PO)** yang statusnya sudah *Approved* dari *dropdown*.
4. Sistem akan otomatis memunculkan daftar obat sesuai dengan yang dipesan di PO.
5. Untuk setiap obat, wajib mengisi:
   - **No. Batch (Lot)**: Kode produksi dari pabrik.
   - **Tanggal Kedaluwarsa**: Tanggal *Expired*.
   - **Qty**: Jumlah yang diterima.
   - *(Tips: Jika satu obat datang dengan 2 No. Batch berbeda, Anda bisa mengklik tombol "+ Tambah Batch Lain" di bawah nama obat tersebut).*
6. Klik **Simpan Penerimaan**. 
7. **Selesai!** Pada tahap ini, **Stok Obat otomatis bertambah** di sistem sesuai jumlah yang diterima.

---

## 4. Proses Distribusi Obat (Distributions)
Setelah obat ada di gudang farmasi, Anda bisa mendistribusikannya ke berbagai unit (misalnya: IGD, Rawat Inap, Poli Umum, dll).

1. Buka menu **Transaksi** -> **Distribusi**.
2. Klik tombol **Buat Distribusi**.
3. Pilih **Unit Tujuan** (Destination Unit).
4. Pada bagian Item, pilih Obat yang akan didistribusikan.
5. **Penting:** Sistem akan meminta Anda memilih obat berdasarkan **Batch** (agar sistem FIFO/FEFO berjalan). Pilih Batch yang memiliki stok, lalu masukkan jumlah (Qty) yang ingin dikirim.
6. Klik **Simpan Distribusi**.
7. Status distribusi awal adalah **Pending**. Stok di gudang utama belum terpotong.
8. Klik tombol **Approve** pada halaman detail distribusi. Setelah di-*approve*, **Stok di gudang utama otomatis berkurang**.

---

## 5. Laporan & Analisis
Untuk memantau performa inventaris, Anda bisa menggunakan menu Laporan.

### A. Kartu Stok (Stock Card)
- Buka menu **Laporan** -> **Kartu Stok**.
- Fitur ini berfungsi seperti buku tabungan untuk setiap obat. Anda bisa melihat riwayat keluar-masuknya suatu obat secara spesifik beserta saldo stok akhirnya.

### B. Analisis ABC (ABC Analysis)
- Buka menu **Laporan** -> **Analisis ABC**.
- Fitur ini mengelompokkan obat berdasarkan nilai investasinya (Kategori A = Paling berharga, C = Paling murah). Sangat berguna untuk manajemen prioritas stok.

### C. Prediksi Kebutuhan (Forecasting)
- Buka menu **Laporan** -> **Prediksi Kebutuhan**.
- Sistem akan menggunakan data historis (misal: riwayat distribusi bulan-bulan lalu) untuk memprediksi berapa jumlah obat yang harus Anda beli bulan depan agar tidak kehabisan stok.

---

## Ringkasan Alur:
1. **Master Data** (Kategori $\rightarrow$ Supplier $\rightarrow$ Obat)
2. **Order** (Purchase Order $\rightarrow$ Approve)
3. **Terima Barang** (Goods Receipt $\rightarrow$ Stok Bertambah)
4. **Keluarkan Barang** (Distribusi $\rightarrow$ Approve $\rightarrow$ Stok Berkurang)

Selamat bekerja!
