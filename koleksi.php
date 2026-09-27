<?php
// ===================================================
// Kuro Atelier - Showroom (Ruang Pamer Koleksi)
// 1-of-1 Bespoke Masterpieces & Series 24 Limited
// ===================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

initSession();
$pageTitle = 'Ruang Pamer Koleksi • Kuro Atelier Tokyo';
$currentPage = 'koleksi';

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-16 bg-stone-950/40 min-h-[90vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        <!-- Showroom Header -->
        <div class="text-center space-y-4 max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 border border-amber-500/30 bg-amber-950/20 px-3.5 py-1.5 rounded-full text-xs text-amber-300 font-mono tracking-wider">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                <span>RUANG PAMER ATELIER TOKYO • GINZA</span>
            </div>
            <h1 class="font-serif-luxury text-3xl sm:text-5xl font-bold text-white tracking-wide">
                MAHAKARYA OLFAKTORI
            </h1>
            <p class="text-stone-400 text-sm leading-relaxed font-light">
                Setiap formula diracik dengan tangan menggunakan minyak kayu gaharu Kyara berusia 300 tahun dari kuil suci Nara, dituangkan ke dalam flacon kristal obsidian berukirkan emas murni 24K.
            </p>
        </div>

        <!-- VIP Whitelist Access Banner -->
        <?php if ($isUserWhitelisted || $isUserAdmin): ?>
            <div class="glass-kuro border border-amber-500/50 bg-amber-950/30 rounded-2xl p-5 flex items-center justify-between gap-4 shadow-xl">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-amber-500/20 border border-amber-400 flex items-center justify-center text-amber-300">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs font-serif-luxury font-bold text-amber-300 tracking-wider block">AKSES VIP SOVEREIGN AKTIF</span>
                        <p class="text-[11px] text-stone-300">Hak istimewa akuisisi karya 1-of-1, transparansi harga rahasia, dan komunikasi privat Concierge telah terbuka penuh.</p>
                    </div>
                </div>
                <button onclick="KuroApp.openConcierge()" class="btn-outline-gold px-4 py-2 rounded-xl text-xs flex items-center gap-1.5 shrink-0 hidden sm:flex">
                    <i data-lucide="message-square" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>Hubungi Master</span>
                </button>
            </div>
        <?php else: ?>
            <div class="glass-kuro border border-stone-800 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xl">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-stone-900 border border-stone-700 flex items-center justify-center text-amber-400">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs font-serif-luxury font-bold text-white tracking-wider block">PROTOKOL KERAHASIAAN 1-OF-1</span>
                        <p class="text-[11px] text-stone-400">Harga dan hak akuisisi karya 1-of-1 hanya ditampilkan kepada anggota yang telah disetujui dalam Kuro Whitelist.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 shrink-0 w-full sm:w-auto">
                    <a href="whitelist.php" class="btn-gold px-4 py-2 rounded-xl text-xs font-bold text-center flex-1 sm:flex-initial">
                        Ajukan Whitelist
                    </a>
                    <button onclick="KuroApp.openLoginModal()" class="btn-outline-gold px-4 py-2 rounded-xl text-xs text-center flex-1 sm:flex-initial">
                        Masuk VIP
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Category Filters -->
        <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3">
            <button id="cat-all" onclick="KuroApp.filterCategory('all')" class="px-5 py-2.5 rounded-full text-xs font-serif-luxury tracking-widest border border-amber-400 bg-amber-500/20 text-amber-300 font-bold transition-all shadow-md">
                SEMUA KARYA (ALL EDITIONS)
            </button>
            <button id="cat-Series-24" onclick="KuroApp.filterCategory('Series-24')" class="px-5 py-2.5 rounded-full text-xs font-serif-luxury tracking-widest border border-stone-800 bg-stone-900 text-stone-400 hover:text-white hover:border-stone-700 transition-all">
                SERIES 24 (EDISI TERBATAS 24 BOTOL)
            </button>
            <button id="cat-1-of-1" onclick="KuroApp.filterCategory('1-of-1')" class="px-5 py-2.5 rounded-full text-xs font-serif-luxury tracking-widest border border-stone-800 bg-stone-900 text-stone-400 hover:text-white hover:border-stone-700 transition-all">
                1-OF-1 BESPOKE (MAHAKARYA TUNGGAL)
            </button>
        </div>

        <!-- Product Grid -->
        <div id="showroom-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Populated dynamically via KuroApp.loadProducts() -->
            <div class="col-span-full py-16 text-center text-stone-500 space-y-3">
                <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-amber-400 mx-auto"></i>
                <p class="font-serif-luxury text-sm tracking-wider">Membuka Lemari Kurasi Kuro...</p>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof KuroApp !== 'undefined') {
        KuroApp.loadProducts();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
