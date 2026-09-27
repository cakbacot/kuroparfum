<?php
// ===================================================
// Kuro Atelier - Dedicated Kuro Master Admin Desk Page
// ===================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

initSession();
if (!isAdmin()) {
    header('Location: index.php?auth_modal=login&alert=Akses+khusus+Kuro+Master+Perfumer');
    exit;
}

$pageTitle = 'Panel Kurasi Kuro Master • Ginza Central Command';
$currentPage = 'admin';

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-12 bg-black/90 min-h-[90vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Admin Desk Banner -->
        <div class="glass-kuro border border-amber-500/40 rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none select-none font-kanji text-[180px] text-amber-400">
                黒
            </div>

            <div class="space-y-2 relative z-10">
                <div class="flex items-center gap-2 text-amber-400 text-xs font-mono tracking-widest uppercase">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <span>KURO ATELIER • TOKYO GINZA CENTRAL DESK</span>
                </div>
                <h1 class="font-serif-luxury text-2xl sm:text-3xl font-bold text-white tracking-wide">
                    PANEL KURASI KURO MASTER (黒)
                </h1>
                <p class="text-xs text-stone-400 max-w-2xl leading-relaxed">
                    Pusat kendali operasional pesanan website, pelacakan pengiriman (fulfillment), manajemen inventaris produk, serta kurasi eksklusif pemohon whitelist.
                </p>
            </div>

            <div class="flex items-center gap-3 relative z-10 w-full sm:w-auto">
                <button onclick="KuroApp.refreshAdminData()" class="btn-outline-gold px-4 py-2.5 rounded-xl text-xs flex items-center justify-center gap-2 flex-1 sm:flex-initial">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>Segarkan Data</span>
                </button>
                <a href="index.php" class="btn-gold px-5 py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 flex-1 sm:flex-initial shadow-lg">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    <span>Lihat Showroom</span>
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4">
            <!-- Total Orders -->
            <div class="glass-kuro p-4 rounded-xl border border-stone-800 space-y-1">
                <span class="text-[10px] text-stone-500 font-mono uppercase block">TOTAL PESANAN</span>
                <div id="admin-stats-total-orders" class="font-serif-luxury text-2xl font-bold text-amber-300">0</div>
                <span class="text-[10px] text-stone-500">Karya terpesan</span>
            </div>

            <!-- Pending Fulfillment -->
            <div class="glass-kuro p-4 rounded-xl border border-amber-500/40 bg-amber-950/20 space-y-1">
                <span class="text-[10px] text-amber-400 font-mono uppercase block">PERLU DIPROSES</span>
                <div id="admin-stats-pending-fulfillment" class="font-serif-luxury text-2xl font-bold text-amber-400">0</div>
                <span class="text-[10px] text-amber-300/80">Kemas / Pengiriman</span>
            </div>

            <!-- Paid vs Unpaid -->
            <div class="glass-kuro p-4 rounded-xl border border-stone-800 space-y-1">
                <span class="text-[10px] text-stone-500 font-mono uppercase block">STATUS BAYAR</span>
                <div class="flex items-baseline gap-2">
                    <span id="admin-stats-paid-orders" class="font-serif-luxury text-xl font-bold text-emerald-400">0</span>
                    <span class="text-[10px] text-stone-600">/</span>
                    <span id="admin-stats-unpaid-orders" class="font-serif-luxury text-lg font-bold text-stone-400">0</span>
                </div>
                <span class="text-[10px] text-stone-500">Lunas / Belum</span>
            </div>

            <!-- Total Revenue -->
            <div class="glass-kuro p-4 rounded-xl border border-stone-800 space-y-1">
                <span class="text-[10px] text-stone-500 font-mono uppercase block">TOTAL PENDAPATAN</span>
                <div id="admin-stats-revenue" class="font-serif-luxury text-lg sm:text-xl font-bold text-gold-gradient truncate">Rp 0</div>
                <span class="text-[10px] text-stone-500">Transaksi Lunas</span>
            </div>

            <!-- Available Flacons -->
            <div class="glass-kuro p-4 rounded-xl border border-stone-800 space-y-1">
                <span class="text-[10px] text-stone-500 font-mono uppercase block">FLACON AKTIF</span>
                <div id="admin-stats-available" class="font-serif-luxury text-2xl font-bold text-white">0</div>
                <span class="text-[10px] text-stone-500">Koleksi Tersedia</span>
            </div>

            <!-- Whitelist Pending -->
            <div class="glass-kuro p-4 rounded-xl border border-stone-800 space-y-1">
                <span class="text-[10px] text-stone-500 font-mono uppercase block">KURASI PENDING</span>
                <div id="admin-stats-pending-whitelist" class="font-serif-luxury text-2xl font-bold text-amber-300">0</div>
                <span class="text-[10px] text-stone-500">Pemohon VIP</span>
            </div>
        </div>

        <!-- Admin Tab Navigation -->
        <div class="flex border-b border-stone-800 text-xs font-serif-luxury tracking-wider gap-2 overflow-x-auto pb-1">
            <button id="admin-tab-btn-orders" onclick="KuroApp.switchAdminTab('orders')" class="px-5 py-3 rounded-t-xl bg-stone-900 border-t border-x border-amber-400/60 text-amber-300 font-bold flex items-center gap-2 transition-all">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span>PESANAN & PENGIRIMAN (FULFILLMENT)</span>
            </button>
            <button id="admin-tab-btn-products" onclick="KuroApp.switchAdminTab('products')" class="px-5 py-3 rounded-t-xl text-stone-400 hover:text-white hover:bg-stone-900/50 flex items-center gap-2 transition-all">
                <i data-lucide="flask-conical" class="w-4 h-4"></i>
                <span>KATALOG PRODUK (CRUD)</span>
            </button>
            <button id="admin-tab-btn-whitelist" onclick="KuroApp.switchAdminTab('whitelist')" class="px-5 py-3 rounded-t-xl text-stone-400 hover:text-white hover:bg-stone-900/50 flex items-center gap-2 transition-all">
                <i data-lucide="scroll" class="w-4 h-4"></i>
                <span>KURASI WHITELIST</span>
            </button>
            <button id="admin-tab-btn-invites" onclick="KuroApp.switchAdminTab('invites')" class="px-5 py-3 rounded-t-xl text-stone-400 hover:text-white hover:bg-stone-900/50 flex items-center gap-2 transition-all">
                <i data-lucide="key" class="w-4 h-4"></i>
                <span>KODE UNDANGAN VIP</span>
            </button>
        </div>

        <!-- ==============================================
             PANEL TAB 1: ORDERS & FULFILLMENT MANAGEMENT
             ============================================== -->
        <div id="admin-panel-orders" class="space-y-6">
            <!-- Filter Bar -->
            <div class="glass-kuro p-4 rounded-xl border border-stone-800 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-mono text-stone-500 uppercase mr-1">Status Bayar:</span>
                    <button onclick="KuroApp.filterAdminOrders('payment', '')" id="filter-pay-all" class="px-2.5 py-1 rounded-lg text-xs bg-amber-500/20 text-amber-300 border border-amber-500/40 font-semibold">Semua</button>
                    <button onclick="KuroApp.filterAdminOrders('payment', 'pending')" id="filter-pay-pending" class="px-2.5 py-1 rounded-lg text-xs bg-stone-900 text-stone-400 hover:text-white border border-stone-800">Belum Bayar</button>
                    <button onclick="KuroApp.filterAdminOrders('payment', 'confirmed')" id="filter-pay-confirmed" class="px-2.5 py-1 rounded-lg text-xs bg-stone-900 text-stone-400 hover:text-white border border-stone-800">Lunas</button>

                    <span class="text-[10px] font-mono text-stone-500 uppercase ml-3 mr-1">Pengiriman:</span>
                    <button onclick="KuroApp.filterAdminOrders('fulfillment', '')" id="filter-ful-all" class="px-2.5 py-1 rounded-lg text-xs bg-amber-500/20 text-amber-300 border border-amber-500/40 font-semibold">Semua</button>
                    <button onclick="KuroApp.filterAdminOrders('fulfillment', 'menunggu')" id="filter-ful-menunggu" class="px-2.5 py-1 rounded-lg text-xs bg-stone-900 text-stone-400 hover:text-white border border-stone-800">Menunggu</button>
                    <button onclick="KuroApp.filterAdminOrders('fulfillment', 'dikemas')" id="filter-ful-dikemas" class="px-2.5 py-1 rounded-lg text-xs bg-stone-900 text-stone-400 hover:text-white border border-stone-800">📦 Dikemas</button>
                    <button onclick="KuroApp.filterAdminOrders('fulfillment', 'dikirim')" id="filter-ful-dikirim" class="px-2.5 py-1 rounded-lg text-xs bg-stone-900 text-stone-400 hover:text-white border border-stone-800">🚚 Dikirim</button>
                    <button onclick="KuroApp.filterAdminOrders('fulfillment', 'selesai')" id="filter-ful-selesai" class="px-2.5 py-1 rounded-lg text-xs bg-stone-900 text-stone-400 hover:text-white border border-stone-800">✅ Selesai</button>
                </div>

                <div class="relative w-full sm:w-64">
                    <input type="text" id="admin-orders-search" oninput="KuroApp.searchAdminOrders(this.value)" placeholder="Cari No. Order, Invoice, Nama..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-xl px-3.5 py-2 pl-9 text-xs text-white placeholder-stone-500 outline-none">
                    <i data-lucide="search" class="w-4 h-4 text-stone-500 absolute left-3 top-2.5"></i>
                </div>
            </div>

            <!-- Orders Container -->
            <div id="admin-orders-list" class="space-y-4">
                <div class="glass-kuro p-12 text-center rounded-2xl border border-stone-800 space-y-3">
                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-amber-400 mx-auto"></i>
                    <p class="text-xs text-stone-400">Memuat pesanan dan status pengiriman...</p>
                </div>
            </div>
        </div>

        <!-- ==============================================
             PANEL TAB 2: PRODUCT CRUD MANAGEMENT
             ============================================== -->
        <div id="admin-panel-products" class="space-y-6 hidden">
            <!-- Product Management Header -->
            <div class="glass-kuro p-4 rounded-xl border border-stone-800 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="font-serif-luxury text-sm font-bold text-white tracking-wider">MANAJEMEN INVENTARIS FLACON</h3>
                    <p class="text-xs text-stone-400">Kelola katalog Series 24, Mahakarya 1-of-1, pembaruan stok, harga, dan formula olfaktori.</p>
                </div>
                <button onclick="KuroApp.openProductModal()" class="btn-gold px-5 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>TAMBAH KOLEKSI BARU</span>
                </button>
            </div>

            <!-- Products List Container -->
            <div id="admin-products-list" class="space-y-4">
                <div class="glass-kuro p-12 text-center rounded-2xl border border-stone-800 space-y-3">
                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-amber-400 mx-auto"></i>
                    <p class="text-xs text-stone-400">Memuat inventaris flacon...</p>
                </div>
            </div>
        </div>

        <!-- ==============================================
             PANEL TAB 3: WHITELIST REQUESTS
             ============================================== -->
        <div id="admin-panel-whitelist" class="space-y-6 hidden">
            <div class="glass-kuro p-4 rounded-xl border border-stone-800 flex items-center justify-between">
                <div>
                    <h3 class="font-serif-luxury text-sm font-bold text-white tracking-wider">PERMOHONAN KURASI KOLEKTOR (WHITELIST)</h3>
                    <p class="text-xs text-stone-400">Verifikasi calon pembeli untuk akses karya eksklusif 1-of-1 Bespoke</p>
                </div>
            </div>

            <div id="admin-whitelist-requests" class="space-y-4">
                <div class="glass-kuro p-12 text-center rounded-2xl border border-stone-800 space-y-3">
                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-amber-400 mx-auto"></i>
                    <p class="text-xs text-stone-400">Memuat permohonan kurasi kolektor...</p>
                </div>
            </div>
        </div>

        <!-- ==============================================
             PANEL TAB 4: VIP INVITE CODES
             ============================================== -->
        <div id="admin-panel-invites" class="space-y-6 hidden">
            <!-- Generate Code Form -->
            <div class="glass-kuro p-6 rounded-2xl border border-amber-500/30 space-y-4">
                <div class="flex items-center gap-2 text-amber-400 text-xs font-serif-luxury font-bold tracking-wider">
                    <i data-lucide="key" class="w-4 h-4"></i>
                    <span>TERBITKAN KODE UNDANGAN RESMI</span>
                </div>
                <form id="form-generate-invite" onsubmit="KuroApp.generateInviteCode(event)" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Kode Custom (Opsional)</label>
                        <input type="text" id="invite-custom-code" placeholder="Otomatis jika kosong..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white uppercase font-mono outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Maks Penggunaan</label>
                        <input type="number" id="invite-max-uses" value="1" min="1" max="100" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Catatan Penerima</label>
                        <input type="text" id="invite-notes" placeholder="Contoh: Untuk Duta Besar Jepang..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="btn-gold w-full py-2 rounded-lg text-xs font-bold shadow-lg">
                            GENERATE KODE
                        </button>
                    </div>
                </form>
            </div>

            <!-- Active Codes List -->
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs font-mono text-stone-400 px-1">
                    <span>DAFTAR KODE UNDANGAN AKTIF</span>
                    <span>KUOTA & PENGGUNAAN</span>
                </div>
                <div id="admin-invite-codes-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <!-- Populated via loadAdminInviteCodes() -->
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ===================================================
     MODAL: ADMIN PRODUCT CRUD (CREATE & EDIT)
     =================================================== -->
<div id="admin-product-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
    <div class="glass-kuro border border-amber-500/40 rounded-2xl max-w-2xl w-full max-h-[92vh] overflow-y-auto p-6 md:p-8 relative shadow-2xl space-y-6">
        <button onclick="KuroApp.closeProductModal()" class="absolute top-5 right-5 text-stone-400 hover:text-white transition-colors z-10">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        <div class="space-y-1 border-b border-amber-500/30 pb-4">
            <div class="flex items-center gap-2 text-amber-400 text-[10px] font-mono tracking-widest uppercase">
                <i data-lucide="flask-conical" class="w-3.5 h-3.5"></i>
                <span>ATELIER MASTER INVENTORY</span>
            </div>
            <h3 id="admin-product-modal-title" class="font-serif-luxury text-xl font-bold text-white">
                TAMBAH KARYA PARFUM BARU
            </h3>
            <p class="text-xs text-stone-400">
                Lengkapi spesifikasi mahakarya olfaktori Kuro, stok unit, harga, serta deskripsi artistik.
            </p>
        </div>

        <form id="admin-product-form" onsubmit="KuroApp.submitProductModal(event)" class="space-y-4">
            <input type="hidden" id="admin-prod-id" value="0">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Nama Parfum *</label>
                    <input type="text" id="admin-prod-name" required placeholder="Contoh: KURO Kyara Oud" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Nama Kanji Jepang *</label>
                    <input type="text" id="admin-prod-kanji" required placeholder="Contoh: 黒伽羅沈香" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none font-kanji">
                </div>
            </div>

            <div>
                <label class="text-[10px] font-mono text-stone-400 uppercase">Subtitle / Nuansa Olfaktori Singkat *</label>
                <input type="text" id="admin-prod-subtitle" required placeholder="Contoh: Sacred Imperial Oud & Smoked Hinoki Resin" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Tipe Edisi *</label>
                    <select id="admin-prod-edition-type" onchange="KuroApp.onEditionTypeChange()" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                        <option value="Series-24">Series-24 (Komersial 24 Botol)</option>
                        <option value="1-of-1">1-of-1 (Mahakarya Tunggal Dunia)</option>
                        <option value="Limited Reserve">Limited Reserve (Privat)</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Harga (Rupiah) *</label>
                    <input type="number" id="admin-prod-price" required min="100000" step="10000" placeholder="Contoh: 4850000" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Alokasi Stok (Unit) *</label>
                    <input type="number" id="admin-prod-stock" required min="0" max="999" value="24" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Serial Edisi</label>
                    <input type="text" id="admin-prod-serial" placeholder="Series 24 Edition (Limit 24 Botol)" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none font-mono">
                </div>
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Volume (ml)</label>
                    <input type="number" id="admin-prod-volume" value="50" min="10" max="500" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Status Inventaris</label>
                    <select id="admin-prod-status" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                        <option value="active">Active (Tersedia di Showroom)</option>
                        <option value="vaulted">Vaulted (Tersimpan di Brankas)</option>
                        <option value="acquired">Acquired (Telah Terakuisisi)</option>
                        <option value="archived">Archived (Diarsipkan)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="text-[10px] font-mono text-stone-400 uppercase">Konsentrasi Minyak Parfum</label>
                <input type="text" id="admin-prod-concentration" value="Extrait de Parfum (35% Oil Concentration)" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
            </div>

            <div>
                <label class="text-[10px] font-mono text-stone-400 uppercase">Craftsmanship & Wadah Botol</label>
                <input type="text" id="admin-prod-craftsmanship" value="Flacon kristal obsidian dengan ukiran emas murni 24K dan segel tradisional Kyoto" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
            </div>

            <!-- Visual Flacon Presets -->
            <div>
                <label class="text-[10px] font-mono text-stone-400 uppercase block mb-1">Visual Flacon (Path Aset Gambar)</label>
                <div class="flex items-center gap-2 mb-2">
                    <input type="text" id="admin-prod-image" required value="assets/images/kuro_flacon_1.jpg" class="flex-1 bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white font-mono outline-none">
                </div>
                <div class="flex flex-wrap gap-2 text-[10px]">
                    <span class="text-stone-500 self-center">Pilihan Cepat:</span>
                    <button type="button" onclick="KuroApp.selectImagePreset('assets/images/kuro_kyara_oud.jpg')" class="px-2 py-1 rounded bg-stone-900 border border-stone-700 hover:border-amber-400 text-stone-300">Kyara Oud (Baru)</button>
                    <button type="button" onclick="KuroApp.selectImagePreset('assets/images/kuro_shogun.jpg')" class="px-2 py-1 rounded bg-stone-900 border border-stone-700 hover:border-amber-400 text-stone-300">Kuro Shogun (Baru)</button>
                    <button type="button" onclick="KuroApp.selectImagePreset('assets/images/kuro_flacon_1.jpg')" class="px-2 py-1 rounded bg-stone-900 border border-stone-700 hover:border-amber-400 text-stone-300">Flacon 1 (Gold/Obsidian)</button>
                    <button type="button" onclick="KuroApp.selectImagePreset('assets/images/kuro_flacon_2.jpg')" class="px-2 py-1 rounded bg-stone-900 border border-stone-700 hover:border-amber-400 text-stone-300">Flacon 2 (Sumi Ink)</button>
                    <button type="button" onclick="KuroApp.selectImagePreset('assets/images/kuro_flacon_3.jpg')" class="px-2 py-1 rounded bg-stone-900 border border-stone-700 hover:border-amber-400 text-stone-300">Flacon 3 (Kyoto Shrine)</button>
                </div>
            </div>

            <div>
                <label class="text-[10px] font-mono text-stone-400 uppercase">Deskripsi Filosofis Karya</label>
                <textarea id="admin-prod-desc" rows="3" required placeholder="Ceritakan sejarah, inspirasi kuil, dan perpaduan aroma yang terkandung..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none resize-none"></textarea>
            </div>

            <!-- Notes Pyramid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-black/40 p-3 rounded-xl border border-stone-800">
                <div>
                    <label class="text-[10px] font-mono text-amber-400 uppercase">Top Notes</label>
                    <input type="text" id="admin-prod-top-notes" placeholder="Contoh: Saffron, Bergamot..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-2.5 py-1.5 text-xs text-white outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-mono text-amber-400 uppercase">Heart Notes</label>
                    <input type="text" id="admin-prod-heart-notes" placeholder="Contoh: Black Rose, Smoked Leather..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-2.5 py-1.5 text-xs text-white outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-mono text-amber-400 uppercase">Base Notes</label>
                    <input type="text" id="admin-prod-base-notes" placeholder="Contoh: Kyara Oud, Ambergris..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-2.5 py-1.5 text-xs text-white outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-800">
                <button type="button" onclick="KuroApp.closeProductModal()" class="btn-outline-gold px-5 py-2.5 rounded-xl text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-gold px-7 py-2.5 rounded-xl text-xs font-bold shadow-lg flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan ke Lemari Kurasi</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================================================
     MODAL: ADMIN ORDER FULFILLMENT UPDATER
     =================================================== -->
<div id="admin-order-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
    <div class="glass-kuro border border-amber-500/40 rounded-2xl max-w-lg w-full p-6 md:p-8 relative shadow-2xl space-y-6">
        <button onclick="KuroApp.closeOrderModal()" class="absolute top-5 right-5 text-stone-400 hover:text-white transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        <div class="space-y-1 border-b border-amber-500/30 pb-4">
            <div class="flex items-center gap-2 text-amber-400 text-[10px] font-mono tracking-widest uppercase">
                <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                <span>KURIR & PELACAKAN PENGIRIMAN</span>
            </div>
            <h3 id="admin-order-modal-title" class="font-serif-luxury text-xl font-bold text-white">
                UPDATE STATUS PESANAN
            </h3>
            <p id="admin-order-modal-subtitle" class="text-xs text-stone-400 font-mono">
                Order #KURO-ORD-XXXX
            </p>
        </div>

        <form id="admin-order-form" onsubmit="KuroApp.submitOrderModal(event)" class="space-y-4">
            <input type="hidden" id="admin-order-id" value="0">

            <div>
                <label class="text-[10px] font-mono text-stone-400 uppercase">Tahapan Pengiriman (Fulfillment) *</label>
                <select id="admin-order-fulfillment-status" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                    <option value="menunggu">Menunggu Konfirmasi</option>
                    <option value="dikemas">Sedang Dikemas (Paulownia Wooden Box)</option>
                    <option value="dikirim">Sedang Melakukan Proses Pengiriman</option>
                    <option value="selesai">Barang Sudah Diterima Kolektor</option>
                    <option value="dibatalkan">Dibatalkan</option>
                </select>
            </div>

            <div>
                <label class="text-[10px] font-mono text-stone-400 uppercase">Status Pembayaran</label>
                <select id="admin-order-payment-status" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                    <option value="pending">Menunggu Pembayaran (Pending)</option>
                    <option value="confirmed">Pembayaran Lunas (Confirmed)</option>
                    <option value="failed">Gagal / Ditolak</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Nama Kurir Privat / Ekspedisi</label>
                    <input type="text" id="admin-order-courier" placeholder="Contoh: Kuro Private Imperial Courier" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Nomor Resi / AWB Pelacakan</label>
                    <input type="text" id="admin-order-tracking" placeholder="Contoh: KURO-EXP-2026-VIP88" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white font-mono outline-none">
                </div>
            </div>

            <div>
                <label class="text-[10px] font-mono text-stone-400 uppercase">Catatan Kurasi / Penjelasan Pengiriman</label>
                <textarea id="admin-order-notes" rows="3" placeholder="Contoh: Flacon telah disegel dengan stempel cap lilin merah dan kurir bersarung tangan sutra hitam dalam perjalanan..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-800">
                <button type="button" onclick="KuroApp.closeOrderModal()" class="btn-outline-gold px-5 py-2.5 rounded-xl text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-gold px-7 py-2.5 rounded-xl text-xs font-bold shadow-lg flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Perbarui Status Pesanan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Automatically load admin metrics and data tables upon entering admin page
    if (typeof KuroApp !== 'undefined') {
        KuroApp.loadAdminStats();
        KuroApp.loadAdminOrders();
        KuroApp.loadAdminProducts();
        KuroApp.loadAdminWhitelistRequests();
        KuroApp.loadAdminInviteCodes();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
