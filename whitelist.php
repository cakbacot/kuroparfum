<?php
// ===================================================
// Kuro Atelier - Akses VIP & Kurasi Keanggotaan
// ===================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

initSession();
$pageTitle = 'Akses VIP & Kurasi Whitelist • Kuro Atelier Tokyo';
$currentPage = 'whitelist';

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-16 bg-[#070708] min-h-[90vh]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        <!-- Title Section -->
        <div class="text-center space-y-4 max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 border border-amber-500/30 bg-amber-950/20 px-3.5 py-1.5 rounded-full text-xs text-amber-300 font-mono tracking-wider">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                <span>SOVEREIGN COLLECTOR CHAMBER</span>
            </div>
            <h1 class="font-serif-luxury text-3xl sm:text-5xl font-bold text-white tracking-wide">
                AKSES VIP & KURASI ANGGOTA
            </h1>
            <p class="text-stone-400 text-sm leading-relaxed font-light">
                Karya wewangian 1-of-1 Kuro Atelier hanya diciptakan satu botol di seluruh dunia. Untuk menjaga integritas dan warisan karya, alokasi kepemilikan hanya diberikan melalui sistem kurasi personal atau undangan resmi.
            </p>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Column: Forms (Tabs: Kode Undangan vs Pengajuan Kurasi) -->
            <div class="lg:col-span-7 glass-kuro border border-amber-500/40 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl">
                
                <!-- Tab Buttons -->
                <div class="flex border-b border-stone-800 text-xs font-serif-luxury tracking-wider">
                    <button id="page-tab-invite" onclick="switchPageWhitelistTab('invite')" class="flex-1 py-3 text-center text-amber-300 border-b-2 border-amber-400 font-bold transition-all">
                        KODE UNDANGAN VIP
                    </button>
                    <button id="page-tab-apply" onclick="switchPageWhitelistTab('apply')" class="flex-1 py-3 text-center text-stone-400 hover:text-white transition-all">
                        PENGAJUAN KURASI IDENTITAS
                    </button>
                </div>

                <!-- Tab 1: Redeem Invite Code -->
                <div id="page-view-invite" class="space-y-5">
                    <p class="text-xs text-stone-400 font-light leading-relaxed">
                        Jika Anda telah menerima surat undangan fisik bersegel lilin merah atau kode rahasia dari Kuro Patron, masukkan kode otentikasi Anda di bawah ini:
                    </p>

                    <div class="space-y-3">
                        <label class="text-[10px] font-mono text-stone-400 uppercase tracking-wider block">KODE UNDANGAN RESMI (12-DIGIT)</label>
                        <input type="text" id="invite-code-input" placeholder="MASUKKAN KODE UNDANGAN VIP" autocomplete="off" value="" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-xl px-4 py-3.5 text-xs text-amber-200 uppercase font-mono tracking-widest outline-none text-center shadow-inner">
                    </div>

                    <button onclick="KuroApp.submitInviteCode()" class="btn-gold w-full py-3.5 rounded-xl text-xs font-bold shadow-lg flex items-center justify-center gap-2">
                        <i data-lucide="key" class="w-4 h-4"></i>
                        <span>VALIDASI KODE & BUKA AKSES PENUH</span>
                    </button>
                </div>

                <!-- Tab 2: Apply for Whitelist -->
                <div id="page-view-apply" class="space-y-5 hidden">
                    <p class="text-xs text-stone-400 font-light leading-relaxed">
                        Kuro Atelier membuka kesempatan bagi kolektor, patron seni, dan eksekutif untuk mengajukan diri ke dalam lingkaran kurasi:
                    </p>

                    <form id="whitelist-apply-form" onsubmit="KuroApp.submitWhitelistApplication(event)" class="space-y-4">
                        <div>
                            <label class="text-[10px] font-mono text-stone-400 uppercase">Nama Lengkap *</label>
                            <input type="text" name="full_name" required placeholder="Nama Lengkap Anda" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3.5 py-2.5 text-xs text-white outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono text-stone-400 uppercase">Alamat Email Resmi *</label>
                            <input type="email" name="email" required placeholder="email@perusahaan.com" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3.5 py-2.5 text-xs text-white outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono text-stone-400 uppercase">Organisasi / Lembaga & Jabatan *</label>
                            <input type="text" name="organization_title" required placeholder="Contoh: Managing Director, Sovereign Wealth / Art Collector" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3.5 py-2.5 text-xs text-white outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono text-stone-400 uppercase">Nomor Kontak Privat (WhatsApp/Telepon)</label>
                            <input type="tel" name="phone" placeholder="+62 812-xxxx-xxxx" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3.5 py-2.5 text-xs text-white outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono text-stone-400 uppercase">Pernyataan Minat Koleksi *</label>
                            <textarea name="statement_of_intent" rows="3" required placeholder="Ceritakan apresiasi Anda terhadap seni haute perfumery 1-of-1..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3.5 py-2.5 text-xs text-white outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label class="text-[10px] font-mono text-stone-400 uppercase">Preferensi Profil Aroma (Opsional)</label>
                            <input type="text" name="olfactory_preference" placeholder="Contoh: Dark Kyara Oud, Smoked Rose, Ambergris" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3.5 py-2.5 text-xs text-white outline-none">
                        </div>

                        <button type="submit" class="btn-gold w-full py-3.5 rounded-xl text-xs font-bold shadow-lg flex items-center justify-center gap-2 mt-4">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>KIRIM PERMOHONAN KURASI KE ATELIER</span>
                        </button>
                    </form>
                </div>

            </div>

            <!-- Right Column: Privileges & Exclusivity Policy -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Privileges Card -->
                <div class="glass-kuro border border-stone-800 rounded-3xl p-6 sm:p-8 space-y-5 shadow-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-400/40 flex items-center justify-center text-amber-400">
                            <i data-lucide="crown" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-serif-luxury text-base font-bold text-white tracking-wide">HAK ISTIMEWA VIP SOVEREIGN</h3>
                            <span class="text-[10px] font-mono text-amber-400/80">EXCLUSIVE PATRON PRIVILEGES</span>
                        </div>
                    </div>

                    <ul class="space-y-4 text-xs text-stone-300 font-light">
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                            <span><strong>Transparansi Nilai 1-of-1:</strong> Membuka akses harga rahasia mahakarya tunggal yang dilindungi enkripsi bagi publik.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                            <span><strong>Hak Akuisisi Tunggal:</strong> Berhak membeli dan menutup botol ke dalam status <em>Vaulted / Acquired</em> seumur hidup.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                            <span><strong>Layanan Concierge 1-on-1:</strong> Jalur komunikasi privat terenkripsi langsung dengan Kuro Master Perfumer di Tokyo.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                            <span><strong>Ukiran Grafir Emas 24K:</strong> Personalisasi inisial atau lambang keluarga kolektor yang diukir langsung oleh pemahat Kyoto.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                            <span><strong>Pengantaran Bersegel Hanko:</strong> Paket dikemas dalam kotak kayu Paulownia dengan kurir sarung tangan sutra hitam.</span>
                        </li>
                    </ul>
                </div>

                <!-- Privacy & Confidentiality Notice -->
                <div class="p-6 rounded-2xl border border-stone-800 bg-stone-950/60 space-y-2 text-xs text-stone-400 font-light">
                    <div class="flex items-center gap-2 text-amber-400 font-serif-luxury text-[11px] font-bold">
                        <i data-lucide="shield" class="w-4 h-4"></i>
                        <span>PROTOKOL KERAHASIAAN TINGKAT TINGGI</span>
                    </div>
                    <p class="leading-relaxed text-[11px]">
                        Identitas seluruh kolektor Kuro dilindungi di bawah perjanjian kerahasiaan ketat. Nama pembeli karya 1-of-1 tidak akan pernah dipublikasikan tanpa izin tertulis eksplisit.
                    </p>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
function switchPageWhitelistTab(tab) {
    const tabInvite = document.getElementById('page-tab-invite');
    const tabApply = document.getElementById('page-tab-apply');
    const viewInvite = document.getElementById('page-view-invite');
    const viewApply = document.getElementById('page-view-apply');

    if (tab === 'invite') {
        tabInvite.classList.add('border-b-2', 'border-amber-400', 'text-amber-300', 'font-bold');
        tabInvite.classList.remove('text-stone-400');
        tabApply.classList.remove('border-b-2', 'border-amber-400', 'text-amber-300', 'font-bold');
        tabApply.classList.add('text-stone-400');
        viewInvite.classList.remove('hidden');
        viewApply.classList.add('hidden');
    } else {
        tabApply.classList.add('border-b-2', 'border-amber-400', 'text-amber-300', 'font-bold');
        tabApply.classList.remove('text-stone-400');
        tabInvite.classList.remove('border-b-2', 'border-amber-400', 'text-amber-300', 'font-bold');
        tabInvite.classList.add('text-stone-400');
        viewApply.classList.remove('hidden');
        viewInvite.classList.add('hidden');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
