<?php
// ===================================================
// Kuro Atelier - Shared Executive Navigation & Header
// ===================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

initSession();
$currentUser = getCurrentUser();
$isUserWhitelisted = isWhitelisted();
$isUserAdmin = isAdmin();

$pageTitle = $pageTitle ?? 'KURO Atelier • Haute Parfumerie 1-of-1';
$currentPage = $currentPage ?? 'home';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="Kuro Atelier Haute Parfumerie Tokyo. Mahakarya wewangian 1-of-1 bespoke dan Series 24 beraroma kayu Kyara 300 tahun dalam flacon kristal obsidian berukir emas 24K.">

    <!-- Fonts: Google Fonts (Cinzel Luxury, Outfit Modern, Noto Serif JP) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=Cinzel:wght@400;600;700;800&family=Noto+Serif+JP:wght@300;400;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: {
                            subtle: 'rgba(212, 175, 55, 0.25)',
                            DEFAULT: '#D4AF37',
                            light: '#F3E5AB',
                            dark: '#997F24',
                            amber: '#FFBF00'
                        }
                    },
                    fontFamily: {
                        'serif-luxury': ['Cinzel', 'serif'],
                        'serif-deco': ['"Cinzel Decorative"', 'serif'],
                        'kanji': ['"Noto Serif JP"', 'serif'],
                        'sans': ['Outfit', 'sans-serif'],
                        'mono': ['monospace']
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
<body class="bg-[#070708] text-stone-200 antialiased selection:bg-amber-500/30 selection:text-amber-200 flex flex-col min-h-screen">

    <!-- ===================================================
         MAIN EXECUTIVE NAVIGATION BAR
         =================================================== -->
    <header class="sticky top-0 z-40 glass-kuro border-b border-gold-subtle">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="index.php" class="flex items-center gap-3.5 group">
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
            <nav class="hidden lg:flex items-center gap-7 text-xs font-serif-luxury tracking-widest text-stone-300">
                <a href="index.php" class="transition-colors <?= $currentPage === 'home' ? 'text-amber-300 font-bold border-b border-amber-400 pb-1' : 'hover:text-amber-300' ?>">
                    BERANDA
                </a>
                <a href="koleksi.php" class="transition-colors <?= $currentPage === 'koleksi' ? 'text-amber-300 font-bold border-b border-amber-400 pb-1' : 'hover:text-amber-300' ?>">
                    RUANG PAMER
                </a>
                <a href="filosofi.php" class="transition-colors <?= $currentPage === 'filosofi' ? 'text-amber-300 font-bold border-b border-amber-400 pb-1' : 'hover:text-amber-300' ?>">
                    FILOSOFI
                </a>
                <a href="whitelist.php" class="transition-colors <?= $currentPage === 'whitelist' ? 'text-amber-300 font-bold border-b border-amber-400 pb-1' : 'hover:text-amber-300' ?>">
                    AKSES VIP
                </a>
                <a href="verifikasi.php" class="transition-colors <?= $currentPage === 'verifikasi' ? 'text-amber-300 font-bold border-b border-amber-400 pb-1' : 'hover:text-amber-300' ?>">
                    VERIFIKASI COA
                </a>
                <a href="kolektor.php" class="transition-colors <?= $currentPage === 'kolektor' ? 'text-amber-300 font-bold border-b border-amber-400 pb-1' : 'hover:text-amber-300' ?>">
                    PORTAL SAYA
                </a>
                <a id="nav-admin-link" href="admin.php" class="<?= $isUserAdmin ? '' : 'hidden ' ?>px-3 py-1 rounded-full bg-amber-950/60 border border-amber-500/60 text-amber-300 font-bold flex items-center gap-1.5 hover:bg-amber-900/60 transition-all shadow-md">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <span>KURO DESK (ADMIN)</span>
                </a>
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

                <!-- User Session & Status Badge -->
                <div id="user-session-badge">
                    <?php if ($currentUser): ?>
                        <div class="flex items-center gap-2 border <?= $isUserAdmin ? 'border-amber-500/50 bg-amber-950/30 text-amber-300' : ($isUserWhitelisted ? 'border-emerald-500/50 bg-emerald-950/30 text-emerald-300' : 'border-stone-700 bg-stone-900 text-stone-300') ?> px-3 py-1.5 rounded-full text-xs tracking-wider">
                            <span class="w-2 h-2 rounded-full <?= $isUserWhitelisted || $isUserAdmin ? 'bg-amber-400 animate-ping' : 'bg-emerald-400' ?>"></span>
                            <a href="kolektor.php" class="font-semibold text-white hover:text-amber-300 transition-colors">
                                <?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?>
                            </a>
                            <span class="text-[10px] opacity-75 font-mono">
                                (<?= $isUserAdmin ? 'KURO MASTER' : ($isUserWhitelisted ? 'SOVEREIGN VIP' : 'ANGGOTA') ?>)
                            </span>
                            <button onclick="KuroApp.logout()" title="Keluar Sesi" class="ml-1 text-stone-400 hover:text-rose-400 transition-colors">
                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-2">
                            <button onclick="KuroApp.openLoginModal()" class="text-xs px-3.5 py-1.5 rounded-full text-stone-300 hover:text-amber-200 border border-stone-800 hover:border-amber-500/40 transition-colors font-medium">
                                MASUK
                            </button>
                            <a href="whitelist.php" class="btn-outline-gold text-xs px-3.5 py-1.5 rounded-full flex items-center gap-1.5 font-medium">
                                <i data-lucide="key" class="w-3 h-3 text-amber-400"></i>
                                <span>AKSES VIP</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <button onclick="document.getElementById('mobile-nav').classList.toggle('hidden')" class="lg:hidden p-2 text-stone-400 hover:text-white">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div id="mobile-nav" class="hidden lg:hidden border-t border-stone-800 bg-[#070708]/95 backdrop-blur-xl px-6 py-4 space-y-3 font-serif-luxury text-xs tracking-widest">
            <a href="index.php" class="block py-2 <?= $currentPage === 'home' ? 'text-amber-300 font-bold' : 'text-stone-300' ?>">BERANDA</a>
            <a href="koleksi.php" class="block py-2 <?= $currentPage === 'koleksi' ? 'text-amber-300 font-bold' : 'text-stone-300' ?>">RUANG PAMER</a>
            <a href="filosofi.php" class="block py-2 <?= $currentPage === 'filosofi' ? 'text-amber-300 font-bold' : 'text-stone-300' ?>">FILOSOFI & CRAFT</a>
            <a href="whitelist.php" class="block py-2 <?= $currentPage === 'whitelist' ? 'text-amber-300 font-bold' : 'text-stone-300' ?>">AKSES VIP</a>
            <a href="verifikasi.php" class="block py-2 <?= $currentPage === 'verifikasi' ? 'text-amber-300 font-bold' : 'text-stone-300' ?>">VERIFIKASI COA</a>
            <a href="kolektor.php" class="block py-2 <?= $currentPage === 'kolektor' ? 'text-amber-300 font-bold' : 'text-stone-300' ?>">PORTAL SAYA</a>
            <?php if ($isUserAdmin): ?>
            <a href="admin.php" class="block py-2 text-amber-400 font-bold border-t border-stone-800 pt-3">⚜️ PANEL KURASI (ADMIN)</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="flex-grow">
