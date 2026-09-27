<?php
// ===================================================
// Kuro Atelier - Filosofi Shibui & Craftsmanship
// ===================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

initSession();
$pageTitle = 'Filosofi & Seni Craftsmanship • Kuro Atelier Tokyo';
$currentPage = 'filosofi';

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-16 bg-[#070708] min-h-[90vh]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-20">

        <!-- Title Section -->
        <div class="text-center space-y-4 max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 border border-amber-500/30 bg-amber-950/20 px-3.5 py-1.5 rounded-full text-xs text-amber-300 font-mono tracking-wider">
                <span class="font-kanji">侘寂 • 渋い</span>
                <span>FILOSOFI & TRADISI KERAJINAN TOKYO</span>
            </div>
            <h1 class="font-serif-luxury text-3xl sm:text-5xl font-bold text-white tracking-wide">
                KEANGGUNGAN DALAM KESUNYIAN MUTLAK
            </h1>
            <p class="text-stone-400 text-sm leading-relaxed font-light">
                Kuro Atelier tidak mengejar tren wewangian modern yang riuh. Kami mengembalikan seni parfum ke hakikat spiritualnya: sebuah medium perenungan jiwa, hening, eksklusif, dan abadi.
            </p>
        </div>

        <!-- The Concept of Shibui -->
        <div class="glass-kuro border border-amber-500/30 rounded-3xl p-8 sm:p-12 relative overflow-hidden shadow-2xl">
            <div class="absolute -right-8 -top-8 opacity-10 pointer-events-none select-none font-kanji text-[180px] text-amber-400">
                渋
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center relative z-10">
                <div class="space-y-4">
                    <span class="text-xs font-mono text-amber-400 uppercase tracking-widest block">ESENSI SHIBUI (渋い)</span>
                    <h2 class="font-serif-luxury text-2xl sm:text-3xl font-bold text-white leading-snug">
                        Kemewahan yang Menolak Berteriak
                    </h2>
                    <p class="text-stone-300 text-xs sm:text-sm leading-relaxed font-light">
                        Dalam estetika tradisional Jepang, <em>Shibui</em> melambangkan keindahan yang mendalam, tidak mencolok, namun menyimpan kekuatan batin yang tak terbantahkan. Seseorang yang mengenakan wewangian Kuro tidak mencari pengakuan orang asing—kehadirannya telah terukir sebelum sepatah kata pun diucapkan.
                    </p>
                    <p class="text-stone-400 text-xs leading-relaxed font-light">
                        Karya kami tidak didistribusikan secara massal ke etalase perbelanjaan umum. Setiap racikan adalah dialog sunyi antara sang Master Perfumer dan kolektor yang memiliki kepekaan rasa tertinggi.
                    </p>
                </div>
                <div class="rounded-2xl overflow-hidden border border-stone-800 bg-stone-950 p-2 shadow-2xl">
                    <img src="assets/images/kuro_kyara_oud.jpg" alt="Flacon Kuro Obsidian" class="w-full h-80 object-cover rounded-xl filter brightness-90 hover:brightness-100 transition-all duration-700">
                </div>
            </div>
        </div>

        <!-- The Four Sacred Pillars -->
        <div class="space-y-8">
            <div class="text-center space-y-2">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest">EMPAT PILAR KERAJINAN</span>
                <h3 class="font-serif-luxury text-2xl sm:text-3xl font-bold text-white">Anatomi Kemewahan Kuro</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pillar 1 -->
                <div class="glass-kuro border border-stone-800 hover:border-amber-500/50 p-8 rounded-2xl transition-all space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-400/40 flex items-center justify-center text-amber-400 font-kanji text-xl font-bold">
                            香
                        </div>
                        <div>
                            <h4 class="font-serif-luxury text-base font-bold text-white">Kayu Kyara Kuil Nara 300 Tahun</h4>
                            <span class="text-[10px] font-mono text-amber-400/80">KODO INCENSE HERITAGE</span>
                        </div>
                    </div>
                    <p class="text-xs text-stone-400 leading-relaxed font-light">
                        Minyak atsiri disuling dari batang kayu gaharu Kyara kuno yang tersimpan di kuil suci Nara. Diproses dengan distilasi fraksinasi suhu rendah selama 45 hari tanpa bahan pelarut sintetis, menghasilkan aroma kayu yang smoky, balsamic, dan agung.
                    </p>
                </div>

                <!-- Pillar 2 -->
                <div class="glass-kuro border border-stone-800 hover:border-amber-500/50 p-8 rounded-2xl transition-all space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-400/40 flex items-center justify-center text-amber-400 font-kanji text-xl font-bold">
                            晶
                        </div>
                        <div>
                            <h4 class="font-serif-luxury text-base font-bold text-white">Flacon Kristal Obsidian Hitam</h4>
                            <span class="text-[10px] font-mono text-amber-400/80">HAND-BLOWN TOKYO GLASS</span>
                        </div>
                    </div>
                    <p class="text-xs text-stone-400 leading-relaxed font-light">
                        Ditiup dengan tangan oleh pembuat kaca artisanal generasi keempat di pinggiran Tokyo. Ketebalan kristal hitam legam dirancang secara presisi untuk memblokir 100% spektrum sinar UV, melindungi molekul minyak wangi tetap murni selama beberapa dekade.
                    </p>
                </div>

                <!-- Pillar 3 -->
                <div class="glass-kuro border border-stone-800 hover:border-amber-500/50 p-8 rounded-2xl transition-all space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-400/40 flex items-center justify-center text-amber-400 font-kanji text-xl font-bold">
                            金
                        </div>
                        <div>
                            <h4 class="font-serif-luxury text-base font-bold text-white">Plat Emas 24K Ukiran Kyoto</h4>
                            <span class="text-[10px] font-mono text-amber-400/80">IMPERIAL GILDING & ENGRAVING</span>
                        </div>
                    </div>
                    <p class="text-xs text-stone-400 leading-relaxed font-light">
                        Setiap flacon disematkan plat emas murni 24 karat yang ditempa oleh seniman logam Kyoto. Kolektor dapat meminta ukiran tangan nama keluarga, inisial, atau kanji pribadi langsung saat proses pemesanan.
                    </p>
                </div>

                <!-- Pillar 4 -->
                <div class="glass-kuro border border-stone-800 hover:border-amber-500/50 p-8 rounded-2xl transition-all space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-400/40 flex items-center justify-center text-amber-400 font-kanji text-xl font-bold">
                            印
                        </div>
                        <div>
                            <h4 class="font-serif-luxury text-base font-bold text-white">Segel Hanko Inkan & Hak Kepemilikan</h4>
                            <span class="text-[10px] font-mono text-amber-400/80">AUTHENTICITY & PROVENANCE</span>
                        </div>
                    </div>
                    <p class="text-xs text-stone-400 leading-relaxed font-light">
                        Disertai sertifikat keaslian fisik beralas kertas washi tradisional berstempel Hanko Inkan merah (黒工房印) dan catatan sidik jari digital kriptografis SHA-256 yang tersimpan permanen di arsip kami.
                    </p>
                </div>
            </div>
        </div>

        <!-- Call to Action Banner -->
        <div class="glass-kuro border border-amber-500/40 rounded-2xl p-8 sm:p-10 text-center space-y-6 shadow-2xl">
            <div class="font-kanji text-3xl text-amber-400">黒</div>
            <h3 class="font-serif-luxury text-2xl sm:text-3xl font-bold text-white">
                Siap Menemukan Mahakarya Pribadi Anda?
            </h3>
            <p class="text-stone-400 text-xs sm:text-sm max-w-xl mx-auto font-light leading-relaxed">
                Jelajahi koleksi wewangian Series 24 atau ajukan kurasi identitas untuk mendapatkan akses rahasia karya tunggal 1-of-1.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-4">
                <a href="koleksi.php" class="btn-gold px-8 py-3.5 rounded-xl text-xs font-bold shadow-lg">
                    MASUK KE RUANG PAMER
                </a>
                <a href="whitelist.php" class="btn-outline-gold px-8 py-3.5 rounded-xl text-xs font-semibold">
                    AJUKAN AKSES VIP
                </a>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
