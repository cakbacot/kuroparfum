<?php
// ===================================================
// Kuro Atelier - Beranda Utama (Executive Landing & Teaser)
// ===================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

initSession();
$pageTitle = 'KURO Atelier • Haute Parfumerie 1-of-1 Tokyo';
$currentPage = 'home';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===================================================
     HERO SECTION (PRD: LANDING PAGE EKSKLUSIF & TEASER)
     =================================================== -->
<section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden border-b border-stone-900">
    <!-- Hero Background -->
    <div class="absolute inset-0 bg-cover bg-center opacity-40 mix-blend-screen scale-105 transform animate-pulse-subtle" style="background-image: url('assets/images/kuro_hero_bg.jpg');"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-[#070708] via-[#070708]/75 to-transparent"></div>
    <div class="absolute inset-0 bg-radial-gradient from-transparent via-[#070708]/60 to-[#070708]"></div>

    <!-- Gold Particle Accent -->
    <div class="absolute inset-0 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:32px_32px] opacity-15 pointer-events-none"></div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8 py-20">
        <!-- Badge Tagline -->
        <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full glass-kuro border border-amber-500/40 text-[11px] font-mono tracking-[0.25em] text-amber-300 uppercase shadow-xl animate-fade-in">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-ping"></span>
            <span>GINZA DISTRICT, TOKYO • 1-OF-1 BESPOKE & SERIES 24</span>
        </div>

        <!-- Master Title -->
        <div class="space-y-4">
            <h1 class="font-serif-luxury text-4xl sm:text-6xl lg:text-7xl font-bold tracking-[0.1em] text-white leading-tight">
                KEHENINGAN DALAM<br>
                <span class="text-gold-gradient font-serif-deco">KEMEWAHAN TERTINGGI</span>
            </h1>
            <p class="font-kanji text-amber-400/60 text-lg sm:text-xl tracking-[0.3em]">
                静寂の中に宿る至高の贅沢
            </p>
        </div>

        <!-- Exclusivity Teaser Copy -->
        <p class="text-stone-300 text-xs sm:text-base max-w-2xl mx-auto font-light leading-relaxed tracking-wide">
            Kuro Atelier mempersembahkan formulasi wewangian independen beraroma minyak kayu Kyara kuil suci Nara berusia 300 tahun. Dituangkan ke dalam flacon kristal obsidian hitam ditiup tangan, dilapisi plat emas murni 24K berukirkan kanji otentik.
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <a href="koleksi.php" class="btn-gold px-9 py-4 rounded-xl text-xs font-bold tracking-widest flex items-center justify-center gap-2.5 w-full sm:w-auto shadow-2xl hover:scale-105 transition-all">
                <i data-lucide="eye" class="w-4 h-4"></i>
                <span>MASUK KE RUANG PAMER</span>
            </a>
            <a href="whitelist.php" class="btn-outline-gold px-8 py-4 rounded-xl text-xs font-semibold tracking-widest flex items-center justify-center gap-2.5 w-full sm:w-auto hover:bg-stone-900/60 transition-all">
                <i data-lucide="key" class="w-4 h-4 text-amber-400"></i>
                <span>KURASI ANGGOTA VIP</span>
            </a>
        </div>
    </div>
</section>

<!-- ===================================================
     SECTION 2: HIGHLIGHT KARYA PILIHAN
     =================================================== -->
<section class="py-24 border-b border-stone-900 bg-stone-950/40 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="flex flex-col md:flex-row items-start md:items-end justify-between gap-4">
            <div class="space-y-2">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest">SOROTAN EDISI KHUSUS</span>
                <h2 class="font-serif-luxury text-3xl sm:text-4xl font-bold text-white tracking-wide">
                    DUA TINGKATAN EKSKLUSIVITAS
                </h2>
            </div>
            <a href="koleksi.php" class="text-xs font-serif-luxury text-amber-300 hover:text-amber-200 flex items-center gap-1.5 transition-colors group">
                <span>Lihat Seluruh Katalog Flacon</span>
                <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Card 1: 1-of-1 Bespoke Masterpiece -->
            <div class="glass-kuro border border-amber-500/40 rounded-3xl p-8 space-y-6 relative overflow-hidden group shadow-2xl hover:border-amber-400 transition-all duration-500">
                <div class="aspect-[16/10] rounded-2xl overflow-hidden bg-black border border-stone-800 relative">
                    <img src="assets/images/kuro_kyara_oud.jpg" alt="Kuro Kyara Oud" class="w-full h-full object-cover filter brightness-90 group-hover:scale-105 group-hover:brightness-100 transition-all duration-700">
                    <div class="absolute top-4 left-4 bg-black/80 backdrop-blur-md px-3 py-1 rounded-full border border-amber-500/50 text-[10px] font-mono text-amber-300">
                        1-OF-1 BESPOKE • HANYA 1 BOTOL
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="font-serif-luxury text-xl font-bold text-white">KURO Kyara Oud</h3>
                        <span class="font-kanji text-amber-400 text-lg font-bold">黒沈香</span>
                    </div>
                    <p class="text-xs text-stone-400 font-light leading-relaxed">
                        Mahakarya tunggal dunia dari batang kayu gaharu kuil Nara berpadu ambergris liar Samudra Pasifik. Dilengkapi sertifikat hak kepemilikan mutlak dan brankas status Vaulted permanen.
                    </p>
                </div>
                <div class="pt-4 border-t border-stone-800 flex items-center justify-between">
                    <span class="text-[11px] font-mono text-amber-400/80">AKSES: KURASI VIP SOVEREIGN</span>
                    <a href="koleksi.php" class="btn-outline-gold text-xs px-4 py-2 rounded-xl">Buka Ruang Pamer</a>
                </div>
            </div>

            <!-- Card 2: Series 24 Limited Edition -->
            <div class="glass-kuro border border-stone-800 rounded-3xl p-8 space-y-6 relative overflow-hidden group shadow-2xl hover:border-amber-500/40 transition-all duration-500">
                <div class="aspect-[16/10] rounded-2xl overflow-hidden bg-black border border-stone-800 relative">
                    <img src="assets/images/kuro_sumi.jpg" alt="Series 24 Flacon" class="w-full h-full object-cover filter brightness-90 group-hover:scale-105 group-hover:brightness-100 transition-all duration-700">
                    <div class="absolute top-4 left-4 bg-black/80 backdrop-blur-md px-3 py-1 rounded-full border border-stone-700 text-[10px] font-mono text-stone-300">
                        SERIES 24 • BATAS 24 BOTOL
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="font-serif-luxury text-xl font-bold text-white">KURO Sumi No Hikari</h3>
                        <span class="font-kanji text-amber-400 text-lg font-bold">墨の光</span>
                    </div>
                    <p class="text-xs text-stone-400 font-light leading-relaxed">
                        Tinta kaligrafi kuno Kyoto berpadu dengan asap dupa Hinoki dan vanili gelap Madagaskar. Tersedia bagi seluruh anggota terdaftar dengan kuota alokasi ketat 24 botol.
                    </p>
                </div>
                <div class="pt-4 border-t border-stone-800 flex items-center justify-between">
                    <span class="font-serif-luxury text-sm font-bold text-gold-gradient">Rp 4.850.000</span>
                    <a href="koleksi.php" class="btn-gold text-xs px-4 py-2 rounded-xl font-bold">Tambah Keranjang</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================================================
     SECTION 3: TEASER FILOSOFI SHIBUI & CRAFTSMANSHIP
     =================================================== -->
<section class="py-24 border-b border-stone-900 bg-[#070708] relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-kuro border border-amber-500/30 rounded-3xl p-8 sm:p-14 relative overflow-hidden shadow-2xl">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-7 space-y-6">
                    <span class="text-xs font-mono text-amber-400 uppercase tracking-widest block">FILOSOFI SHIBUI (渋い)</span>
                    <h2 class="font-serif-luxury text-3xl sm:text-4xl font-bold text-white leading-snug">
                        Seni Wewangian yang Menghormati Kesunyian
                    </h2>
                    <p class="text-stone-300 text-xs sm:text-sm font-light leading-relaxed">
                        Di tengah riuhnya industri wewangian massal, Kuro Atelier memilih jalan sunyi para biksu Nara dan pemahat kekaisaran Kyoto. Setiap flacon dibuat dari kristal obsidian hitam pekat, disegel dengan cap lilin merah Hanko Inkan tradisional, dan ditandatangani oleh Master Perfumer.
                    </p>
                    <div class="pt-2">
                        <a href="filosofi.php" class="btn-outline-gold px-6 py-3 rounded-xl text-xs font-semibold inline-flex items-center gap-2">
                            <span>Baca Kisah & Filosofi Lengkap</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 text-amber-400"></i>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-5 grid grid-cols-2 gap-4">
                    <div class="p-5 rounded-2xl bg-black border border-stone-800 space-y-2">
                        <span class="font-kanji text-amber-400 text-2xl font-bold block">三百年</span>
                        <h4 class="font-serif-luxury text-xs font-bold text-white">Kayu Kyara 300 Th</h4>
                        <p class="text-[11px] text-stone-500 leading-relaxed font-light">Disuling dari batang dupa kuil suci Nara.</p>
                    </div>
                    <div class="p-5 rounded-2xl bg-black border border-stone-800 space-y-2">
                        <span class="font-kanji text-amber-400 text-2xl font-bold block">純金印</span>
                        <h4 class="font-serif-luxury text-xs font-bold text-white">Plat Emas 24K</h4>
                        <p class="text-[11px] text-stone-500 leading-relaxed font-light">Ditempa dan diukir tangan oleh pemahat Kyoto.</p>
                    </div>
                    <div class="p-5 rounded-2xl bg-black border border-stone-800 space-y-2">
                        <span class="font-kanji text-amber-400 text-2xl font-bold block">黒水晶</span>
                        <h4 class="font-serif-luxury text-xs font-bold text-white">Obsidian Flacon</h4>
                        <p class="text-[11px] text-stone-500 leading-relaxed font-light">Memblokir 100% sinar UV agar aroma abadi.</p>
                    </div>
                    <div class="p-5 rounded-2xl bg-black border border-stone-800 space-y-2">
                        <span class="font-kanji text-amber-400 text-2xl font-bold block">真贋鑑</span>
                        <h4 class="font-serif-luxury text-xs font-bold text-white">Sertifikat COA</h4>
                        <p class="text-[11px] text-stone-500 leading-relaxed font-light">Sidik jari SHA-256 dan stempel Hanko resmi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================================================
     SECTION 4: COA VERIFICATION TEASER
     =================================================== -->
<section class="py-20 border-b border-stone-900 bg-stone-950/60">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-400/40 mx-auto flex items-center justify-center text-amber-400">
            <i data-lucide="shield-check" class="w-6 h-6"></i>
        </div>
        <div class="space-y-2">
            <h2 class="font-serif-luxury text-2xl sm:text-3xl font-bold text-white">
                Verifikasi Sertifikat Keaslian COA
            </h2>
            <p class="text-xs text-stone-400 max-w-lg mx-auto font-light leading-relaxed">
                Punya botol Kuro Atelier? Masukkan nomor seri atau hash kriptografis untuk memverifikasi tanggal peresmian dan keabsahan kepemilikan tunggal Anda.
            </p>
        </div>
        <div>
            <a href="verifikasi.php" class="btn-gold px-8 py-3.5 rounded-xl text-xs font-bold inline-flex items-center gap-2 shadow-lg">
                <i data-lucide="search" class="w-4 h-4"></i>
                <span>BUKA PORTAL VERIFIKASI COA</span>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
