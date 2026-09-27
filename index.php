<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
initSession();
$currentUser = getCurrentUser();
$isWhitelisted = isWhitelisted();
?>
<!DOCTYPE html>
<html lang="id" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KURO (黒) • Haute Parfumerie 1-of-1 Bespoke Tokyo</title>
    <meta name="description" content="Platform eksklusif mini e-commerce parfum Kuro. Koleksi wewangian edisi tunggal (1-of-1) yang hanya dimiliki oleh satu orang di dunia.">
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        kuro: {
                            950: '#050506',
                            900: '#070708',
                            850: '#0c0c0e',
                            800: '#131316',
                            700: '#1c1c21',
                        },
                        gold: {
                            100: '#FFF7D6',
                            200: '#FCE79D',
                            300: '#F4D368',
                            400: '#E4BF44',
                            500: '#D4AF37',
                            600: '#B89324',
                            700: '#8E6E14',
                            800: '#644D0A',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Custom Luxury Stylesheet -->
    <link rel="stylesheet" href="assets/css/kuro.css">
</head>
<body class="bg-[#070708] text-stone-200 antialiased selection:bg-amber-500/30 selection:text-amber-200">

    <!-- ===================================================
         MAIN EXECUTIVE NAVIGATION BAR
         =================================================== -->
    <header class="sticky top-0 z-40 glass-kuro border-b border-gold-subtle">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="#hero" class="flex items-center gap-3.5 group">
                <div class="w-11 h-11 rounded-lg bg-black border border-gold-subtle flex items-center justify-center text-amber-400 font-kanji text-2xl font-bold group-hover:border-amber-400 transition-colors shadow-lg">
                    黒
                </div>
                <div>
                    <div class="font-serif-luxury font-bold text-xl tracking-[0.25em] text-white group-hover:text-amber-300 transition-colors">
                        KURO
                    </div>
                    <div class="text-[9px] font-mono tracking-[0.35em] text-amber-400/80 uppercase">
                        ATELIER TOKYO • 1-OF-1
                    </div>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden lg:flex items-center gap-8 text-xs font-serif-luxury tracking-widest text-stone-300">
                <a href="#teaser" class="hover:text-amber-300 transition-colors">VISI & TEASER</a>
                <a href="#filosofi" class="hover:text-amber-300 transition-colors">FILOSOFI</a>
                <a href="#showroom-section" class="hover:text-amber-300 transition-colors text-amber-200">RUANG PAMER PRIVAT</a>
                <a href="#coa-lookup" class="hover:text-amber-300 transition-colors">VERIFIKASI COA</a>
                <button id="nav-admin-link" onclick="KuroApp.toggleAdminDesk()" class="hidden text-amber-400 hover:text-amber-200 border-b border-amber-500/40 pb-0.5 transition-colors">
                    KURO DESK (ADMIN)
                </button>
            </nav>

            <!-- Actions Right -->
            <div class="flex items-center gap-3">
                <!-- Shopping Cart Button for Series 24 -->
                <button id="nav-cart-btn" onclick="KuroApp.openCartDrawer()" title="Keranjang Belanja Series 24" class="relative glass-kuro px-3 py-1.5 rounded-full border border-stone-800 hover:border-amber-500/40 flex items-center gap-2 transition-all">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-amber-400"></i>
                    <span class="text-xs text-stone-300 font-serif-luxury tracking-widest hidden sm:inline">KERANJANG</span>
                    <span id="nav-cart-count" class="hidden absolute -top-1.5 -right-1.5 bg-amber-500 text-black text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center font-mono shadow-md">0</span>
                </button>

                <!-- Zen Sound Synthesizer Toggle -->
                <button id="audio-toggle-btn" onclick="KuroApp.toggleAudio()" title="Suara Zen Lonceng Kuil Jepang" class="glass-kuro px-3 py-1.5 rounded-full border border-stone-800 hover:border-amber-500/40 flex items-center gap-2 transition-all">
                    <i data-lucide="volume-x" class="w-4 h-4 text-stone-500"></i>
                    <span class="text-xs text-stone-400 font-serif-luxury tracking-widest hidden md:inline">AUDIO OFF</span>
                </button>

                <!-- User Session & Whitelist Status Badge -->
                <div id="user-session-badge">
                    <!-- Populated dynamically via KuroApp.renderUserBadge() -->
                </div>
            </div>
        </div>
    </header>

    <main>
    <!-- ===================================================
         HERO SECTION (PRD: LANDING PAGE EKSKLUSIF & TEASER)
         "Pengguna baru hanya melihat teaser visual yang memancarkan
          aura elegan dan eksekutif tanpa menampilkan katalog penuh"
         =================================================== -->
    <section id="hero" class="relative min-h-[92vh] flex items-center justify-center overflow-hidden border-b border-stone-900">
        <!-- Hero Sumi-e Gold Ink Background -->
        <div class="absolute inset-0 bg-cover bg-center opacity-40 mix-blend-screen scale-105 transform animate-pulse-subtle" style="background-image: url('assets/images/kuro_hero_bg.jpg');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#070708] via-[#070708]/75 to-transparent"></div>
        <div class="absolute inset-0 bg-radial-gradient from-transparent via-[#070708]/60 to-[#070708]"></div>

        <div class="relative max-w-5xl mx-auto px-4 py-20 text-center space-y-8 z-10">
            <!-- Japanese Calligraphy Teaser Badge -->
            <div class="inline-flex items-center gap-2 border border-amber-500/30 bg-black/60 backdrop-blur-md px-4 py-1.5 rounded-full">
                <span class="font-kanji text-amber-400 text-sm">一期一会</span>
                <span class="w-1 h-1 rounded-full bg-amber-400"></span>
                <span class="font-mono text-xs text-amber-200/90 tracking-widest uppercase">ICHI-GO ICHI-E • SOLITARY CREATION</span>
            </div>

            <!-- Main Headline -->
            <div class="space-y-3">
                <h1 class="font-serif-luxury text-4xl sm:text-6xl md:text-7xl font-bold tracking-tight text-white leading-tight">
                    SATU KARYA.<br>
                    <span class="text-gold-gradient font-extrabold shimmer-text">SATU JIWA DI DUNIA.</span>
                </h1>
                <p class="font-editorial italic text-lg sm:text-2xl text-amber-100/80 max-w-2xl mx-auto font-light leading-relaxed">
                    "Kuro adalah wewangian yang tidak diciptakan untuk khalayak luas. Setiap formula diracik hanya untuk satu flacon, dimiliki oleh satu orang terpilih di bumi."
                </p>
            </div>

            <!-- Teaser Micro Specs -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-3xl mx-auto pt-4 text-center">
                <div class="glass-kuro p-3.5 rounded-xl border border-stone-800">
                    <div class="font-serif-luxury text-amber-400 text-lg font-bold">1-of-1</div>
                    <div class="text-[10px] text-stone-400 font-mono uppercase tracking-widest">Edisi Tunggal Global</div>
                </div>
                <div class="glass-kuro p-3.5 rounded-xl border border-stone-800">
                    <div class="font-serif-luxury text-amber-400 text-lg font-bold">38% - 40%</div>
                    <div class="text-[10px] text-stone-400 font-mono uppercase tracking-widest">Pure Extrait Concentration</div>
                </div>
                <div class="glass-kuro p-3.5 rounded-xl border border-stone-800">
                    <div class="font-serif-luxury text-amber-400 text-lg font-bold">80-Yr Kyara</div>
                    <div class="text-[10px] text-stone-400 font-mono uppercase tracking-widest">Resin Gaharu Purba</div>
                </div>
                <div class="glass-kuro p-3.5 rounded-xl border border-stone-800">
                    <div class="font-serif-luxury text-amber-400 text-lg font-bold">SHA-256 COA</div>
                    <div class="text-[10px] text-stone-400 font-mono uppercase tracking-widest">Sertifikat Kriptografis</div>
                </div>
            </div>

            <!-- Call to Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                <button onclick="KuroApp.openWhitelistModal('invite')" class="btn-gold px-8 py-3.5 rounded-xl text-xs font-bold flex items-center gap-2 w-full sm:w-auto justify-center">
                    <i data-lucide="key" class="w-4 h-4"></i>
                    <span>MASUKKAN KODE UNDANGAN VIP</span>
                </button>
                <button onclick="KuroApp.openWhitelistModal('apply')" class="btn-outline-gold px-8 py-3.5 rounded-xl text-xs font-semibold flex items-center gap-2 w-full sm:w-auto justify-center">
                    <i data-lucide="scroll-text" class="w-4 h-4 text-amber-400"></i>
                    <span>AJUKAN KURASI KEANGGOTAAN</span>
                </button>
                <a href="#showroom-section" class="text-xs text-stone-400 hover:text-amber-200 font-serif-luxury tracking-widest py-2 px-3 transition-colors flex items-center gap-1.5">
                    <span>LIHAT PAMERAN</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ===================================================
         FILOSOFI & ARAH VISUAL (PRD: WABI-SABI, SHIBUI, KURO)
         =================================================== -->
    <section id="filosofi" class="py-24 border-b border-stone-900 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
            <div class="text-center space-y-3 max-w-3xl mx-auto">
                <span class="text-xs font-mono text-amber-400 tracking-[0.3em] uppercase">FILOSOFI ATELIER JEPANG</span>
                <h2 class="font-serif-luxury text-3xl sm:text-4xl font-bold text-white tracking-wide">
                    KESENANGAN YANG TENANG DARI SEBUAH KELANGKAAN MUTLAK
                </h2>
                <div class="w-16 h-0.5 bg-amber-400 mx-auto"></div>
                <p class="text-xs sm:text-sm text-stone-400 font-light leading-relaxed">
                    Kuro menolak konsep produksi massal. Kami tidak menjual ribuan botol wewangian di rak pertokoan. Kami mengabadikan kepribadian seorang pemimpin ke dalam satu flacon tunggal yang tidak akan pernah direplikasi selamanya.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Pillar 1: Shibui -->
                <div class="glass-kuro-card p-8 rounded-2xl space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-black border border-amber-500/30 flex items-center justify-center text-amber-400 font-kanji text-2xl font-bold">
                        渋
                    </div>
                    <h3 class="font-serif-luxury text-lg font-bold text-amber-200">Shibui (渋い) • Keanggunan yang Tenang</h3>
                    <p class="text-xs text-stone-400 leading-relaxed font-light">
                        Keindahan yang matang, bersahaja, dan tidak berteriak untuk mencari perhatian. Aroma Kuro hadir dengan keheningan wibawa seorang pengambil keputusan besar—terasa intens, magnetis, namun penuh kelembutan kontemplatif.
                    </p>
                </div>

                <!-- Pillar 2: Wabi-Sabi -->
                <div class="glass-kuro-card p-8 rounded-2xl space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-black border border-amber-500/30 flex items-center justify-center text-amber-400 font-kanji text-2xl font-bold">
                        侘
                    </div>
                    <h3 class="font-serif-luxury text-lg font-bold text-amber-200">Wabi-Sabi (侘寂) • Kesempurnaan Bekas Luka</h3>
                    <p class="text-xs text-stone-400 leading-relaxed font-light">
                        Menerima keaslian waktu dan ketidaksempurnaan alami. Dari kayu Hinoki purba yang menua di kuil terpencil hingga botol keramik Kintsugi yang disatukan kembali dengan urat emas murni 24K cair oleh empu Kanazawa.
                    </p>
                </div>

                <!-- Pillar 3: Direct Concierge -->
                <div class="glass-kuro-card p-8 rounded-2xl space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-black border border-amber-500/30 flex items-center justify-center text-amber-400 font-kanji text-2xl font-bold">
                        匠
                    </div>
                    <h3 class="font-serif-luxury text-lg font-bold text-amber-200">Takumi Concierge • Percakapan Langsung</h3>
                    <p class="text-xs text-stone-400 leading-relaxed font-light">
                        Tidak ada perantara sales atau retail. Pembeli terhubung langsung dengan Kuro (Master Perfumer) dalam saluran enkripsi privat untuk membahas ukiran nama inisial, ritual pembukaan segel, dan pengantaran diplomatik pribadi.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================
         RUANG PAMER PRIVAT (BESPOKE SHOWCASE SECTION)
         "Halaman produk yang menonjolkan detail, cerita, dan komposisi...
          Label khusus untuk produk edisi tunggal (1-of-1 edition) di dunia."
         =================================================== -->
    <section id="showroom-section" class="py-24 border-b border-stone-900 bg-stone-950/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <span class="text-xs font-mono text-amber-400 tracking-[0.25em] uppercase">BESPOKE FLACON SHOWCASE</span>
                    </div>
                    <h2 class="font-serif-luxury text-3xl sm:text-4xl font-bold text-white tracking-wide">
                        KOLEKSI EDISI TUNGGAL (1-OF-1 GLOBAL)
                    </h2>
                    <p class="text-xs sm:text-sm text-stone-400 font-light max-w-xl">
                        Tiga mahakarya yang saat ini berada di ruang pamer privat Kuro Tokyo. Setiap botol hanya tersedia sebanyak satu unit di seluruh dunia.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="KuroApp.openConcierge()" class="btn-outline-gold px-4 py-2 rounded-lg text-xs flex items-center gap-2">
                        <i data-lucide="message-circle" class="w-4 h-4 text-amber-400"></i>
                        <span>KONSULTASI CONCIERGE KURO</span>
                    </button>
                </div>
            </div>

            <!-- Category Filter Tabs: All, Series 24, Bespoke 1-of-1 -->
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-stone-800/80 pb-4">
                <div class="flex flex-wrap items-center gap-2">
                    <button id="filter-cat-all" onclick="KuroApp.setCategoryFilter('all')" class="px-4 py-2 rounded-xl text-xs font-serif-luxury font-bold bg-amber-500/20 text-amber-300 border border-amber-400/50 shadow-md">
                        Semua Koleksi
                    </button>
                    <button id="filter-cat-series24" onclick="KuroApp.setCategoryFilter('series24')" class="px-4 py-2 rounded-xl text-xs font-serif-luxury text-stone-400 hover:text-white border border-stone-800 hover:border-stone-700 bg-stone-900/50 transition-colors flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>KURO SERIES 24 (LIMIT 24 BOTOL)</span>
                        <span class="bg-amber-400/20 text-amber-300 text-[10px] px-1.5 py-0.5 rounded font-mono font-bold">24 UNIT / PRODUK</span>
                    </button>
                    <button id="filter-cat-bespoke1of1" onclick="KuroApp.setCategoryFilter('bespoke1of1')" class="px-4 py-2 rounded-xl text-xs font-serif-luxury text-stone-400 hover:text-white border border-stone-800 hover:border-stone-700 bg-stone-900/50 transition-colors flex items-center gap-1.5">
                        <i data-lucide="shield" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>BESPOKE 1-OF-1 (WHITELIST EXCLUSIVE)</span>
                    </button>
                </div>
                <div class="text-xs font-mono text-stone-400 hidden lg:flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>Series 24: Terbuka untuk semua akun login</span>
                </div>
            </div>

            <!-- Dynamic Whitelist Alert Banner -->
            <div id="whitelist-alert-banner">
                <!-- Rendered by KuroApp.renderPrivateNotice() -->
            </div>

            <!-- Showroom Grid -->
            <div id="showroom-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Populated dynamically via KuroApp.renderShowroom() -->
            </div>
        </div>
    </section>

    <!-- ===================================================
         KURO ATELIER DESK / ADMIN SECTION (PRD: MANAGEMENT)
         Hidden by default, unlocked for Kuro Admin
         =================================================== -->
    <section id="admin-section" class="hidden py-16 bg-black border-b border-amber-500/40 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Admin Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-amber-500/30 pb-6">
                <div>
                    <div class="flex items-center gap-2 text-amber-400 text-xs font-mono tracking-widest uppercase">
                        <i data-lucide="shield-check" class="w-4 h-4 text-amber-400"></i>
                        <span>KURO ATELIER • CREATOR & MASTER DESK</span>
                    </div>
                    <h2 class="font-serif-luxury text-2xl md:text-3xl font-bold text-white mt-1">
                        PUSAT KENDALI PESANAN, PENGIRIMAN & INVENTARIS PRODUK
                    </h2>
                    <p class="text-xs text-stone-400 font-light mt-1">
                        Kelola alur pemesanan website, konfirmasi status pembayaran, perbarui tahapan pengiriman paket, dan lakukan manajemen katalog karya Kuro secara penuh.
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <button onclick="KuroApp.refreshAdminData()" class="glass-kuro px-3.5 py-2 rounded-xl text-xs text-amber-300 hover:text-white border border-amber-500/30 flex items-center gap-1.5 transition-all">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        <span>Segarkan Data</span>
                    </button>
                    <button onclick="KuroApp.toggleAdminDesk()" class="text-stone-400 hover:text-white text-xs font-mono px-3.5 py-2 rounded-xl border border-stone-800 hover:border-stone-700 transition-colors">
                        ✕ Tutup Panel
                    </button>
                </div>
            </div>

            <!-- Atelier Statistics Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
                <div class="glass-kuro p-4 rounded-xl border border-amber-500/30">
                    <div class="text-[10px] font-mono text-stone-400 uppercase">Total Pesanan Website</div>
                    <div id="stat-total-orders" class="font-serif-luxury text-2xl font-bold text-amber-300 mt-1">0</div>
                    <div class="text-[10px] text-stone-500 font-mono mt-0.5">Semua Transaksi Masuk</div>
                </div>
                <div class="glass-kuro p-4 rounded-xl border border-amber-500/30">
                    <div class="text-[10px] font-mono text-stone-400 uppercase">Perlu Diproses / Kirim</div>
                    <div id="stat-pending-fulfillment" class="font-serif-luxury text-2xl font-bold text-amber-400 mt-1">0</div>
                    <div class="text-[10px] text-stone-500 font-mono mt-0.5">Sedang Dikemas / Kirim</div>
                </div>
                <div class="glass-kuro p-4 rounded-xl border border-amber-500/30">
                    <div class="text-[10px] font-mono text-stone-400 uppercase">Total Flacon Tersedia</div>
                    <div id="stat-available-flacons" class="font-serif-luxury text-2xl font-bold text-amber-200 mt-1">0 / 0</div>
                    <div class="text-[10px] text-stone-500 font-mono mt-0.5">Edisi 1-of-1 & Series 24</div>
                </div>
                <div class="glass-kuro p-4 rounded-xl border border-amber-500/30">
                    <div class="text-[10px] font-mono text-stone-400 uppercase">Kolektor Sovereign VIP</div>
                    <div id="stat-approved-members" class="font-serif-luxury text-2xl font-bold text-emerald-400 mt-1">0</div>
                    <div class="text-[10px] text-stone-500 font-mono mt-0.5">Patron Whitelist Disetujui</div>
                </div>
                <div class="glass-kuro p-4 rounded-xl border border-amber-500/30 col-span-2 sm:col-span-1">
                    <div class="text-[10px] font-mono text-stone-400 uppercase">Total Valuasi Terkonfirmasi</div>
                    <div id="stat-total-revenue" class="font-serif-luxury text-lg font-bold text-gold-gradient mt-1">Rp 0</div>
                    <div class="text-[10px] text-stone-500 font-mono mt-0.5">Pembayaran Lunas</div>
                </div>
            </div>

            <!-- Admin Navigation Tabs -->
            <div class="flex items-center gap-2 border-b border-stone-800 pb-3 overflow-x-auto">
                <button id="admin-tab-btn-orders" onclick="KuroApp.switchAdminTab('orders')" class="admin-tab-btn px-4 py-2.5 rounded-xl text-xs font-serif-luxury font-bold tracking-wider flex items-center gap-2 bg-amber-500/20 text-amber-300 border border-amber-500/40 shrink-0 transition-all">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span>MANAJEMEN PESANAN & PENGIRIMAN</span>
                    <span id="badge-admin-orders-count" class="bg-amber-400/20 text-amber-300 px-2 py-0.5 rounded-full text-[10px] font-mono">0</span>
                </button>
                <button id="admin-tab-btn-products" onclick="KuroApp.switchAdminTab('products')" class="admin-tab-btn px-4 py-2.5 rounded-xl text-xs font-serif-luxury font-bold tracking-wider flex items-center gap-2 text-stone-400 hover:text-white border border-transparent shrink-0 transition-all">
                    <i data-lucide="flask-conical" class="w-4 h-4"></i>
                    <span>CRUD KATALOG & INVENTARIS PRODUK</span>
                    <span id="badge-admin-products-count" class="bg-stone-800 text-stone-400 px-2 py-0.5 rounded-full text-[10px] font-mono">0</span>
                </button>
                <button id="admin-tab-btn-whitelist" onclick="KuroApp.switchAdminTab('whitelist')" class="admin-tab-btn px-4 py-2.5 rounded-xl text-xs font-serif-luxury font-bold tracking-wider flex items-center gap-2 text-stone-400 hover:text-white border border-transparent shrink-0 transition-all">
                    <i data-lucide="award" class="w-4 h-4"></i>
                    <span>KURASI WHITELIST</span>
                    <span id="badge-admin-whitelist-count" class="bg-stone-800 text-stone-400 px-2 py-0.5 rounded-full text-[10px] font-mono">0</span>
                </button>
                <button id="admin-tab-btn-invites" onclick="KuroApp.switchAdminTab('invites')" class="admin-tab-btn px-4 py-2.5 rounded-xl text-xs font-serif-luxury font-bold tracking-wider flex items-center gap-2 text-stone-400 hover:text-white border border-transparent shrink-0 transition-all">
                    <i data-lucide="key" class="w-4 h-4"></i>
                    <span>KODE UNDANGAN VIP</span>
                </button>
            </div>

            <!-- ==============================================
                 TAB PANE 1: ORDERS & FULFILLMENT MANAGEMENT
                 ============================================== -->
            <div id="admin-tab-pane-orders" class="space-y-6">
                <!-- Orders Filter & Search Toolbar -->
                <div class="glass-kuro p-4 rounded-2xl border border-stone-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="text-xs font-mono text-stone-400 uppercase">Filter:</span>
                        <!-- Filter Payment -->
                        <select id="admin-filter-payment" onchange="KuroApp.applyOrdersFilter()" class="bg-stone-950 border border-stone-800 text-xs text-white rounded-lg px-3 py-1.5 outline-none focus:border-amber-400">
                            <option value="">Semua Status Bayar</option>
                            <option value="checkout_review">Belum Bayar (Tahap Review)</option>
                            <option value="pending_verification">Menunggu Verifikasi</option>
                            <option value="confirmed">Lunas (Terkonfirmasi)</option>
                            <option value="completed">Transaksi Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                        <!-- Filter Fulfillment -->
                        <select id="admin-filter-fulfillment" onchange="KuroApp.applyOrdersFilter()" class="bg-stone-950 border border-stone-800 text-xs text-white rounded-lg px-3 py-1.5 outline-none focus:border-amber-400">
                            <option value="">Semua Status Pengiriman</option>
                            <option value="menunggu">Menunggu Konfirmasi</option>
                            <option value="dikemas">Sedang Dikemas (Packaging)</option>
                            <option value="dikirim">Sedang Dalam Pengiriman</option>
                            <option value="selesai">Barang Sudah Diterima</option>
                            <option value="dibatalkan">Pengiriman Dibatalkan</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-80">
                        <div class="relative flex-1">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-stone-500 absolute left-3 top-2.5"></i>
                            <input type="text" id="admin-orders-search" oninput="KuroApp.debounceOrdersSearch()" placeholder="Cari No. Order / Invoice / Nama..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg pl-9 pr-3 py-1.5 text-xs text-white outline-none">
                        </div>
                        <button onclick="KuroApp.loadAdminOrders()" class="glass-kuro px-3 py-1.5 rounded-lg text-xs text-amber-300 border border-stone-800 hover:border-amber-400">
                            Cari
                        </button>
                    </div>
                </div>

                <!-- Orders List Container -->
                <div id="admin-orders-list" class="space-y-4">
                    <!-- Populated dynamically via KuroApp.loadAdminOrders() -->
                </div>
            </div>

            <!-- ==============================================
                 TAB PANE 2: PRODUCTS CRUD & INVENTORY
                 ============================================== -->
            <div id="admin-tab-pane-products" class="hidden space-y-6">
                <!-- Products Action Toolbar -->
                <div class="glass-kuro p-4 rounded-2xl border border-stone-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-serif-luxury text-base font-bold text-white">
                            Inventaris Koleksi Kuro Tokyo
                        </h3>
                        <p class="text-xs text-stone-400 font-light">
                            Kelola mahakarya Bespoke 1-of-1, edisi terbatas Series 24, stok unit, harga, serta deskripsi olfaktori.
                        </p>
                    </div>
                    <button onclick="KuroApp.openProductModal(0)" class="btn-gold px-5 py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-lg shrink-0">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>+ TAMBAH PRODUK BARU</span>
                    </button>
                </div>

                <!-- Products Table / Grid Container -->
                <div id="admin-products-list" class="space-y-4">
                    <!-- Populated dynamically via KuroApp.loadAdminProducts() -->
                </div>
            </div>

            <!-- ==============================================
                 TAB PANE 3: WHITELIST APPLICANTS QUEUE
                 ============================================== -->
            <div id="admin-tab-pane-whitelist" class="hidden space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-serif-luxury text-lg font-bold text-amber-200">
                            Antrean Kurasi Permohonan Akses (Whitelist Applicants)
                        </h3>
                        <p class="text-xs text-stone-400 font-light">Tinjau permohonan keanggotaan privat untuk membuka hak istimewa akuisisi edisi 1-of-1.</p>
                    </div>
                    <span class="text-xs text-stone-500 font-mono">Ditinjau oleh Master Kuro</span>
                </div>

                <div id="admin-applicants-table" class="space-y-3">
                    <!-- Populated dynamically via KuroApp.loadAdminApplicants() -->
                </div>
            </div>

            <!-- ==============================================
                 TAB PANE 4: VIP INVITE CODES GENERATOR
                 ============================================== -->
            <div id="admin-tab-pane-invites" class="hidden space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-5 glass-kuro p-6 rounded-2xl border border-amber-500/30 space-y-4">
                        <h3 class="font-serif-luxury text-base font-bold text-amber-300">
                            Terbitkan Kode Undangan VIP
                        </h3>
                        <p class="text-xs text-stone-400 font-light">
                            Kode undangan memberikan akses instan status keanggotaan VIP tanpa melalui antrean kurasi manual.
                        </p>

                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] font-mono text-stone-400 uppercase">Keterangan / Penerima Undangan</label>
                                <input type="text" id="new-invite-desc" placeholder="Contoh: Ambassador Switzerland VIP" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                            </div>
                            <div>
                                <label class="text-[10px] font-mono text-stone-400 uppercase">Batas Pemakaian (Kuota)</label>
                                <input type="number" id="new-invite-quota" value="1" min="1" max="50" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                            </div>
                            <button onclick="KuroApp.generateNewInviteCode()" class="btn-gold w-full py-2.5 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 shadow-lg">
                                <i data-lucide="key" class="w-4 h-4"></i>
                                <span>GENERATE KODE VIP</span>
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-7 glass-kuro p-6 rounded-2xl border border-stone-800 space-y-3">
                        <div class="flex items-center justify-between border-b border-stone-800 pb-2">
                            <span class="text-xs font-mono text-amber-400 uppercase font-bold tracking-widest">DAFTAR KODE UNDANGAN AKTIF</span>
                            <span class="text-[10px] text-stone-500 font-mono">Realtime Ledger</span>
                        </div>
                        <div id="admin-invite-codes-list" class="space-y-2 max-h-80 overflow-y-auto pr-1">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================
         VERIFIKASI SERTIFIKAT KEASLIAN (COA PUBLIC VERIFIER)
         =================================================== -->
    <section id="coa-lookup" class="py-20 border-b border-stone-900 bg-gradient-to-b from-stone-950 to-[#070708]">
        <div class="max-w-4xl mx-auto px-4 text-center space-y-6">
            <div class="inline-flex items-center gap-2 border border-amber-500/20 bg-black/60 px-3.5 py-1 rounded-full text-xs font-mono text-amber-400">
                <i data-lucide="award" class="w-3.5 h-3.5"></i>
                <span>CRYPTOGRAPHIC VERIFICATION LEDGER</span>
            </div>

            <div class="space-y-2">
                <h2 class="font-serif-luxury text-3xl font-bold text-white">
                    VERIFIKASI KEASLIAN KARYA EDISI 1-OF-1
                </h2>
                <p class="text-xs text-stone-400 max-w-lg mx-auto font-light leading-relaxed">
                    Setiap flacon Kuro dilengkapi sertifikat fisik berstempel segel emas dan hash kriptografis unik yang membuktikan kepemilikan tunggal Anda di seluruh dunia.
                </p>
            </div>

            <div class="glass-kuro p-4 rounded-2xl border border-amber-500/30 max-w-xl mx-auto flex items-center gap-2">
                <input type="text" id="verify-coa-input" placeholder="Masukkan Nomor Seri COA (Contoh: COA-KURO-001-2026-X94K)" class="bg-transparent flex-1 px-3 py-2 text-xs text-white placeholder-stone-600 outline-none font-mono">
                <button onclick="verifyCertificateInput()" class="btn-gold px-5 py-2.5 rounded-xl text-xs font-bold shrink-0">
                    VERIFIKASI
                </button>
            </div>

            <div id="coa-lookup-result" class="max-w-xl mx-auto hidden">
                <!-- Result dynamically rendered -->
            </div>
        </div>
    </section>
    </main>

    <!-- ===================================================
         FOOTER
         =================================================== -->
    <footer class="py-16 bg-black border-t border-stone-900 text-xs text-stone-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded bg-stone-900 border border-stone-800 flex items-center justify-center font-kanji text-amber-400 text-lg">
                    黒
                </div>
                <div>
                    <div class="font-serif-luxury text-stone-300 font-bold tracking-widest">KURO ATELIER TOKYO</div>
                    <div class="text-[10px] font-mono text-stone-600">Imperial Bespoke Parfumerie • Ginza, Tokyo</div>
                </div>
            </div>

            <div class="text-center md:text-right font-light text-[11px] space-y-1">
                <p>© 2026 Kuro Exclusive Mini E-Commerce. All rights reserved.</p>
                <p class="text-stone-600 font-mono">Strict Private Access & Non-Replicable 1-of-1 Formulations</p>
            </div>
        </div>
    </footer>

    <!-- ===================================================
         MODAL 1: WHITELIST & INVITE CODE
         =================================================== -->
    <div id="whitelist-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="glass-kuro border border-amber-500/40 rounded-2xl max-w-lg w-full p-6 md:p-8 space-y-6 relative shadow-2xl">
            <button onclick="KuroApp.closeWhitelistModal()" class="absolute top-5 right-5 text-stone-400 hover:text-white transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <!-- Modal Header -->
            <div class="text-center space-y-1">
                <div class="font-kanji text-amber-400 text-2xl font-bold">黒</div>
                <h3 class="font-serif-luxury text-xl font-bold text-white tracking-wider">AKSES PRIVAT KURO ATELIER</h3>
                <p class="text-xs text-stone-400 font-light">Pintu masuk eksklusif ke Ruang Pamer Koleksi 1-of-1</p>
            </div>

            <!-- Tabs -->
            <div class="flex border-b border-stone-800 text-xs font-serif-luxury tracking-wider">
                <button id="tab-invite-btn" onclick="KuroApp.switchWhitelistTab('invite')" class="flex-1 py-2.5 text-center text-amber-300 border-b-2 border-amber-400 font-semibold transition-colors">
                    KODE UNDANGAN VIP
                </button>
                <button id="tab-apply-btn" onclick="KuroApp.switchWhitelistTab('apply')" class="flex-1 py-2.5 text-center text-stone-400 hover:text-white transition-colors">
                    PENGAJUAN KURASI IDENTITAS
                </button>
            </div>

            <!-- View 1: Invite Code -->
            <div id="view-invite-code" class="space-y-4">
                <p class="text-xs text-stone-400 font-light leading-relaxed">
                    Jika Anda telah menerima surat undangan resmi atau kode VIP dari Kuro Patron, masukkan kode 12 digit Anda di bawah ini:
                </p>

                <div class="space-y-2">
                    <input type="text" id="invite-code-input" placeholder="Contoh: KURO-VIP-2026 atau SHIBUI-CHAMBER" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-xl px-4 py-3 text-xs text-amber-200 uppercase font-mono tracking-widest outline-none text-center">
                    <div class="text-[10px] text-stone-500 text-center font-mono">Kode Uji Coba Tersedia: KURO-VIP-2026, SHIBUI-CHAMBER</div>
                </div>

                <button onclick="KuroApp.submitInviteCode()" class="btn-gold w-full py-3 rounded-xl text-xs font-bold">
                    VALIDASI KODE & BUKA AKSES
                </button>
            </div>

            <!-- View 2: Whitelist Apply Form -->
            <div id="view-apply-form" class="space-y-4 hidden">
                <p class="text-xs text-stone-400 font-light leading-relaxed">
                    Setiap calon kolektor akan dikurasi secara personal oleh tim Kuro berdasarkan apresiasi seni dan integritas profil:
                </p>

                <form id="whitelist-apply-form" onsubmit="KuroApp.submitWhitelistApplication(event)" class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Nama Lengkap</label>
                        <input type="text" name="full_name" required placeholder="Nama Anda" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Alamat Email Resmi</label>
                        <input type="email" name="email" required placeholder="email@perusahaan.com" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Organisasi / Perusahaan & Posisi Jabatan</label>
                        <input type="text" name="organization_title" required placeholder="Contoh: Managing Partner, Sovereign Asset Management" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Nomor Kontak Privat (Telepon/WhatsApp)</label>
                        <input type="tel" name="phone" placeholder="+62 812-xxxx-xxxx" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Pernyataan Minat Koleksi (Alasan Ingin Memiliki Kuro)</label>
                        <textarea name="statement_of_intent" rows="3" required placeholder="Ceritakan ketertarikan Anda pada haute perfumery 1-of-1..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none resize-none"></textarea>
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Preferensi Profil Aroma (Opsional)</label>
                        <input type="text" name="olfactory_preference" placeholder="Contoh: Dark Oud, Smoked Wood, Orris, Rose Otto" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                    </div>

                    <button type="submit" class="btn-gold w-full py-3 rounded-xl text-xs font-bold mt-2">
                        KIRIM PERMOHONAN KURASI
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================================================
         MODAL 2: DETAIL PRODUK & PIRAMIDA OLFAKTORI
         =================================================== -->
    <div id="product-detail-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="glass-kuro border border-amber-500/40 rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-6 md:p-8 relative shadow-2xl">
            <button onclick="KuroApp.closeDetailModal()" class="absolute top-5 right-5 text-stone-400 hover:text-white transition-colors z-10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <div id="product-detail-body">
                <!-- Dynamically populated via KuroApp.openDetailModal() -->
            </div>
        </div>
    </div>

    <!-- ===================================================
         MODAL 3: DIRECT CONCIERGE LIVE SUITE
         =================================================== -->
    <div id="concierge-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="glass-kuro border border-amber-500/40 rounded-2xl max-w-2xl w-full h-[85vh] flex flex-col relative shadow-2xl overflow-hidden">
            <!-- Concierge Header -->
            <div class="p-4 md:p-5 border-b border-stone-800 flex items-center justify-between bg-stone-950/70">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-amber-500/20 border border-amber-400/60 flex items-center justify-center font-kanji text-amber-300 text-lg font-bold">
                            黒
                        </div>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-black absolute bottom-0 right-0"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-serif-luxury text-sm font-bold text-white">Kuro (黒) Master Perfumer</h3>
                            <span class="bg-amber-950 text-amber-300 border border-amber-500/40 text-[9px] font-mono px-2 py-0.5 rounded-full">PRIVATE CHANNEL</span>
                        </div>
                        <p class="text-[11px] text-stone-400">Tokyo Atelier Imperial Chamber • Enkripsi Langsung</p>
                    </div>
                </div>
                <button onclick="KuroApp.closeConcierge()" class="text-stone-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Messages Stream Area -->
            <div id="concierge-messages-stream" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 bg-gradient-to-b from-[#070708] via-stone-950/60 to-[#070708]">
                <!-- Messages populated via KuroApp.loadConciergeMessages() -->
            </div>

            <!-- Quick Inquiry Templates -->
            <div class="p-2 border-t border-stone-800 bg-stone-950/50">
                <div class="text-[10px] font-mono text-stone-500 mb-1 px-1">TEMPLATE KONSULTASI EKSEKUTIF:</div>
                <div id="concierge-quick-templates" class="grid grid-cols-1 sm:grid-cols-3 gap-1.5">
                    <!-- Populated via loadConciergeTemplates() -->
                </div>
            </div>

            <!-- Message Input Footer -->
            <div class="p-4 border-t border-stone-800 bg-stone-950 flex items-center gap-2">
                <input type="text" id="concierge-message-input" onkeydown="if(event.key==='Enter') KuroApp.sendConciergeMessage()" placeholder="Ketik pesan konsultasi privat Anda untuk Kuro..." class="flex-1 bg-stone-900 border border-stone-800 focus:border-amber-400 rounded-xl px-4 py-2.5 text-xs text-white placeholder-stone-500 outline-none">
                <button onclick="KuroApp.sendConciergeMessage()" class="btn-gold px-5 py-2.5 rounded-xl text-xs flex items-center gap-1.5 font-bold">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>KIRIM</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================
         MODAL 4: HIGH-VALUE CHECKOUT & ACQUISITION
         =================================================== -->
    <div id="checkout-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="glass-kuro border border-amber-500/40 rounded-2xl max-w-4xl w-full max-h-[92vh] overflow-y-auto p-6 md:p-8 relative shadow-2xl">
            <button onclick="KuroApp.closeCheckout()" class="absolute top-5 right-5 text-stone-400 hover:text-white transition-colors z-10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <div id="checkout-modal-content">
                <!-- Dynamically populated via KuroApp.openCheckout() -->
            </div>
        </div>
    </div>

    <!-- ===================================================
         MODAL 5: CERTIFICATE OF AUTHENTICITY (COA)
         =================================================== -->
    <div id="coa-modal" class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="max-w-2xl w-full max-h-[95vh] overflow-y-auto relative">
            <button onclick="KuroApp.closeCertificateModal()" class="absolute top-4 right-4 text-stone-400 hover:text-white transition-colors z-10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <div id="coa-modal-body">
                <!-- Dynamically populated via showCertificateModal() -->
            </div>
        </div>
    </div>

    <!-- ===================================================
         DRAWER: SHOPPING CART (SERIES 24)
         =================================================== -->
    <div id="cart-drawer" class="fixed inset-0 z-50 overflow-hidden hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/75 backdrop-blur-sm transition-opacity" onclick="KuroApp.closeCartDrawer()"></div>
        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-[#0c0c0e] border-l border-amber-500/30 flex flex-col shadow-2xl">
                <!-- Cart Header -->
                <div class="p-6 border-b border-stone-800 flex items-center justify-between bg-stone-950/80">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-amber-500/10 border border-amber-400/40 flex items-center justify-center text-amber-400">
                            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-serif-luxury text-base font-bold text-white tracking-wide">KERANJANG BELANJA</h3>
                            <p class="text-[10px] text-amber-400/80 font-mono tracking-wider">KURO SERIES 24 (ALOKASI TERBATAS)</p>
                        </div>
                    </div>
                    <button onclick="KuroApp.closeCartDrawer()" class="text-stone-400 hover:text-white p-1 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Cart Items List -->
                <div id="cart-drawer-items" class="flex-1 overflow-y-auto p-6 space-y-4">
                    <!-- Populated via KuroApp.renderCartDrawer() -->
                </div>

                <!-- Cart Footer -->
                <div class="p-6 border-t border-stone-800 bg-stone-950/90 space-y-4">
                    <div class="flex items-center justify-between text-xs text-stone-400">
                        <span>Subtotal Alokasi:</span>
                        <span id="cart-drawer-subtotal" class="font-serif-luxury text-lg font-bold text-gold-gradient">Rp 0</span>
                    </div>
                    <p class="text-[11px] text-stone-500 leading-relaxed">
                        Catatan: Langkah selanjutnya adalah tinjauan checkout untuk mencatat pesanan alokasi Anda tanpa perlu memilih metode pembayaran terlebih dahulu.
                    </p>
                    <button id="cart-drawer-checkout-btn" onclick="KuroApp.openCartCheckout()" class="btn-gold w-full py-3.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-lg">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>LANJUT KE CHECKOUT REVIEW</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================
         MODAL: SERIES 24 CHECKOUT REVIEW
         (Stops strictly at Checkout Stage, No Payment Method Selection)
         =================================================== -->
    <div id="cart-checkout-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="glass-kuro border border-amber-500/40 rounded-2xl max-w-2xl w-full max-h-[92vh] overflow-y-auto p-6 md:p-8 relative shadow-2xl space-y-6">
            <button onclick="KuroApp.closeCartCheckout()" class="absolute top-5 right-5 text-stone-400 hover:text-white transition-colors z-10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <!-- Header -->
            <div class="border-b border-stone-800 pb-4">
                <div class="flex items-center gap-2 text-amber-400 text-[11px] font-mono tracking-widest uppercase">
                    <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                    <span>TAHAP TINJAUAN CHECKOUT • SERIES 24</span>
                </div>
                <h3 class="font-serif-luxury text-2xl font-bold text-white mt-1">Konfirmasi & Tinjau Pesanan</h3>
                <p class="text-xs text-stone-400 mt-1 leading-relaxed">
                    Lengkapi alamat pengiriman untuk mengunci alokasi botol Series 24 Anda. Transaksi ini akan berhenti pada tahap checkout (tanpa pemilihan metode pembayaran terlebih dahulu).
                </p>
            </div>

            <!-- Items Review Box -->
            <div class="bg-black/60 border border-stone-800 rounded-xl p-4 space-y-3">
                <div class="text-[10px] font-mono text-stone-400 uppercase tracking-wider">RINGKASAN ITEM DALAM KERANJANG:</div>
                <div id="checkout-review-items" class="space-y-1">
                    <!-- Populated dynamically via openCartCheckout() -->
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-stone-800 text-xs font-semibold">
                    <span class="text-stone-300">Total Nilai Alokasi:</span>
                    <span id="checkout-review-subtotal" class="font-serif-luxury text-base text-gold-gradient font-bold">Rp 0</span>
                </div>
            </div>

            <!-- Checkout Form -->
            <form id="form-cart-checkout" onsubmit="KuroApp.submitCartCheckout(event)" class="space-y-4 text-left">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Nama Lengkap Penerima *</label>
                        <input type="text" id="checkout-review-name" required placeholder="Nama penerima paket..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Nomor Telepon / WhatsApp *</label>
                        <input type="tel" id="checkout-review-phone" required placeholder="+62 812-xxxx-xxxx" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Alamat Lengkap Pengiriman *</label>
                    <textarea id="checkout-review-address" rows="3" required placeholder="Jl. Sudirman No..., Kelurahan..., Kota..., Kode Pos..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none resize-none"></textarea>
                </div>

                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Catatan Khusus Pesanan (Opsional)</label>
                    <input type="text" id="checkout-review-notes" placeholder="Contoh: Titipkan di lobby gedung, sertakan kartu ucapan..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>

                <!-- Info Notice on Checkout Stage Restriction -->
                <div class="bg-amber-950/20 border border-amber-500/30 rounded-xl p-3.5 flex items-start gap-2.5 text-xs text-amber-200/90 leading-relaxed">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                    <div>
                        <strong>Protokol Kuro:</strong> Mengirimkan checkout ini akan mengunci stok alokasi Series 24 dan mencatat pesanan Anda dengan status <em>Review Checkout</em>. Sesuai ketentuan, <strong>tahap metode pembayaran tidak akan dimunculkan</strong> sekarang.
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div>
                        <span class="text-[10px] text-stone-500 uppercase font-mono block">TOTAL AKHIR</span>
                        <span id="checkout-review-total" class="font-serif-luxury text-xl font-bold text-gold-gradient">Rp 0</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="KuroApp.closeCartCheckout()" class="btn-outline-gold px-4 py-2.5 rounded-xl text-xs">
                            Batal
                        </button>
                        <button type="submit" class="btn-gold px-6 py-2.5 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-lg">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                            <span>SELESAIKAN CHECKOUT</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================
         MODAL: OFFICIAL INVOICE & ORDER SUMMARY (SERIES 24)
         =================================================== -->
    <div id="order-summary-modal" class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-6 hidden overflow-y-auto">
        <div id="order-summary-content" class="w-full max-w-3xl my-auto">
            <!-- Dynamically populated via KuroApp.showCheckoutSummary() -->
        </div>
    </div>

    <!-- ===================================================
         MODAL: ADMIN PRODUCT CRUD (TAMBAH / EDIT KARYA PARFUM)
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
                            <option value="available">Tersedia (Available)</option>
                            <option value="reserved">Dipesan (Reserved)</option>
                            <option value="acquired">Terakuisisi / Vault (Acquired)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Konsentrasi Ekstrak</label>
                    <input type="text" id="admin-prod-concentration" value="Eau de Parfum Intense (26% Concentration)" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>

                <!-- Visual Flacon Preset Selector -->
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase block mb-1.5">Pilih Aset Foto Flacon Mewah (Atau Ketik URL Sendiri)</label>
                    <div class="grid grid-cols-4 sm:grid-cols-7 gap-2 mb-2">
                        <button type="button" onclick="KuroApp.selectImagePreset('assets/images/kuro_kyara_oud.jpg')" class="group relative rounded-lg overflow-hidden border border-stone-800 hover:border-amber-400 focus:border-amber-400 transition-all aspect-square">
                            <img src="assets/images/kuro_kyara_oud.jpg" class="w-full h-full object-cover">
                            <span class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center text-[9px] text-amber-300 font-mono">Kyara</span>
                        </button>
                        <button type="button" onclick="KuroApp.selectImagePreset('assets/images/kuro_shogun.jpg')" class="group relative rounded-lg overflow-hidden border border-stone-800 hover:border-amber-400 focus:border-amber-400 transition-all aspect-square">
                            <img src="assets/images/kuro_shogun.jpg" class="w-full h-full object-cover">
                            <span class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center text-[9px] text-amber-300 font-mono">Shogun</span>
                        </button>
                        <button type="button" onclick="KuroApp.selectImagePreset('assets/images/kuro_series24_noir.jpg')" class="group relative rounded-lg overflow-hidden border border-stone-800 hover:border-amber-400 focus:border-amber-400 transition-all aspect-square">
                            <img src="assets/images/kuro_series24_noir.jpg" class="w-full h-full object-cover">
                            <span class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center text-[9px] text-amber-300 font-mono">Noir 24</span>
                        </button>
                        <button type="button" onclick="KuroApp.selectImagePreset('assets/images/kuro_series24_amber.jpg')" class="group relative rounded-lg overflow-hidden border border-stone-800 hover:border-amber-400 focus:border-amber-400 transition-all aspect-square">
                            <img src="assets/images/kuro_series24_amber.jpg" class="w-full h-full object-cover">
                            <span class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center text-[9px] text-amber-300 font-mono">Amber 24</span>
                        </button>
                        <button type="button" onclick="KuroApp.selectImagePreset('assets/images/kuro_sumi.jpg')" class="group relative rounded-lg overflow-hidden border border-stone-800 hover:border-amber-400 focus:border-amber-400 transition-all aspect-square">
                            <img src="assets/images/kuro_sumi.jpg" class="w-full h-full object-cover">
                            <span class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center text-[9px] text-amber-300 font-mono">Sumi</span>
                        </button>
                        <button type="button" onclick="KuroApp.selectImagePreset('assets/images/kuro_kintsugi.jpg')" class="group relative rounded-lg overflow-hidden border border-stone-800 hover:border-amber-400 focus:border-amber-400 transition-all aspect-square">
                            <img src="assets/images/kuro_kintsugi.jpg" class="w-full h-full object-cover">
                            <span class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center text-[9px] text-amber-300 font-mono">Kintsugi</span>
                        </button>
                        <button type="button" onclick="KuroApp.selectImagePreset('assets/images/kuro_yugen.jpg')" class="group relative rounded-lg overflow-hidden border border-stone-800 hover:border-amber-400 focus:border-amber-400 transition-all aspect-square">
                            <img src="assets/images/kuro_yugen.jpg" class="w-full h-full object-cover">
                            <span class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center text-[9px] text-amber-300 font-mono">Yūgen</span>
                        </button>
                    </div>
                    <input type="text" id="admin-prod-image" required value="assets/images/kuro_series24_noir.jpg" placeholder="assets/images/... atau https://..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none font-mono">
                </div>

                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Deskripsi Naratif Produk *</label>
                    <textarea id="admin-prod-desc" rows="2" required placeholder="Deskripsi karakter aroma, latar belakang kreasi, dan komposisi utama..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Filosofi Wabi-Sabi / Shibui</label>
                        <textarea id="admin-prod-philosophy" rows="2" placeholder="Konsep estetika dan filosofi di balik formula parfum..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none resize-none"></textarea>
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Keahlian Botol (Flacon Craftsmanship)</label>
                        <textarea id="admin-prod-craftsmanship" rows="2" placeholder="Detail material kaca, tutup kuningan, atau ukiran kanji..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none resize-none"></textarea>
                    </div>
                </div>

                <!-- Notes Pyramid -->
                <div class="border-t border-stone-800 pt-3 space-y-3">
                    <span class="text-[10px] font-mono text-amber-400 uppercase tracking-widest block font-bold">PIRAMIDA OLFAKTORI (PISAHKAN DENGAN KOMA)</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="text-[10px] font-mono text-stone-400 uppercase">Top Notes (Awal)</label>
                            <input type="text" id="admin-prod-top-notes" placeholder="Bergamot, Pink Pepper" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono text-stone-400 uppercase">Heart Notes (Inti)</label>
                            <input type="text" id="admin-prod-heart-notes" placeholder="Black Incense, Damask Rose" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono text-stone-400 uppercase">Base Notes (Pondasi)</label>
                            <input type="text" id="admin-prod-base-notes" placeholder="Smoked Vetiver, Amber Noir" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-800">
                    <button type="button" onclick="KuroApp.closeProductModal()" class="btn-outline-gold px-4 py-2.5 rounded-xl text-xs">
                        Batal
                    </button>
                    <button type="submit" class="btn-gold px-6 py-2.5 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-lg">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>SIMPAN PRODUK</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================
         MODAL: ADMIN ORDER STATUS & FULFILLMENT TRACKER
         =================================================== -->
    <div id="admin-order-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="glass-kuro border border-amber-500/40 rounded-2xl max-w-xl w-full max-h-[92vh] overflow-y-auto p-6 md:p-8 relative shadow-2xl space-y-6">
            <button onclick="KuroApp.closeOrderModal()" class="absolute top-5 right-5 text-stone-400 hover:text-white transition-colors z-10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <div class="space-y-1 border-b border-amber-500/30 pb-4">
                <div class="flex items-center gap-2 text-amber-400 text-[10px] font-mono tracking-widest uppercase">
                    <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                    <span>LOGISTIK & KONFIRMASI PEMBAYARAN</span>
                </div>
                <h3 class="font-serif-luxury text-xl font-bold text-white">
                    PERBARUI STATUS PESANAN
                </h3>
                <div id="admin-order-modal-ref" class="text-xs text-amber-300/80 font-mono">
                    Nomor Pesanan: -
                </div>
            </div>

            <!-- Order Brief Overview -->
            <div id="admin-order-modal-summary" class="bg-black/40 border border-stone-800 p-3.5 rounded-xl space-y-1 text-xs">
                <!-- Populated dynamically -->
            </div>

            <form id="admin-order-form" onsubmit="KuroApp.submitOrderModal(event)" class="space-y-4">
                <input type="hidden" id="admin-order-id" value="0">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Status Pembayaran *</label>
                        <select id="admin-order-payment-status" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                            <option value="checkout_review">Belum Bayar (Tahap Checkout Review)</option>
                            <option value="pending_verification">Menunggu Verifikasi Bank</option>
                            <option value="confirmed">Pembayaran Lunas (Terkonfirmasi)</option>
                            <option value="completed">Transaksi Selesai & Lunas</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Tahap Pengiriman / Kemas *</label>
                        <select id="admin-order-fulfillment-status" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                            <option value="menunggu">Menunggu Konfirmasi</option>
                            <option value="dikemas">Sedang Dikemas (Packaging)</option>
                            <option value="dikirim">Sedang Dalam Pengiriman (Shipping)</option>
                            <option value="selesai">Barang Sudah Diterima (Delivered)</option>
                            <option value="dibatalkan">Pengiriman Dibatalkan</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Ekspedisi / Nama Kurir</label>
                        <input type="text" id="admin-order-courier" placeholder="Contoh: JNE Diplomatic Express / Paxel" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-stone-400 uppercase">Nomor Resi / AWB</label>
                        <input type="text" id="admin-order-tracking" placeholder="Contoh: JNE-KURO-9988112" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none font-mono">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Catatan Pemrosesan Pengiriman</label>
                    <textarea id="admin-order-notes" rows="2" placeholder="Contoh: Paket telah diserahkan ke agen ekspedisi SCBD, perkiraan tiba esok hari..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none resize-none"></textarea>
                </div>

                <div class="p-3 bg-amber-950/20 border border-amber-500/20 rounded-xl text-[11px] text-amber-200/80 flex items-start gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                    <span>Setiap pembaruan status pengiriman dan pembayaran akan dikirimkan secara otomatis sebagai notifikasi personal ke akun pesan Concierge pembeli.</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-800">
                    <button type="button" onclick="KuroApp.closeOrderModal()" class="btn-outline-gold px-4 py-2.5 rounded-xl text-xs">
                        Batal
                    </button>
                    <button type="submit" class="btn-gold px-6 py-2.5 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-lg">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>SIMPAN PEMBARUAN PESANAN</span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- ===================================================
         MODAL: AUTHENTICATION (LOGIN & DAFTAR AKUN)
         Syarat utama pembelian Kuro Series 24
         =================================================== -->
    <div id="auth-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="glass-kuro border border-amber-500/40 rounded-2xl max-w-md w-full p-6 md:p-8 relative shadow-2xl space-y-6">
            <button onclick="KuroApp.closeLoginModal()" class="absolute top-5 right-5 text-stone-400 hover:text-white transition-colors z-10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <!-- Brand Header -->
            <div class="text-center space-y-1">
                <div class="w-10 h-10 mx-auto rounded-lg bg-black border border-gold-subtle flex items-center justify-center text-amber-400 font-kanji text-xl font-bold">
                    黒
                </div>
                <h3 class="font-serif-luxury text-xl font-bold text-white tracking-widest pt-2">KURO ATELIER</h3>
                <p class="text-[10px] text-amber-400/80 font-mono tracking-wider uppercase">AKSES ANGGOTA • PEMBELIAN SERIES 24</p>
            </div>

            <!-- Dynamic Alert Message -->
            <div id="auth-modal-alert" class="hidden p-3 bg-amber-950/40 border border-amber-500/30 rounded-xl text-xs text-amber-200 text-center leading-relaxed">
            </div>

            <!-- Tab Switcher -->
            <div class="flex border-b border-stone-800 text-xs font-serif-luxury tracking-wider">
                <button id="tab-auth-login" onclick="KuroApp.switchAuthTab('login')" class="flex-1 py-2 text-center border-b-2 border-amber-400 text-amber-300 font-bold transition-all">
                    MASUK (LOGIN)
                </button>
                <button id="tab-auth-register" onclick="KuroApp.switchAuthTab('register')" class="flex-1 py-2 text-center text-stone-400 hover:text-white transition-all">
                    BUAT AKUN BARU
                </button>
            </div>

            <!-- Form: Login -->
            <form id="form-auth-login" onsubmit="KuroApp.submitLogin(event)" class="space-y-4 text-left">
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Alamat Email</label>
                    <input type="email" name="email" required placeholder="email@domain.com" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Kata Sandi</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>

                <div class="text-[11px] text-stone-400 bg-black/40 border border-stone-800 rounded-lg p-2.5 space-y-1">
                    <div class="font-mono text-amber-400 font-semibold text-[10px] uppercase">AKUN PENGUJIAN SIAP PAKAI (Password: password123):</div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span>👑 Terverifikasi VIP (Tanaka):</span>
                        <span class="text-amber-300 font-mono">tanaka@executives.co.jp</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span>🛒 Akun Gratis (Budi Pratama):</span>
                        <span class="text-sky-300 font-mono">gratis@kuro.com</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span>⚜️ Kuro Master (Admin):</span>
                        <span class="text-amber-200 font-mono">kuro@atelier.com</span>
                    </div>
                </div>

                <button type="submit" class="btn-gold w-full py-3 rounded-xl text-xs font-bold">
                    MASUK KE AKUN SAYA
                </button>
            </form>

            <!-- Form: Register -->
            <form id="form-auth-register" onsubmit="KuroApp.submitRegister(event)" class="space-y-3 text-left hidden">
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Nama Anda..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Alamat Email</label>
                    <input type="email" name="email" required placeholder="email@domain.com" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Kata Sandi Baru</label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>

                <button type="submit" class="btn-gold w-full py-3 rounded-xl text-xs font-bold mt-2">
                    DAFTAR & LANGSUNG BELANJA
                </button>
            </form>
        </div>
    </div>

    <!-- ===================================================
         TOAST NOTIFICATION COMPONENT
         =================================================== -->
    <div id="kuro-toast" class="fixed bottom-6 right-6 z-50 glass-kuro border border-amber-400/80 bg-stone-950/95 text-amber-200 px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 opacity-0 translate-y-4 pointer-events-none transition-all duration-300">
        <i id="kuro-toast-icon" data-lucide="info" class="w-4 h-4 text-amber-400 shrink-0"></i>
        <span id="kuro-toast-text" class="text-xs font-medium">Notifikasi</span>
    </div>

    <!-- Main Client JS Application -->
    <script src="assets/js/kuro.js"></script>

    <script>
        // COA Lookup Form Function
        async function verifyCertificateInput() {
            const input = document.getElementById('verify-coa-input');
            const serial = input ? input.value.trim() : '';
            const resultBox = document.getElementById('coa-lookup-result');

            if (!serial) {
                KuroApp.showToast('Masukkan nomor seri atau hash sertifikat COA.', 'error');
                return;
            }

            try {
                const res = await fetch(`api/orders.php?action=verify_coa&serial=${encodeURIComponent(serial)}`);
                const data = await res.json();
                
                resultBox.classList.remove('hidden');
                if (data.success) {
                    KuroApp.playZenChime('bell');
                    const c = data.data.certificate;
                    resultBox.innerHTML = `
                        <div class="glass-kuro p-6 rounded-2xl border border-emerald-500/50 bg-emerald-950/20 text-left space-y-3">
                            <div class="flex items-center gap-2 text-emerald-400 font-serif-luxury font-bold text-sm">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                <span>TERVERIFIKASI 100% ASLI DALAM ARSIP RESMI KURO TOKYO</span>
                            </div>
                            <div class="text-xs text-stone-300 space-y-1">
                                <div>Karya: <strong class="text-white">${c.product_name}</strong> (${c.edition_serial})</div>
                                <div>Pemilik Tunggal Terdaftar: <strong class="text-amber-300">${c.owner_name}</strong> (${c.title_company || 'Sovereign Collector'})</div>
                                <div>Nomor Seri COA: <span class="font-mono text-amber-200">${c.coa_serial}</span></div>
                                <div>Tanggal Registrasi: <span class="font-mono text-stone-400">${c.acquired_at}</span></div>
                                ${c.custom_engraving ? `<div>Ukiran Kustom: <span class="italic text-amber-100 font-editorial">"${c.custom_engraving}"</span></div>` : ''}
                            </div>
                        </div>
                    `;
                } else {
                    resultBox.innerHTML = `
                        <div class="glass-kuro p-4 rounded-xl border border-rose-500/50 bg-rose-950/20 text-rose-300 text-xs text-center">
                            ${data.message}
                        </div>
                    `;
                }
                lucide.createIcons();
            } catch (e) {
                console.error(e);
            }
        }
    </script>
</body>
</html>
