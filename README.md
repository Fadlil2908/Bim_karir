# Sistem Informasi Karir  

Sistem Informasi Karir adalah proyek berbasis web untuk mengelola data dan aktivitas terkait karir. Proyek ini dikembangkan menggunakan PHP dan MySQL.  

## Cara Installasi dan Penggunaan  

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di lingkungan lokal Anda:  

### 1. Unduh dan Ekstrak Proyek  
1. Download file dari repositori ini.  
2. Ekstrak file yang telah diunduh menggunakan [WinRAR](https://www.rarlab.com/download.htm) atau aplikasi serupa.  

### 2. Persiapan dan Konfigurasi  
1. Buka folder proyek di [Visual Studio Code](https://code.visualstudio.com/) untuk melihat atau mengedit kode.  
2. Pastikan Anda telah menginstal [XAMPP](https://www.apachefriends.org/) di komputer Anda.  

### 3. Jalankan Server Lokal  
1. Buka aplikasi **XAMPP** dan nyalakan **Apache** serta **MySQL**.  
2. Klik tombol **Admin** pada MySQL untuk membuka phpMyAdmin.  
3. Buat database baru dengan nama `karir`.  
4. Import file `karir.sql` yang terdapat dalam folder proyek ke database tersebut.  

### 4. Akses Aplikasi  
1. Buka browser Anda dan ketikkan URL berikut:  http://localhost/karir/index.php
2. Aplikasi siap digunakan.  

## Akun untuk Uji Coba  

Gunakan kredensial berikut untuk masuk dan menguji sistem:  

| **Username**   | **Password**   | **Role**   |  
|-----------------|----------------|------------|  
| admin123        | admin123       | Admin      |  
| dokter123       | dokter123      | Doctor     |  
| pasien123       | pasien123      | Patient    |  
| dokter111       | dokter111      | Doctor     |  
| pasien111       | pasien111      | Patient    |  

## Teknologi yang Digunakan  
- **Backend**: PHP  
- **Database**: MySQL  
- **Frontend**: HTML, CSS, JavaScript  

## Lisensi  
Proyek ini dilisensikan di bawah [MIT License](LICENSE).  
