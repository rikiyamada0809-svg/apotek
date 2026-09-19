# ✅ Todolist Pengerjaan Sistem Informasi Apotek

> **Tech Stack**: Laravel 11 + MySQL + Blade/Livewire  
> **Tanggal Mulai**: -  
> **Status**: Belum Dimulai

---

## Legenda Status
- `[ ]` Belum dikerjakan
- `[/]` Sedang dikerjakan
- `[x]` Selesai

---

## 🔧 FASE 0 – Setup & Persiapan Environment (Minggu 1)

### 0.1 Setup Development Environment
- [x] Install PHP 8.2+, Composer, Node.js, MySQL
- [x] Install Laravel Installer (`composer global require laravel/installer`)
- [x] Setup code editor (VS Code + extensions: Laravel, PHP Intelephense, MySQL)
- [x] Install & konfigurasi XAMPP / Laragon / Docker

### 0.2 Inisialisasi Project Laravel
- [x] Buat project baru: `composer create-project laravel/laravel apotek`
- [x] Setup `.env` file (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- [x] Buat database MySQL: `apotek_db`
- [x] Test koneksi database: `php artisan migrate`
- [x] Setup Git repository & `.gitignore`
- [x] Buat commit awal: `git init && git add . && git commit -m "Initial project setup"`

### 0.3 Install Package Dependencies
- [x] Install Laravel Breeze / Jetstream (auth scaffolding)
  ```bash
  composer require laravel/breeze --dev
  php artisan breeze:install blade
  ```
- [x] Install Spatie Permission (RBAC): `composer require spatie/laravel-permission`
- [x] Install Laravel DomPDF (cetak PDF): `composer require barryvdh/laravel-dompdf`
- [x] Install Maatwebsite Excel (export Excel): `composer require maatwebsite/excel`
- [x] Install Laravel Livewire (interaktif UI): `composer require livewire/livewire`
- [x] Install Laravel Debugbar (dev): `composer require barryvdh/laravel-debugbar --dev`
- [x] Install Intervention Image (gambar produk): `composer require intervention/image`
- [x] `npm install && npm run build`

### 0.4 Konfigurasi Dasar
- [x] Setup timezone di `config/app.php` → `Asia/Jakarta`
- [x] Setup locale → `id`
- [x] Setup mata uang (helper format Rupiah)
- [x] Konfigurasi filesystem (storage untuk upload foto resep, dokumen)
- [x] Setup logging channel
- [x] Buat base layout template (header, sidebar, footer)

---

## 🏗️ FASE 1 – Foundation: Master Data & Auth (Minggu 2-5)

### 1.1 Database Migration – Master Data

#### Tabel Users & RBAC
- [x] Migration `users` (tambah kolom: phone, role, is_active, avatar)
- [x] Migration `roles` (via Spatie) — Pemilik, Manajer, Apoteker, Kasir, Admin Gudang
- [x] Migration `permissions` (via Spatie) — per modul per aksi
- [x] Seeder: buat roles & permissions default
- [x] Seeder: buat user admin default (Pemilik)

#### Tabel Kategori & Satuan
- [x] Migration `categories` (id, name, parent_id, description, is_active, timestamps)
- [x] Migration `units` (id, name, abbreviation, timestamps)
- [x] Seeder: data kategori obat (Antibiotik, Analgesik, Vitamin, dll)
- [x] Seeder: data satuan (Box, Strip, Tablet, Botol, Tube, Sachet, dll)

#### Tabel Pabrik & Supplier
- [x] Migration `manufacturers` (id, name, country, contact, is_active, timestamps)
- [x] Migration `suppliers` (id, code, name, contact_person, phone, email, address, city, tax_id, payment_terms, bank_name, bank_account_number, bank_account_name, is_active, notes, timestamps)

#### Tabel Produk
- [x] Migration `products` (id, product_code, barcode, name, generic_name, brand, category_id, drug_class, form, strength, manufacturer_id, requires_prescription, storage_condition, description, min_order_qty, is_active, image_url, timestamps)
- [x] Migration `product_units` (id, product_id, unit_id, conversion_factor, is_base_unit, purchase_price, selling_price, wholesale_price, prescription_price, barcode, timestamps)

#### Tabel Pelanggan & Dokter
- [x] Migration `customers` (id, code, name, phone, email, address, date_of_birth, gender, customer_type, membership_number, membership_valid_until, discount_percentage, credit_limit, outstanding_balance, allergy_notes, is_active, timestamps)
- [x] Migration `doctors` (id, name, sip_number, specialization, phone, clinic_name, clinic_address, is_active, timestamps)

#### Tabel Gudang
- [x] Migration `warehouses` (id, name, address, type, is_active, timestamps)
- [x] Migration `storage_locations` (id, warehouse_id, code, name, type, temperature_range, is_active, timestamps)

### 1.2 Model & Relationship

- [x] Model `User` — hasRoles (Spatie RBAC), fillable & casts
- [x] Model `Category` — hasMany(Products), belongsTo(parent Category), hasMany(children)
- [x] Model `Unit` — hasMany(ProductUnits)
- [x] Model `Manufacturer` — hasMany(Products)
- [x] Model `Supplier` — fillable & scopes
- [x] Model `Product` — belongsTo(Category, Manufacturer), hasMany(ProductUnits)
- [x] Model `ProductUnit` — belongsTo(Product, Unit)
- [x] Model `Customer` — fillable & membership casts
- [x] Model `Doctor` — fillable & scopes
- [x] Model `Warehouse` — hasMany(StorageLocations)
- [x] Model `StorageLocation` — belongsTo(Warehouse)

### 1.3 CRUD Master Data – Controllers & Views

#### Manajemen User
- [x] `UserController` — index, create, store, edit, update, destroy
- [x] View: daftar user (search, filter role)
- [x] View: form tambah/edit user (assign role)
- [x] Validasi: email unique, password policy
- [x] Fitur: aktif/nonaktifkan user
- [x] Fitur: reset password

#### Kategori
- [x] `CategoryController` — CRUD
- [x] View: daftar kategori (support parent-child)
- [x] View: form tambah/edit kategori
- [x] Validasi: nama kategori & perlindungan relasi

#### Satuan
- [x] `UnitController` — CRUD
- [x] View: daftar satuan
- [x] View: form tambah/edit satuan

#### Pabrik
- [x] `ManufacturerController` — CRUD
- [x] View: daftar pabrik
- [x] View: form tambah/edit pabrik

#### Supplier
- [x] `SupplierController` — CRUD
- [x] View: daftar supplier (search, filter kota)
- [x] View: form tambah/edit supplier
- [x] View: detail supplier (informasi umum & pembayaran/perbankan)

#### Produk / Obat
- [x] `ProductController` — CRUD
- [x] View: daftar produk (search, filter kategori/golongan/pabrik)
- [x] View: form tambah produk (informasi dasar & harga satuan multi-unit Alpine.js)
- [x] View: form edit produk
- [x] View: detail produk (info lengkap, tabel satuan & harga)
- [x] Fitur: multi-satuan & harga per satuan (harga beli, harga jual, grosir, resep)

#### Pelanggan
- [x] `CustomerController` — CRUD
- [x] View: daftar pelanggan (search, filter tipe)
- [x] View: form tambah/edit pelanggan
- [x] View: detail pelanggan (informasi keuangan, diskon, limit piutang, alergi)

#### Dokter
- [x] `DoctorController` — CRUD
- [x] View: daftar dokter
- [x] View: form tambah/edit dokter

#### Gudang & Lokasi
- [x] `WarehouseController` — CRUD
- [x] `StorageLocationController` — CRUD
- [x] View: daftar gudang
- [x] View: daftar & kelola lokasi per gudang (rak, kulkas, lemari narkotika)

### 1.4 Autentikasi & Middleware
- [x] Setup login/logout (via Breeze)
- [x] Role & Permission matrix RBAC (Spatie)
- [x] Dashboard routing

### 1.5 Layout & UI Framework
- [x] Setup template admin ApotekCare (Emerald theme, responsive sidebar, navbar)
- [x] Komponen Blade: alert notifikasi dismissable, badge status aktif/nonaktif, delete confirmation button
- [x] Dashboard apotek dengan kartu metrik ringkasan master data & quick action input

---

## 💊 FASE 2 – Core Operations (Minggu 6-11)

### 2.1 Database Migration – Transaksi

#### Tabel Batch
- [x] Migration `batches` (id, product_id, batch_number, manufacturing_date, expiry_date, supplier_id, purchase_price, received_qty, current_qty, status, barcode, notes, received_date, receipt_id, timestamps)

#### Tabel Stok
- [x] Migration `stocks` (id, product_id, batch_id, warehouse_id, location_id, quantity, reserved_qty, available_qty, status, updated_at)
- [x] Migration `stock_settings` (id, product_id, warehouse_id, min_stock, max_stock, reorder_point, safety_stock, lead_time_days, abc_category, updated_at)
- [x] Migration `stock_mutations` (id, product_id, batch_id, warehouse_id, mutation_type, reference_type, reference_id, quantity, stock_before, stock_after, notes, created_by, created_at)

#### Tabel Pembelian
- [x] Migration `purchase_orders` (id, po_number, supplier_id, warehouse_id, order_date, expected_delivery_date, status, subtotal, discount_amount, tax_amount, grand_total, approved_by, approved_at, notes, created_by, timestamps)
- [x] Migration `purchase_order_items` (id, po_id, product_id, unit_id, quantity_ordered, quantity_received, unit_price, discount, subtotal, notes)
- [x] Migration `goods_receipts` (id, receipt_number, po_id, supplier_id, receipt_date, delivery_note_number, status, received_by, verified_by, notes, timestamps)
- [x] Migration `goods_receipt_items` (id, receipt_id, po_item_id, product_id, batch_number, expiry_date, quantity_received, storage_location_id, condition, notes)
- [x] Migration `purchase_invoices` (id, invoice_number, po_id, supplier_id, invoice_date, due_date, total_amount, paid_amount, payment_status, notes, timestamps)
- [x] Migration `purchase_payments` (id, invoice_id, payment_date, amount, payment_method, reference_number, bank_account, notes, created_by)

#### Tabel Penjualan
- [x] Migration `shifts` (id, user_id, warehouse_id, start_time, end_time, opening_cash, closing_cash_system, closing_cash_actual, cash_difference, total_sales_cash, total_sales_non_cash, total_expenses, status, notes, timestamps)
- [x] Migration `sales` (id, invoice_number, sale_date, customer_id, cashier_id, pharmacist_id, prescription_id, subtotal, discount_amount, tax_amount, grand_total, payment_status, status, notes, shift_id, timestamps)
- [x] Migration `sale_items` (id, sale_id, product_id, batch_id, unit_id, quantity, unit_price, discount_per_item, subtotal, is_prescription, notes)
- [x] Migration `sale_payments` (id, sale_id, payment_method, amount, reference_number, payment_date)
- [x] Migration `cash_transactions` (id, shift_id, type, amount, description, reference, created_by, timestamps)

### 2.2 Model & Relationship – Transaksi
- [x] Model `Batch` — belongsTo(Product, Supplier), hasMany(Stocks, StockMutations)
- [x] Model `Stock` — belongsTo(Product, Batch, Warehouse, StorageLocation)
- [x] Model `StockMutation` — belongsTo(Product, Batch, Warehouse, User)
- [x] Model `StockSetting` — belongsTo(Product, Warehouse)
- [x] Model `PurchaseOrder` — belongsTo(Supplier, Warehouse, User), hasMany(PurchaseOrderItems, GoodsReceipts)
- [x] Model `PurchaseOrderItem` — belongsTo(PurchaseOrder, Product, Unit)
- [x] Model `GoodsReceipt` — belongsTo(PurchaseOrder, Supplier), hasMany(GoodsReceiptItems)
- [x] Model `GoodsReceiptItem` — belongsTo(GoodsReceipt, Product, StorageLocation)
- [x] Model `PurchaseInvoice` — belongsTo(PurchaseOrder, Supplier), hasMany(PurchasePayments)
- [x] Model `PurchasePayment` — belongsTo(PurchaseInvoice, User)
- [x] Model `Shift` — belongsTo(User, Warehouse), hasMany(Sales, CashTransactions)
- [x] Model `Sale` — belongsTo(Customer, User, Shift, Prescription), hasMany(SaleItems, SalePayments)
- [x] Model `SaleItem` — belongsTo(Sale, Product, Batch, Unit)
- [x] Model `SalePayment` — belongsTo(Sale)
- [x] Model `CashTransaction` — belongsTo(Shift, User)

### 2.3 Service Layer (Business Logic)
- [x] `StockService` — updateStock(), reserveStock(), releaseStock(), getAvailableStock(), getFEFOBatch()
- [x] `MutationService` — recordMutation() — catat setiap perubahan stok
- [x] `PurchaseService` — createPO(), approvePO(), receivePO()
- [x] `SaleService` — createSale(), voidSale(), calculateTotal()
- [x] `BatchService` — createBatch(), updateBatchQty(), checkExpiry()
- [x] `InvoiceNumberService` — generate nomor otomatis (INV-YYYYMMDD-XXXX, PO-YYYYMMDD-XXXX, dll)

### 2.4 Modul Pembelian (Purchasing)

#### Purchase Order
- [x] `PurchaseOrderController` — index, create, store, show, edit, update, approve, send, cancel
- [x] View: daftar PO (filter status, supplier, periode)
- [x] View: form buat PO (pilih supplier → tambah item produk → qty & harga → total)
- [x] View: detail PO (info PO, daftar item, status tracking, history penerimaan)
- [x] Fitur: auto-generate nomor PO (PO-YYYYMMDD-XXXX)
- [x] Fitur: hitung subtotal, diskon, PPN, grand total
- [x] Fitur: approval workflow (status draft → pending → approved)
- [x] Fitur: cetak PO (PDF)
- [x] Fitur: duplikat PO dari PO sebelumnya

#### Penerimaan Barang
- [x] `GoodsReceiptController` — index, create (dari PO), store, show, verify, approve
- [x] View: daftar penerimaan barang
- [x] View: form penerimaan (tampilkan item PO → input qty diterima, batch, expired, lokasi)
- [x] View: detail penerimaan
- [x] Fitur: validasi qty diterima ≤ qty outstanding PO
- [x] Fitur: input batch number & expiry date per item
- [x] Fitur: pilih lokasi penyimpanan per item
- [x] Fitur: update status PO (partially_received / received)
- [x] Fitur: auto-create Batch record
- [x] Fitur: auto-update Stock (tambah stok per batch)
- [x] Fitur: auto-create StockMutation (catat mutasi masuk)
- [x] Fitur: cetak bukti penerimaan (PDF)

#### Faktur & Pembayaran Supplier
- [x] `PurchaseInvoiceController` — index, create (dari PO/Receipt), store, show
- [x] `PurchasePaymentController` — store, show
- [x] View: daftar faktur (filter status bayar, jatuh tempo)
- [x] View: form input faktur (nomor faktur supplier, tanggal, jatuh tempo)
- [x] View: form pembayaran (metode, jumlah, referensi)
- [x] View: detail faktur + riwayat pembayaran
- [x] Fitur: tracking jatuh tempo
- [x] Fitur: partial payment
- [x] Fitur: update payment_status otomatis

### 2.5 Modul POS (Point of Sale)

#### Shift / Kasir
- [x] `ShiftController` — open, close, show
- [x] View: form buka shift (input kas awal)
- [x] View: form tutup shift (input kas fisik, tampilkan rekap)
- [x] View: rekap shift (total tunai, non-tunai, pengeluaran, selisih)
- [x] Fitur: cek shift aktif sebelum transaksi
- [x] Fitur: pengeluaran kas (petty cash) selama shift

#### Transaksi Penjualan
- [x] `PosController` — index, searchProducts, store, history, void, receipt
- [x] View: halaman POS (full-screen, optimized)
  - [x] Area pencarian produk (search + barcode scan)
  - [x] Daftar hasil pencarian (nama, stok, harga, satuan)
  - [x] Keranjang belanja (item, qty, satuan, harga, diskon, subtotal)
  - [x] Panel total (subtotal, diskon, pajak, grand total)
  - [x] Panel pembayaran (pilih metode, input nominal, hitung kembalian)
  - [x] Tombol: Hold, Void, Selesai, Cetak
- [x] Komponen Interaktif POS: Real-time update keranjang belanja (Alpine.js)
- [x] Fitur: pencarian produk (by nama / barcode / kode)
- [x] Fitur: tambah item ke keranjang
- [x] Fitur: ubah qty, ubah satuan (auto-convert harga)
- [x] Fitur: hapus item dari keranjang
- [x] Fitur: diskon per item (nominal / persentase)
- [x] Fitur: diskon total transaksi
- [x] Fitur: pilih pelanggan (opsional, untuk member discount)
- [x] Fitur: auto-select batch FEFO (expired terdekat duluan)
- [x] Fitur: validasi stok cukup sebelum checkout
- [x] Fitur: blokir obat expired dari penjualan
- [x] Fitur: tandai obat keras → wajib validasi apoteker
- [x] Fitur: multi metode pembayaran (split payment / cash / non-cash)
- [x] Fitur: hitung kembalian otomatis
- [x] Fitur: simpan transaksi (update stok, batch qty, mutasi)
- [x] Fitur: cetak struk (thermal 58mm/80mm format)
- [x] Fitur: hold/pending transaksi
- [x] Fitur: recall transaksi pending
- [x] Fitur: void transaksi (dengan approval + alasan)
- [x] Fitur: riwayat transaksi hari ini

#### Cetak Struk
- [x] Template struk thermal (58mm / 80mm)
- [x] Info struk: nama apotek, alamat, no telp, SIPA
- [x] Detail item: nama obat, qty, satuan, harga, subtotal
- [x] Total, diskon, pembayaran, kembalian
- [x] Tanggal, kasir, nomor invoice
- [x] Fitur: re-print struk dari riwayat transaksi

### 2.6 Stok & Batch Dasar
- [x] View: daftar stok (per produk, per batch, per gudang)
- [x] View: kartu stok digital (filter produk + periode)
- [x] View: detail batch (info batch, sisa stok, riwayat mutasi)
- [x] Fitur: stok alert pada sidebar/navbar (badge stok menipis)
- [x] Fitur: FEFO sorting pada daftar batch

---

## ⚙️ FASE 3 – Advanced Features (Minggu 12-17)

### 3.1 Database Migration – Retur, Opname, Resep

#### Tabel Retur
- [x] Migration `purchase_returns` (id, return_number, po_id, supplier_id, return_date, reason, status, resolution_type, total_amount, notes, approved_by, created_by, timestamps)
- [x] Migration `purchase_return_items` (id, return_id, product_id, batch_id, quantity, unit_price, subtotal, reason, notes)
- [x] Migration `sale_returns` (id, return_number, original_sale_id, customer_id, return_date, reason, status, resolution_type, refund_amount, refund_method, processed_by, approved_by, notes, timestamps)
- [x] Migration `sale_return_items` (id, return_id, original_sale_item_id, product_id, batch_id, quantity, unit_price, subtotal, item_condition, return_to_stock, notes)

#### Tabel Stok Opname
- [x] Migration `stock_opnames` (id, opname_number, warehouse_id, opname_date, scope, scope_filter, status, total_items, matched_items, discrepancy_items, notes, initiated_by, approved_by, approved_at, started_at, completed_at, timestamps)
- [x] Migration `stock_opname_items` (id, opname_id, product_id, batch_id, location_id, system_qty, physical_qty, discrepancy, discrepancy_reason, adjustment_approved, counted_by, counted_at, notes)

#### Tabel Resep & Racikan
- [x] Migration `prescriptions` (id, prescription_number, doctor_id, customer_id, prescription_date, diagnosis, status, validated_by, validated_at, image_url, notes, timestamps)
- [x] Migration `prescription_items` (id, prescription_id, product_id, dosage, frequency, duration_days, quantity, unit_id, instructions, is_compounded, notes)
- [x] Migration `compound_templates` (id, name, description, preparation_instructions, service_fee, created_by, timestamps)
- [x] Migration `compound_template_items` (id, template_id, product_id, quantity_per_unit, unit_id, notes)
- [x] Migration `compounds` (id, prescription_id, template_id, total_packages, service_fee, total_material_cost, total_price, prepared_by, verified_by, timestamps)
- [x] Migration `compound_items` (id, compound_id, product_id, batch_id, quantity_used, unit_id, unit_price, subtotal)

#### Tabel Expired & Pemusnahan
- [x] Migration `drug_destructions` (id, destruction_number, destruction_date, witness_name, witness_role, method, status, total_loss_amount, document_path, notes, executed_by, approved_by, timestamps)
- [x] Migration `drug_destruction_items` (id, destruction_id, product_id, batch_id, quantity, unit_price, loss_amount, reason, notes)

#### Tabel Batch Recall
- [x] Migration `batch_recalls` (id, batch_id, recall_number, recall_source, recall_reason, recall_date, affected_qty, sold_qty, recovered_qty, status, action_taken, notes, created_by, timestamps)

### 3.2 Model & Relationship – Lanjutan
- [x] Model `PurchaseReturn` + `PurchaseReturnItem`
- [x] Model `SaleReturn` + `SaleReturnItem`
- [x] Model `StockOpname` + `StockOpnameItem`
- [x] Model `Prescription` + `PrescriptionItem`
- [x] Model `CompoundTemplate` + `CompoundTemplateItem`
- [x] Model `Compound` + `CompoundItem`
- [x] Model `DrugDestruction` + `DrugDestructionItem`
- [x] Model `BatchRecall`

### 3.3 Modul Retur Pembelian
- [x] `PurchaseReturnController` — index, create, store, show, approve, send, resolve
- [x] View: daftar retur pembelian (filter status, supplier, periode)
- [x] View: form buat retur (pilih PO → pilih item & batch → qty & alasan)
- [x] View: detail retur (info, item, status tracking)
- [x] Fitur: auto-generate nomor retur (RTP-YYYYMMDD-XXXX)
- [x] Fitur: approval workflow
- [x] Fitur: pilih resolusi (tukar barang / credit note / refund)
- [x] Fitur: update stok saat retur dikirim (kurangi stok)
- [x] Fitur: catat mutasi stok keluar (reference: return)
- [x] Fitur: potong saldo faktur (jika credit note)
- [x] Fitur: cetak surat retur (PDF)

### 3.4 Modul Retur Penjualan
- [x] `SaleReturnController` — index, create, store, show, approve, complete
- [x] View: daftar retur penjualan (filter status, periode)
- [x] View: form buat retur (input nomor invoice → tampilkan item → pilih item retur)
- [x] View: detail retur
- [x] Fitur: auto-generate nomor retur (RTS-YYYYMMDD-XXXX)
- [x] Fitur: validasi periode retur (misal: max 7 hari)
- [x] Fitur: input kondisi barang (baik / rusak / expired)
- [x] Fitur: return to stock (jika kondisi baik)
- [x] Fitur: pilih resolusi (tukar obat / refund)
- [x] Fitur: hitung selisih harga jika tukar obat
- [x] Fitur: update stok & catat mutasi
- [x] Fitur: cetak bukti retur (PDF)

### 3.5 Modul Stok Opname
- [x] `StockOpnameController` — index, create, store, show, start, submit, review, approve, complete
- [x] View: daftar stok opname (filter status, gudang, periode)
- [x] View: form buat opname (pilih scope: full / kategori / lokasi)
- [x] View: halaman input opname (daftar produk, kolom qty sistem & qty fisik)
  - [x] Scan barcode → auto-fill qty
  - [x] Input manual qty fisik
  - [x] Highlight selisih otomatis (merah = kurang, biru = lebih)
- [x] View: review selisih (daftar item ada selisih + input alasan)
- [x] View: detail opname (summary, selisih, adjustment)
- [x] Fitur: auto-generate nomor opname (SO-YYYYMMDD-XXXX)
- [x] Fitur: freeze stok (blokir transaksi selama opname — opsional)
- [x] Fitur: hitung selisih otomatis (physical - system)
- [x] Fitur: approval adjustment oleh manajer
- [x] Fitur: auto-create StockMutation saat adjustment disetujui
- [x] Fitur: update stok sesuai qty fisik
- [x] Fitur: cetak laporan stok opname (PDF)

### 3.6 Modul Data Expired
- [x] `ExpiredController` — dashboard, list, alerts
- [x] View: dashboard expired (widget per level alert)
- [x] View: daftar obat near-expired (filter range: 1 bulan, 3 bulan, 6 bulan)
- [x] View: daftar obat sudah expired
- [x] Fitur: cron job / scheduled task cek expired harian
  ```php
  // routes/console.php
  Schedule::command('expired:check')->dailyAt('06:00');
  ```
- [x] Artisan Command `expired:check` — scan semua batch, update status, kirim notifikasi
- [x] Fitur: auto-update batch status → 'expired' jika tanggal terlewati
- [x] Fitur: auto-block batch expired dari POS (validasi saat checkout)
- [x] Fitur: notifikasi alert bertingkat (Normal/Warning/Caution/Critical/Expired)
- [x] Fitur: badge alert expired di navbar/sidebar

### 3.7 Modul Pemusnahan Obat
- [x] `DrugDestructionController` — index, create, store, show, approve, execute, complete
- [x] View: daftar pemusnahan
- [x] View: form buat pemusnahan (pilih batch expired/rusak → qty → metode → saksi)
- [x] View: detail pemusnahan
- [x] Fitur: auto-generate nomor pemusnahan
- [x] Fitur: approval workflow
- [x] Fitur: upload berita acara pemusnahan (PDF/gambar)
- [x] Fitur: update stok (kurangi) & catat kerugian
- [x] Fitur: cetak berita acara pemusnahan (PDF)

### 3.8 Modul Resep & Racikan
- [x] `PrescriptionController` — index, create, store, show, validate, dispense
- [x] View: daftar resep (filter status, dokter, periode)
- [x] View: form input resep (data dokter, pasien, item obat, dosis, aturan pakai)
- [x] View: detail resep (info, validasi apoteker, status dispensing)
- [x] Fitur: upload foto resep
- [x] Fitur: validasi oleh apoteker (approve/reject + catatan)
- [x] Fitur: link resep ke transaksi POS
- [x] Fitur: riwayat resep per pelanggan
- [x] Fitur: copy resep (untuk pembelian ulang)

#### Racikan
- [x] `CompoundController` — create, store, show
- [x] `CompoundTemplateController` — CRUD
- [x] View: daftar template racikan
- [x] View: form buat template racikan (nama, bahan + qty per bungkus, jasa racik)
- [x] View: form buat racikan dari resep (pilih template / manual → jumlah bungkus)
- [x] Fitur: kalkulasi otomatis bahan berdasarkan jumlah bungkus
- [x] Fitur: hitung harga racikan (HPP bahan + jasa racik)
- [x] Fitur: pengurangan stok per bahan (FEFO)
- [x] Fitur: cetak etiket obat racikan

---

## 📊 FASE 4 – Dashboard, Laporan & Enhancement (Minggu 18-23)

### 4.1 Dashboard Utama
- [x] `DashboardController` — index (per role)
- [x] Widget: Total Penjualan Hari Ini (omzet + jumlah transaksi)
- [x] Widget: Grafik Penjualan 30 Hari (line chart — Chart.js / ApexCharts)
- [x] Widget: Top 10 Obat Terlaris (bar chart)
- [x] Widget: Stok Alert (counter: habis, menipis, expired)
- [x] Widget: Hutang Supplier (total + jatuh tempo terdekat)
- [x] Widget: Piutang Pelanggan (total outstanding)
- [x] Widget: Near-Expired Alert (list + countdown)
- [x] Widget: Kas Hari Ini (summary card)
- [x] Fitur: filter periode (hari ini, minggu ini, bulan ini, custom range)
- [x] Fitur: dashboard berbeda per role

### 4.2 Modul Laporan

#### Infrastruktur Laporan
- [x] Base `ReportController` — handle filter, generate, export
- [x] Layout laporan PDF (header: logo apotek, judul, periode, footer: halaman)
- [x] Export Excel (via Maatwebsite / Streamed CSV)
- [x] View: halaman laporan (pilih jenis, filter, preview, download)

#### Laporan Penjualan
- [x] Laporan Penjualan Harian (filter: tanggal, kasir, shift)
- [x] Laporan Penjualan per Produk (filter: periode, kategori)
- [x] Laporan Penjualan per Pelanggan (filter: periode, tipe)
- [x] Laporan Resep (filter: periode, dokter)
- [x] Laporan Laba Kotor (harga jual - harga beli per item)
- [x] Laporan Metode Pembayaran (filter: periode)
- [x] Laporan Void & Retur Penjualan (filter: periode)

#### Laporan Stok & Gudang
- [x] Laporan Stok Terkini (filter: kategori, gudang)
- [x] Kartu Stok / Mutasi Stok (filter: produk, periode)
- [x] Laporan Stok Minimum (produk di bawah reorder point)
- [x] Laporan Expired (filter: range tanggal)
- [x] Laporan Dead Stock (tidak terjual > X hari)
- [x] Laporan Stok Opname (filter: periode, status)
- [x] Laporan Pemusnahan (filter: periode)
- [x] Laporan Analisis ABC

#### Laporan Pembelian & Keuangan
- [x] Laporan Pembelian (filter: periode, supplier)
- [x] Laporan Hutang Supplier (filter: supplier, status bayar)
- [x] Laporan Aging Hutang (kategori: belum jatuh tempo, 1-30 hari, 31-60, 61-90, >90)
- [x] Laporan Retur Pembelian (filter: periode, supplier)
- [x] Laporan Arus Kas (filter: periode)
- [x] Laporan Laba Rugi Sederhana (pendapatan - HPP - biaya)
- [x] Laporan Piutang Pelanggan (filter: pelanggan, status)

### 4.3 Pengelolaan Stok Lanjutan

#### Auto-Reorder
- [x] Artisan Command `stock:check-reorder` — cek stok vs reorder point
- [x] Schedule: jalankan setiap pagi (07:00)
- [x] Fitur: generate Purchase Request / Draft PO otomatis
- [x] Fitur: notifikasi ke Manajer / Dashboard (stok menipis / kritis / habis)
- [x] View: daftar rekomendasi reorder (produk, stok saat ini, reorder point, saran qty beli, estimasi anggaran, grouping supplier)

#### Analisis ABC
- [x] Artisan Command `stock:abc-analysis` — hitung klasifikasi ABC
- [x] Fitur: kalkulasi berdasarkan nilai penjualan X periode (30, 60, 90, 180, 365 hari)
- [x] Fitur: auto-update abc_category di stock_settings
- [x] View: laporan ABC (tabel + chart pareto Chart.js)

#### Transfer Stok (Multi-Cabang)
- [x] Migration `stock_transfers` + `stock_transfer_items`
- [x] `StockTransferController` — CRUD + approve + receive + cancel
- [x] View: form transfer (dari gudang → ke gudang → pilih produk & batch dinamis)
- [x] Fitur: approval workflow (draft → approved → received)
- [x] Fitur: update stok kedua gudang saat diterima + mutasi transfer_out / transfer_in
- [x] Fitur: cetak surat transfer / surat jalan (PDF)

### 4.4 Batch Recall
- [x] `BatchRecallController` — index, create, store, show, process, complete, printPdf
- [x] View: form recall (pilih batch → input info recall → identifikasi transaksi terdampak)
- [x] Fitur: auto-quarantine batch (status 'recalled', stok 'quarantined', available_qty = 0)
- [x] Fitur: identifikasi transaksi yang sudah menjual batch tersebut
- [x] Fitur: daftar pelanggan yang perlu dihubungi (nama, telp/WhatsApp link, invoice)
- [x] Fitur: tracking proses recall (status, qty recovered, recovery percentage)
- [x] Fitur: cetak Berita Acara Penarikan Obat Resmi (PDF)

---

## 🔒 FASE 5 – Polish, Optimasi & Deployment (Minggu 24-28)

### 5.1 Audit Trail & Keamanan
- [ ] Migration `audit_logs` (id, user_id, action, model_type, model_id, old_values, new_values, ip_address, user_agent, timestamps)
- [ ] Trait `Auditable` — auto-log setiap create/update/delete pada model
- [ ] View: audit log viewer (filter user, model, aksi, periode)
- [ ] Review semua form: CSRF token, validasi input, XSS prevention
- [ ] Review semua query: prevent SQL injection (gunakan Eloquent/prepared statement)
- [ ] Implementasi rate limiting pada API/login
- [ ] File upload validation (tipe, ukuran max)

### 5.2 Notifikasi
- [ ] Migration `notifications` (atau gunakan Laravel Notifications)
- [ ] Notification class: `StockLowNotification`
- [ ] Notification class: `ExpiredAlertNotification`
- [ ] Notification class: `POApprovalNotification`
- [ ] Notification class: `PaymentDueNotification`
- [ ] View: dropdown notifikasi di navbar (bell icon + counter)
- [ ] View: halaman semua notifikasi
- [ ] Fitur: mark as read / mark all as read
- [ ] Fitur: notifikasi email (opsional, untuk alert kritis)

### 5.3 Scheduled Tasks (Cron Jobs)
- [ ] `expired:check` — Cek batch expired setiap hari jam 06:00
- [ ] `stock:check-reorder` — Cek stok menipis setiap hari jam 07:00
- [ ] `payment:check-due` — Cek faktur jatuh tempo setiap hari jam 08:00
- [ ] `batch:update-status` — Update status batch (active → near_expired → expired)
- [ ] `report:daily-summary` — Generate ringkasan harian (opsional, kirim email)
- [ ] Setup cron di server: `* * * * * cd /path-to-project && php artisan schedule:run`

### 5.4 Performance & Optimasi
- [ ] Tambahkan database index pada kolom yang sering di-query/filter
  - [ ] `products`: index pada product_code, barcode, name, category_id, drug_class
  - [ ] `batches`: index pada product_id, expiry_date, status
  - [ ] `stocks`: index pada product_id, batch_id, warehouse_id
  - [ ] `sales`: index pada invoice_number, sale_date, customer_id, status
  - [ ] `stock_mutations`: index pada product_id, batch_id, reference_type, created_at
- [ ] Implementasi eager loading (hindari N+1 query)
- [ ] Cache: config, route (`php artisan config:cache`, `php artisan route:cache`)
- [ ] Cache: query yang sering dipakai (dashboard, stok summary) — Redis/file
- [ ] Pagination server-side pada semua daftar/datatable
- [ ] Optimasi gambar produk (resize, compress on upload)
- [ ] Minify CSS & JS (`npm run build`)

### 5.5 Testing
- [ ] Unit Test: StockService (update, FEFO, reserve, release)
- [ ] Unit Test: InvoiceNumberService (format, sequential)
- [ ] Unit Test: PurchaseService (create PO, approve, receive)
- [ ] Unit Test: SaleService (create sale, void, calculate total)
- [ ] Feature Test: Login / Logout / Role Access
- [ ] Feature Test: CRUD Master Data (Product, Supplier, Customer)
- [ ] Feature Test: Purchase Order workflow (create → approve → receive)
- [ ] Feature Test: POS transaction (add item → checkout → payment)
- [ ] Feature Test: Retur Pembelian & Penjualan
- [ ] Feature Test: Stok Opname workflow
- [ ] Jalankan semua test: `php artisan test`

### 5.6 Deployment Preparation
- [ ] Setup file `.env.production` (DB, mail, app_url, app_debug=false)
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `php artisan optimize`
- [ ] Run `npm run build` (production assets)
- [ ] Setup web server (Nginx / Apache) — virtual host, SSL
- [ ] Setup MySQL production (user, privilege, backup schedule)
- [ ] Setup daily backup script (database + storage)
- [ ] Setup cron job di server untuk `schedule:run`
- [ ] Migration production: `php artisan migrate --force`
- [ ] Seeder production: roles, permissions, admin user
- [ ] Test seluruh fitur di environment production
- [ ] Dokumentasi: cara install, konfigurasi, troubleshooting

### 5.7 Dokumentasi
- [ ] README.md — cara install & setup project
- [ ] Panduan penggunaan per modul (user manual)
- [ ] API documentation (jika ada endpoint API)
- [ ] Dokumentasi database (deskripsi per tabel & kolom)
- [ ] Dokumentasi konfigurasi (.env variables)

---

## 📁 Struktur Folder Laravel (Referensi)

```
apotek/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── CheckExpiredBatches.php
│   │       ├── CheckStockReorder.php
│   │       ├── CheckPaymentDue.php
│   │       └── UpdateBatchStatus.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   ├── Master/
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── UnitController.php
│   │   │   │   ├── SupplierController.php
│   │   │   │   ├── CustomerController.php
│   │   │   │   ├── DoctorController.php
│   │   │   │   ├── ManufacturerController.php
│   │   │   │   ├── WarehouseController.php
│   │   │   │   └── UserController.php
│   │   │   ├── POS/
│   │   │   │   ├── SaleController.php
│   │   │   │   └── ShiftController.php
│   │   │   ├── Purchase/
│   │   │   │   ├── PurchaseOrderController.php
│   │   │   │   ├── GoodsReceiptController.php
│   │   │   │   ├── PurchaseInvoiceController.php
│   │   │   │   └── PurchasePaymentController.php
│   │   │   ├── Stock/
│   │   │   │   ├── StockController.php
│   │   │   │   ├── StockOpnameController.php
│   │   │   │   ├── StockTransferController.php
│   │   │   │   └── ExpiredController.php
│   │   │   ├── Return/
│   │   │   │   ├── PurchaseReturnController.php
│   │   │   │   └── SaleReturnController.php
│   │   │   ├── Pharmacy/
│   │   │   │   ├── PrescriptionController.php
│   │   │   │   ├── CompoundController.php
│   │   │   │   └── CompoundTemplateController.php
│   │   │   ├── Report/
│   │   │   │   ├── SalesReportController.php
│   │   │   │   ├── StockReportController.php
│   │   │   │   └── FinanceReportController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── DrugDestructionController.php
│   │   │   ├── BatchRecallController.php
│   │   │   └── NotificationController.php
│   │   ├── Middleware/
│   │   │   └── CheckShiftActive.php
│   │   └── Requests/
│   │       ├── ProductRequest.php
│   │       ├── PurchaseOrderRequest.php
│   │       ├── SaleRequest.php
│   │       └── ...
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── ProductUnit.php
│   │   ├── Category.php
│   │   ├── Unit.php
│   │   ├── Manufacturer.php
│   │   ├── Supplier.php
│   │   ├── Customer.php
│   │   ├── Doctor.php
│   │   ├── Warehouse.php
│   │   ├── StorageLocation.php
│   │   ├── Batch.php
│   │   ├── Stock.php
│   │   ├── StockSetting.php
│   │   ├── StockMutation.php
│   │   ├── PurchaseOrder.php
│   │   ├── PurchaseOrderItem.php
│   │   ├── GoodsReceipt.php
│   │   ├── GoodsReceiptItem.php
│   │   ├── PurchaseInvoice.php
│   │   ├── PurchasePayment.php
│   │   ├── Sale.php
│   │   ├── SaleItem.php
│   │   ├── SalePayment.php
│   │   ├── Shift.php
│   │   ├── CashTransaction.php
│   │   ├── PurchaseReturn.php
│   │   ├── PurchaseReturnItem.php
│   │   ├── SaleReturn.php
│   │   ├── SaleReturnItem.php
│   │   ├── StockOpname.php
│   │   ├── StockOpnameItem.php
│   │   ├── StockTransfer.php
│   │   ├── StockTransferItem.php
│   │   ├── Prescription.php
│   │   ├── PrescriptionItem.php
│   │   ├── Compound.php
│   │   ├── CompoundItem.php
│   │   ├── CompoundTemplate.php
│   │   ├── CompoundTemplateItem.php
│   │   ├── DrugDestruction.php
│   │   ├── DrugDestructionItem.php
│   │   ├── BatchRecall.php
│   │   └── AuditLog.php
│   ├── Services/
│   │   ├── StockService.php
│   │   ├── MutationService.php
│   │   ├── PurchaseService.php
│   │   ├── SaleService.php
│   │   ├── BatchService.php
│   │   ├── InvoiceNumberService.php
│   │   └── ReportService.php
│   ├── Traits/
│   │   └── Auditable.php
│   └── Notifications/
│       ├── StockLowNotification.php
│       ├── ExpiredAlertNotification.php
│       ├── POApprovalNotification.php
│       └── PaymentDueNotification.php
├── database/
│   ├── migrations/
│   │   ├── 0001_create_categories_table.php
│   │   ├── 0002_create_units_table.php
│   │   ├── 0003_create_manufacturers_table.php
│   │   ├── 0004_create_suppliers_table.php
│   │   ├── 0005_create_products_table.php
│   │   ├── 0006_create_product_units_table.php
│   │   ├── 0007_create_customers_table.php
│   │   ├── 0008_create_doctors_table.php
│   │   ├── 0009_create_warehouses_table.php
│   │   ├── 0010_create_storage_locations_table.php
│   │   ├── 0011_create_batches_table.php
│   │   ├── 0012_create_stocks_table.php
│   │   ├── ... (seluruh migration sesuai urutan dependensi)
│   │   └── 00XX_create_audit_logs_table.php
│   └── seeders/
│       ├── RolePermissionSeeder.php
│       ├── AdminUserSeeder.php
│       ├── CategorySeeder.php
│       ├── UnitSeeder.php
│       └── DemoDataSeeder.php (data dummy untuk testing)
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php (main layout)
│       │   ├── sidebar.blade.php
│       │   ├── navbar.blade.php
│       │   └── footer.blade.php
│       ├── auth/ (login, register, dll)
│       ├── dashboard/
│       ├── master/ (product, category, supplier, dll)
│       ├── pos/ (transaksi, shift)
│       ├── purchase/ (PO, receipt, invoice)
│       ├── stock/ (stok, opname, transfer, expired)
│       ├── returns/ (retur pembelian & penjualan)
│       ├── pharmacy/ (resep, racikan)
│       ├── reports/ (semua laporan)
│       ├── destruction/ (pemusnahan)
│       └── components/ (reusable blade components)
├── routes/
│   └── web.php
├── tests/
│   ├── Unit/
│   └── Feature/
└── ...
```

---

## 📌 Catatan Penting

> [!IMPORTANT]
> - Selalu jalankan `php artisan migrate` setelah membuat migration baru
> - Selalu buat Form Request validation untuk setiap form input
> - Gunakan database transaction pada operasi yang melibatkan multiple tabel (POS, penerimaan barang, retur)
> - Selalu catat mutasi stok di tabel `stock_mutations` untuk audit trail
> - Gunakan Soft Delete pada data master (product, supplier, customer) — jangan hard delete

> [!TIP]
> - Mulai dari CRUD master data yang paling sederhana (kategori, satuan) sebagai pemanasan
> - Buat dummy data (seeder) untuk mempercepat testing
> - Gunakan Livewire untuk halaman POS agar responsif tanpa full page reload
> - Pisahkan business logic ke Service class, jangan taruh semua di Controller

> [!WARNING]
> - Jangan skip validation — terutama pada POS, pembelian, dan stok
> - Selalu test skenario edge case: stok 0, batch expired, qty melebihi stok
> - Backup database sebelum melakukan migration di production

---

*Todolist ini akan diupdate seiring progress pengerjaan.*  
*Tanggal dibuat: 27 Agustus 2026*
