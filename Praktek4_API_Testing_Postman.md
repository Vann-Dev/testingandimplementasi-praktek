# Praktek 4: API Testing dengan Postman

## Pengantar

Pada Praktek 3, kita sudah belajar menguji API menggunakan **PHPUnit + GuzzleHttp** (automated testing via code). Pada praktek ini, kita akan menguji **API yang sama** menggunakan **Postman** — sebuah tool GUI yang populer untuk testing API.

Kita akan belajar:
1. **Manual Testing** — Mengirim request satu per satu lewat Postman
2. **Test Script** — Menulis script otomatis di Postman untuk memvalidasi response
3. **Collection Runner** — Menjalankan semua test secara otomatis sekaligus

---

## Apa itu Postman?

**Postman** adalah aplikasi desktop yang digunakan untuk mengirim HTTP request ke API dan memeriksa response-nya. Postman sangat populer di industri karena:

- **Mudah digunakan** — Tampilan GUI, tidak perlu menulis kode untuk mengirim request
- **Test Script** — Bisa menulis JavaScript untuk memvalidasi response secara otomatis
- **Collection** — Bisa mengelompokkan request dan menjalankan semuanya sekaligus
- **Environment** — Bisa menyimpan variabel (seperti base URL) untuk digunakan ulang

### Perbandingan: PHPUnit vs Postman

| Aspek | PHPUnit + Guzzle (Praktek 3) | Postman (Praktek 4) |
|-------|------------------------------|---------------------|
| **Cara Kerja** | Menulis kode PHP | GUI (klik & ketik) |
| **Test Script** | PHP assertions | JavaScript (pm.test) |
| **Cocok Untuk** | CI/CD, automated pipeline | Eksplorasi API, debugging, demo |
| **Integrasi** | Berjalan di terminal | Aplikasi desktop / web |
| **Kolaborasi** | Kode di Git | Export/import Collection |

---

## Persiapan

### 1. Install Postman

