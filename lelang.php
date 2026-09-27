<?php
// ===================================================
// Kuro Atelier - Ruang Lelang Eksklusif (Salon des Ventes)
// Tokyo Ginza • 1-of-1 Bespoke & Masterpiece Bidding Showroom
// ===================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

initSession();
$currentUser = getCurrentUser();
$isUserWhitelisted = isWhitelisted();
$isUserAdmin = isAdmin();

$pageTitle = 'Ruang Lelang Eksklusif • Kuro Atelier Tokyo';
$currentPage = 'lelang';

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-16 bg-[#070708] min-h-[90vh] relative overflow-hidden">
    <!-- Subtle Background Gold Particles & Kanji Watermark -->
    <div class="absolute -right-20 top-20 opacity-5 pointer-events-none select-none font-kanji text-[300px] text-amber-400">
        競売
    </div>
    <div class="absolute -left-20 bottom-10 opacity-5 pointer-events-none select-none font-kanji text-[260px] text-amber-400">
        至高
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 relative z-10">

        <!-- Auction Room Header -->
        <div class="text-center space-y-4 max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 border border-amber-500/40 bg-amber-950/20 px-4 py-1.5 rounded-full text-xs text-amber-300 font-mono tracking-widest uppercase shadow-lg">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                <span>TOKYO GINZA • SALON DES VENTES (RUANG LELANG SAKRAL)</span>
            </div>
            <h1 class="font-serif-luxury text-3xl sm:text-5xl font-bold text-white tracking-wide leading-tight">
                LELANG KARYA MAHASUCI <span class="font-kanji text-amber-400 font-bold block sm:inline">（競売）</span>
            </h1>
            <p class="text-stone-400 text-xs sm:text-sm leading-relaxed font-light">
                Mahakarya wewangian tunggal dunia (1-of-1) yang dilelang secara terbuka dan transparan. Kolektor dengan akun terverifikasi dapat berpartisipasi langsung dalam penawaran (bidding) realtime ketika sesi lelang telah dibuka resmi.
            </p>

            <div class="pt-2 flex flex-wrap items-center justify-center gap-4 text-xs font-mono">
                <div class="px-3.5 py-1.5 rounded-lg bg-stone-900/80 border border-stone-800 text-stone-300 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Status Bidding: <strong class="text-emerald-300">Live & Realtime</strong></span>
                </div>
                <div class="px-3.5 py-1.5 rounded-lg bg-stone-900/80 border border-stone-800 text-stone-300 flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>Verifikasi: <strong class="text-amber-300">Kolektor Terdaftar</strong></span>
                </div>
            </div>
        </div>

        <!-- VIP Access Status Banner -->
        <?php if ($currentUser && ($isUserWhitelisted || $isUserAdmin)): ?>
            <div class="glass-kuro border border-emerald-500/40 bg-emerald-950/20 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xl">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 border border-emerald-400 flex items-center justify-center text-emerald-300 shrink-0">
                        <i data-lucide="check-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs font-serif-luxury font-bold text-emerald-300 tracking-wider block">HAK AKSES BIDDING TERBUKA PENUH</span>
                        <p class="text-[11px] text-stone-300">Akun Anda terverifikasi sebagai Sovereign VIP / Kuro Admin. Anda dapat langsung menempatkan penawaran pada semua sesi lelang yang berstatus OPEN.</p>
                    </div>
                </div>
                <button onclick="KuroApp.loadAuctionsPage()" class="btn-outline-gold px-4 py-2 rounded-xl text-xs flex items-center gap-1.5 shrink-0">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>Segarkan Bid Live</span>
                </button>
            </div>
        <?php elseif ($currentUser): ?>
            <div class="glass-kuro border border-amber-500/30 bg-amber-950/20 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xl">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-amber-500/10 border border-amber-400/40 flex items-center justify-center text-amber-400 shrink-0">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs font-serif-luxury font-bold text-amber-300 tracking-wider block">STATUS ANGGOTA: MENUNGGU KURASI VIP</span>
                        <p class="text-[11px] text-stone-300">Anda dapat memantau jalannya lelang dan melihat seluruh riwayat penawaran. Untuk mengajukan bid resmi, selesaikan kurasi VIP atau masukkan kode undangan patron.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="whitelist.php" class="btn-gold px-4 py-2 rounded-xl text-xs font-bold">
                        Buka Akses VIP
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="glass-kuro border border-stone-800 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xl">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-stone-900 border border-stone-700 flex items-center justify-center text-amber-400 shrink-0">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs font-serif-luxury font-bold text-white tracking-wider block">MASUK UNTUK MENEMPATKAN PENAWARAN (BID)</span>
                        <p class="text-[11px] text-stone-400">Lelang Kuro Atelier terbuka untuk umum dalam peninjauan data, namun penempatan bid memerlukan identitas patron terverifikasi.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 shrink-0 w-full sm:w-auto">
                    <button onclick="KuroApp.openLoginModal('Masuk untuk berpartisipasi dalam lelang karya 1-of-1.')" class="btn-gold px-5 py-2.5 rounded-xl text-xs font-bold text-center flex-1 sm:flex-initial">
                        Masuk Akun
                    </button>
                    <a href="whitelist.php" class="btn-outline-gold px-4 py-2.5 rounded-xl text-xs text-center flex-1 sm:flex-initial">
                        Ajukan Whitelist
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Auction Filters & Refresh Toolbar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-stone-800 pb-4">
            <div class="flex flex-wrap items-center gap-2">
                <button id="auc-filter-all" onclick="KuroApp.filterAuctionStatus('all')" class="px-4 py-2 rounded-xl text-xs font-serif-luxury font-bold border border-amber-400 bg-amber-500/20 text-amber-300 transition-all shadow-md">
                    SEMUA LELANG
                </button>
                <button id="auc-filter-open" onclick="KuroApp.filterAuctionStatus('open')" class="px-4 py-2 rounded-xl text-xs font-serif-luxury border border-stone-800 bg-stone-900 text-stone-400 hover:text-white hover:border-stone-700 transition-all">
                    🟢 SEDANG DIBUKA (OPEN)
                </button>
                <button id="auc-filter-upcoming" onclick="KuroApp.filterAuctionStatus('upcoming')" class="px-4 py-2 rounded-xl text-xs font-serif-luxury border border-stone-800 bg-stone-900 text-stone-400 hover:text-white hover:border-stone-700 transition-all">
                    🔵 AKAN DATANG (UPCOMING)
                </button>
                <button id="auc-filter-closed" onclick="KuroApp.filterAuctionStatus('closed')" class="px-4 py-2 rounded-xl text-xs font-serif-luxury border border-stone-800 bg-stone-900 text-stone-400 hover:text-white hover:border-stone-700 transition-all">
                    ⚪ SELESAI (CLOSED)
                </button>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto">
                <button onclick="KuroApp.loadAuctionsPage()" class="btn-outline-gold px-3.5 py-2 rounded-xl text-xs flex items-center gap-1.5" title="Segarkan Data Lelang">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>Segarkan</span>
                </button>
                <?php if ($isUserAdmin): ?>
                <a href="admin.php" class="px-3.5 py-2 rounded-xl text-xs bg-amber-950 border border-amber-500/50 text-amber-300 font-bold flex items-center gap-1.5">
                    <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                    <span>Kelola di Admin</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Auctions Grid -->
        <div id="auction-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="col-span-full py-16 text-center text-stone-500 space-y-3">
                <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-amber-400 mx-auto"></i>
                <p class="font-serif-luxury text-sm tracking-wider">Membuka Meja Lelang Kuro Atelier...</p>
            </div>
        </div>

    </div>
</div>

<!-- ===================================================
     MODAL: RIWAYAT PENAWARAN LENGKAP (BID HISTORY)
     =================================================== -->
<div id="auction-history-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
    <div class="glass-kuro border border-amber-500/40 rounded-2xl max-w-xl w-full p-6 md:p-8 space-y-6 relative shadow-2xl max-h-[90vh] overflow-y-auto">
        <button onclick="KuroApp.closeBidHistoryModal()" class="absolute top-5 right-5 text-stone-400 hover:text-white transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        <div class="space-y-1 border-b border-amber-500/30 pb-4">
            <div class="flex items-center gap-2 text-amber-400 text-[10px] font-mono tracking-widest uppercase">
                <i data-lucide="gavel" class="w-3.5 h-3.5"></i>
                <span>CATATAN RESMI DEWAN LELANG GINZA</span>
            </div>
            <h3 id="auction-history-title" class="font-serif-luxury text-xl font-bold text-white">
                RIWAYAT PENAWARAN (BID LADDER)
            </h3>
            <p id="auction-history-subtitle" class="text-xs text-stone-400 font-mono">
                Log penawaran terverifikasi secara kriptografis
            </p>
        </div>

        <div id="auction-history-content" class="space-y-3">
            <!-- Populated via openBidHistoryModal -->
        </div>

        <div class="pt-4 border-t border-stone-800 flex items-center justify-between">
            <span class="text-[10px] font-mono text-stone-500">Kerahasiaan nama patron dilindungi protokol Shibui.</span>
            <button onclick="KuroApp.closeBidHistoryModal()" class="btn-outline-gold px-4 py-2 rounded-xl text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof KuroApp !== 'undefined') {
        KuroApp.loadAuctionsPage();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