Download Postman di: [https://www.postman.com/downloads/](https://www.postman.com/downloads/)

Install seperti biasa, lalu buka aplikasinya. Postman bisa digunakan **tanpa login** (klik "Skip and go to the app" jika diminta login).

### 2. Jalankan API Server

Kita akan menggunakan **API yang sama dari Praktek 3**. Buka terminal/command prompt, masuk ke folder project Praktek 3, lalu jalankan:

```
cd api-testing
php -S localhost:8080 -t api
```

> **Pastikan server tetap berjalan** selama praktek ini. Jangan tutup terminal ini.

Verifikasi di browser: buka `http://localhost:8080/products` — harusnya muncul data JSON produk.

---

## Bagian 1: Manual Testing dengan Postman

### Langkah 1: Buat Collection Baru

1. Buka Postman
2. Klik **"New"** (tombol + di sidebar kiri) → pilih **"Collection"**
3. Beri nama: **"Praktek 4 - Product API"**
4. Klik **"Create"**

Collection adalah folder untuk mengelompokkan semua request yang berhubungan.

---

### Langkah 2: Test GET Semua Produk

1. Klik **"Add a request"** di dalam collection
2. Beri nama: **"Get All Products"**
3. Pastikan method: **GET**
4. Masukkan URL: `http://localhost:8080/products`
5. Klik **"Send"**

**Yang harus dilihat di response:**
- **Status:** `200 OK` (terlihat di pojok kanan atas area response)
- **Body:** JSON berisi 3 produk (Laptop, Mouse, Keyboard)

```json
{
    "status": "success",
    "data": [
        {"id": 1, "name": "Laptop", "price": 15000000},
        {"id": 2, "name": "Mouse", "price": 250000},
        {"id": 3, "name": "Keyboard", "price": 500000}
    ]
}
```

---

### Langkah 3: Test GET Produk by ID

1. Klik **"Add a request"** → nama: **"Get Product by ID"**
2. Method: **GET**
3. URL: `http://localhost:8080/products/1`
4. Klik **"Send"**

**Response yang diharapkan (200 OK):**
```json
{
    "status": "success",
    "data": {"id": 1, "name": "Laptop", "price": 15000000}
}
```

Coba juga dengan ID yang tidak ada: `http://localhost:8080/products/999`

**Response yang diharapkan (404 Not Found):**
```json
{
    "status": "error",
    "message": "Produk tidak ditemukan"
}
```

---

### Langkah 4: Test POST Tambah Produk

1. Klik **"Add a request"** → nama: **"Create Product"**
2. Method: **POST**
3. URL: `http://localhost:8080/products`
4. Klik tab **"Body"** → pilih **"raw"** → pilih **"JSON"** dari dropdown
5. Masukkan body:

```json
{
    "name": "Monitor",
    "price": 3000000
}
```

6. Klik **"Send"**

**Response yang diharapkan (201 Created):**
```json
{
    "status": "success",
    "data": {"id": 4, "name": "Monitor", "price": 3000000}
}
```

---

### Langkah 5: Test PUT Update Produk

1. Klik **"Add a request"** → nama: **"Update Product"**
2. Method: **PUT**
3. URL: `http://localhost:8080/products/2`
4. Klik tab **"Body"** → pilih **"raw"** → pilih **"JSON"**
5. Masukkan body:

```json
{
    "name": "Gaming Mouse",
    "price": 500000
}
```

6. Klik **"Send"**

**Response yang diharapkan (200 OK):**
```json
{
    "status": "success",
    "data": {"id": 2, "name": "Gaming Mouse", "price": 500000}
}
```

---

### Langkah 6: Test DELETE Hapus Produk

1. Klik **"Add a request"** → nama: **"Delete Product"**
2. Method: **DELETE**
3. URL: `http://localhost:8080/products/3`
4. Klik **"Send"**

**Response yang diharapkan (200 OK):**
```json
{
    "status": "success",
    "message": "Produk berhasil dihapus"
}
```

> **Catatan:** Setelah DELETE, data `products.json` akan berubah. Jika ingin mereset data, copy ulang file `products.json` asli.

---

## Bagian 2: Menulis Test Script di Postman

Sampai sini kita hanya **melihat response secara manual**. Sekarang kita akan menulis **test script** — kode JavaScript yang **memvalidasi response secara otomatis**.

### Apa itu Test Script?

Test Script adalah kode JavaScript yang dijalankan **setelah response diterima**. Kita bisa menulis assertion untuk memverifikasi:
- Status code (200, 201, 404, dll)
- Isi response body
- Format data

### Cara Menulis Test Script

1. Buka request **"Get All Products"**
2. Klik tab **"Scripts"** → pilih **"Post-response"**
3. Tulis script berikut:

```javascript
// Test 1: Status code harus 200
pm.test("Status code harus 200", function () {
    pm.response.to.have.status(200);
});

// Test 2: Response harus berformat JSON
pm.test("Response berformat JSON", function () {
    pm.response.to.be.json;
});

// Test 3: Status di body harus "success"
pm.test("Status harus success", function () {
    var body = pm.response.json();
    pm.expect(body.status).to.eql("success");
});

// Test 4: Data harus berisi 3 produk
pm.test("Harus ada 3 produk", function () {
    var body = pm.response.json();
    pm.expect(body.data).to.have.lengthOf(3);
});
```

4. Klik **"Send"**
5. Lihat tab **"Test Results"** di bagian bawah response — harus muncul 4 test **PASS** (centang hijau)

### Penjelasan Syntax:

| Syntax | Fungsi |
|--------|--------|
| `pm.test("nama", function() {...})` | Membuat satu test case |
| `pm.response.to.have.status(200)` | Cek status code |
| `pm.response.to.be.json` | Cek response berformat JSON |
| `pm.response.json()` | Parse response body ke JavaScript object |
| `pm.expect(value).to.eql(expected)` | Cek apakah value sama dengan expected |
| `pm.expect(arr).to.have.lengthOf(n)` | Cek panjang array |

---

### Test Script untuk Setiap Request

Sekarang tambahkan test script ke semua request lainnya.

#### Get Product by ID — Tab Scripts → Post-response:

```javascript
pm.test("Status code harus 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Nama produk harus Laptop", function () {
    var body = pm.response.json();
    pm.expect(body.data.name).to.eql("Laptop");
});

pm.test("Harga produk harus 15000000", function () {
    var body = pm.response.json();
    pm.expect(body.data.price).to.eql(15000000);
});
```

#### Create Product — Tab Scripts → Post-response:

```javascript
pm.test("Status code harus 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Status harus success", function () {
    var body = pm.response.json();
    pm.expect(body.status).to.eql("success");
});

pm.test("Nama produk harus Monitor", function () {
    var body = pm.response.json();
    pm.expect(body.data.name).to.eql("Monitor");
});
```

#### Update Product — Tab Scripts → Post-response:

```javascript
pm.test("Status code harus 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Nama produk harus berubah", function () {
    var body = pm.response.json();
    pm.expect(body.data.name).to.eql("Gaming Mouse");
});
```

#### Delete Product — Tab Scripts → Post-response:

```javascript
pm.test("Status code harus 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Pesan harus 'Produk berhasil dihapus'", function () {
    var body = pm.response.json();
    pm.expect(body.message).to.eql("Produk berhasil dihapus");
});
```

---

## Bagian 3: Collection Runner (Automated Scenario)

Sekarang kita punya beberapa request yang masing-masing sudah memiliki test script. **Collection Runner** memungkinkan kita menjalankan **semua request secara berurutan** dalam satu kali klik.

### Mengapa Menggunakan Collection Runner?

- Menjalankan **semua test sekaligus** tanpa klik Send satu-satu
- Melihat **ringkasan hasil** (berapa test pass/fail)
- Bisa mengatur **urutan eksekusi** dan **jumlah iterasi**
- Cocok untuk **regression testing** — memastikan API masih bekerja setelah ada perubahan

### Langkah Menjalankan Collection Runner:

1. Pastikan data `products.json` sudah direset ke data awal (3 produk)
2. Klik kanan pada collection **"Praktek 4 - Product API"** di sidebar
3. Klik **"Run collection"**
4. Di halaman Runner, pastikan:
   - Semua request tercentang
   - Urutan request sudah benar:
     1. Get All Products
     2. Get Product by ID
     3. Create Product
     4. Update Product
     5. Delete Product
5. Klik **"Run Praktek 4 - Product API"**

### Membaca Hasil Collection Runner:

Setelah selesai, Postman akan menampilkan ringkasan:

- **Hijau (Pass)** — Test berhasil
- **Merah (Fail)** — Test gagal
- **Total**: Berapa test pass dari total test

Contoh hasil yang diharapkan:
```
✓ Get All Products        — 4/4 tests passed
✓ Get Product by ID       — 3/3 tests passed
✓ Create Product          — 3/3 tests passed
✓ Update Product          — 2/2 tests passed
✓ Delete Product          — 2/2 tests passed

Total: 14/14 tests passed
```

---

## Bagian 4: Menggunakan Variables & Environment

Sampai sini, kita menulis `http://localhost:8080` berulang kali di setiap request. Jika URL server berubah (misal dari localhost ke server production), kita harus mengubah **semua request** satu per satu.

**Environment Variables** memecahkan masalah ini — kita simpan URL di satu tempat, lalu referensikan di semua request.

### Langkah Membuat Environment:

1. Klik ikon **"Environments"** di sidebar kiri Postman
2. Klik **"+"** untuk membuat environment baru
3. Beri nama: **"Local"**
4. Tambahkan variable:
   - Variable: `base_url`
   - Initial Value: `http://localhost:8080`
   - Current Value: `http://localhost:8080`
5. Klik **"Save"**
6. Pilih environment **"Local"** di dropdown pojok kanan atas Postman

### Menggunakan Variable di Request:

Sekarang ubah URL di semua request:
- Sebelum: `http://localhost:8080/products`
- Sesudah: `{{base_url}}/products`

Contoh:
- `{{base_url}}/products` → Get All Products
- `{{base_url}}/products/1` → Get Product by ID
- `{{base_url}}/products` → Create Product
- `{{base_url}}/products/2` → Update Product
- `{{base_url}}/products/3` → Delete Product

Dengan cara ini, jika URL berubah, cukup ubah **satu kali** di Environment.

---

## Bagian 5: Export Collection

Collection yang sudah dibuat bisa di-export sebagai file JSON untuk dibagikan ke orang lain atau disimpan di Git.

### Cara Export:

1. Klik kanan pada collection **"Praktek 4 - Product API"**
2. Klik **"Export"**
3. Pilih format: **Collection v2.1**
4. Klik **"Export"** dan simpan file JSON

### Cara Import:

1. Klik **"Import"** di Postman
2. Pilih file JSON yang sudah di-export
3. Collection akan muncul di sidebar

---

## Kesimpulan

Pada praktek ini, kita telah mempelajari:

1. **Postman** adalah tool GUI untuk testing API yang mudah digunakan
2. **Manual Testing** — Mengirim request dan melihat response secara visual
3. **Test Script** — Menulis JavaScript (pm.test) untuk memvalidasi response secara otomatis
4. **Collection Runner** — Menjalankan semua request + test sekaligus (automated scenario)
5. **Environment Variables** — Menyimpan konfigurasi (seperti base URL) yang bisa dipakai ulang
6. **Export/Import** — Membagikan collection ke anggota tim

### Kapan Menggunakan Postman vs PHPUnit?

| Skenario | Gunakan |
|----------|---------|
| Eksplorasi API baru / debugging | **Postman** |
| Demo API ke tim / client | **Postman** |
| Automated testing di CI/CD pipeline | **PHPUnit + Guzzle** |
| Regression testing cepat | **Postman Collection Runner** |
| Testing di server tanpa GUI | **PHPUnit + Guzzle** |

---

## Soal Latihan

Kerjakan 2 soal berikut menggunakan Postman dan API yang sudah berjalan.

---

### Soal 1: Test Endpoint Not Found

Tambahkan request baru di collection dengan nama **"Get Product Not Found"**:

- Method: **GET**
- URL: `{{base_url}}/products/999`

**Tulis test script** yang memverifikasi:
1. Status code harus **404**
2. Field `status` di body harus `"error"`

---

### Soal 2: Test Create Product tanpa Field

Tambahkan request baru di collection dengan nama **"Create Product Missing Field"**:

- Method: **POST**
- URL: `{{base_url}}/products`
- Body (raw JSON):

```json
{
    "name": "Headset"
}
```

(Sengaja tidak ada field `price`)

**Tulis test script** yang memverifikasi:
1. Status code harus **400**
2. Field `status` di body harus `"error"`
