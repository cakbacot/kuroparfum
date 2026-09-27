<?php
// ===================================================
// Kuro Atelier - Verifikasi Keaslian Sertifikat COA
// ===================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

initSession();
$pageTitle = 'Verifikasi Sertifikat COA • Kuro Atelier Tokyo';
$currentPage = 'verifikasi';

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-16 bg-[#070708] min-h-[90vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        <!-- Header -->
        <div class="text-center space-y-4 max-w-2xl mx-auto">
            <div class="inline-flex items-center gap-2 border border-amber-500/30 bg-amber-950/20 px-3.5 py-1.5 rounded-full text-xs text-amber-300 font-mono tracking-wider">
                <span class="font-kanji">真贋鑑定</span>
                <span>AUTHENTICITY VERIFICATION PORTAL</span>
            </div>
            <h1 class="font-serif-luxury text-3xl sm:text-5xl font-bold text-white tracking-wide">
                VERIFIKASI SERTIFIKAT COA
            </h1>
            <p class="text-stone-400 text-sm leading-relaxed font-light">
                Setiap flacon Kuro Atelier memuat identitas tunggal yang terdaftar dalam buku besar Ginza. Masukkan nomor seri Certificate of Authenticity (COA) Anda untuk memverifikasi keabsahan kepemilikan dan asal-usul karya.
            </p>
        </div>

        <!-- Search Card -->
        <div class="glass-kuro border border-amber-500/40 rounded-3xl p-8 sm:p-10 space-y-6 shadow-2xl relative overflow-hidden">
            <div class="space-y-3">
                <label class="text-xs font-mono text-amber-400 uppercase tracking-wider block">
                    NOMOR SERI COA / EDISI BOTOL
                </label>
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <input type="text" id="verify-coa-input" placeholder="Masukkan nomor seri COA (Contoh: COA-KURO-001-2026-X94K)" class="w-full sm:flex-1 bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-xl px-4 py-3.5 text-xs text-white placeholder-stone-600 outline-none font-mono">
                    <button onclick="verifyCertificateInput()" class="btn-gold px-8 py-3.5 rounded-xl text-xs font-bold w-full sm:w-auto shrink-0 shadow-lg flex items-center justify-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>VERIFIKASI KEASLIAN</span>
                    </button>
                </div>
            </div>

            <!-- Dynamic Result Container -->
            <div id="coa-lookup-result" class="hidden">
                <!-- Result dynamically rendered -->
            </div>
        </div>

        <!-- Authenticity Standards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
            <div class="glass-kuro border border-stone-800 p-6 rounded-2xl space-y-3">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-400/40 flex items-center justify-center text-amber-400">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
                <h3 class="font-serif-luxury text-sm font-bold text-white">Kertas Washi Echizen</h3>
                <p class="text-xs text-stone-400 font-light leading-relaxed">
                    Sertifikat dicetak di atas serat kertas washi buatan tangan berusia ratusan tahun yang tahan terhadap kelembapan dan pembusukan selama berabad-abad.
                </p>
            </div>

            <div class="glass-kuro border border-stone-800 p-6 rounded-2xl space-y-3">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-400/40 flex items-center justify-center text-amber-400">
                    <i data-lucide="stamp" class="w-4 h-4"></i>
                </div>
                <h3 class="font-serif-luxury text-sm font-bold text-white">Stempel Hanko Inkan (黒工房印)</h3>
                <p class="text-xs text-stone-400 font-light leading-relaxed">
                    Setiap dokumen fisik dicap langsung dengan tinta mineral sinabar merah murni yang diukir dengan kanji eksklusif Kuro Tokyo.
                </p>
            </div>

            <div class="glass-kuro border border-stone-800 p-6 rounded-2xl space-y-3">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-400/40 flex items-center justify-center text-amber-400">
                    <i data-lucide="fingerprint" class="w-4 h-4"></i>
                </div>
                <h3 class="font-serif-luxury text-sm font-bold text-white">Sidik Jari SHA-256</h3>
                <p class="text-xs text-stone-400 font-light leading-relaxed">
                    Kombinasi serial edisi, nama pemilik terdaftar, stempel waktu peresmian, dan kunci privat atelier menghasilkan tanda tangan hash digital yang tak dapat dipalsukan.
                </p>
            </div>
        </div>

    </div>
</div>

<script>
async function verifyCertificateInput() {
    const input = document.getElementById('verify-coa-input');
    const serial = input ? input.value.trim() : '';
    const resultBox = document.getElementById('coa-lookup-result');

    if (!serial) {
        if (typeof KuroApp !== 'undefined') {
            KuroApp.showToast('Masukkan nomor seri atau hash sertifikat COA.', 'error');
        }
        return;
    }

    try {
        const res = await fetch(`api/orders.php?action=verify_coa&serial=${encodeURIComponent(serial)}`);
        const data = await res.json();
        
        resultBox.classList.remove('hidden');
        if (data.success) {
            if (typeof KuroApp !== 'undefined') KuroApp.playZenChime('bell');
            const c = data.data.certificate;
            resultBox.innerHTML = `
                <div class="p-6 rounded-2xl border border-emerald-500/50 bg-emerald-950/20 text-left space-y-4">
                    <div class="flex items-center gap-2.5 text-emerald-400 font-serif-luxury font-bold text-sm">
                        <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
                        <span>TERVERIFIKASI OTENTIK • TERCATAT DALAM ARSIP RESMI KURO ATELIER TOKYO</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-stone-300 pt-2 border-t border-emerald-500/30">
                        <div><span class="text-stone-500 font-mono">Mahakarya:</span> <strong class="text-white block">${c.product_name}</strong></div>
                        <div><span class="text-stone-500 font-mono">Serial Edisi:</span> <span class="font-mono text-amber-300 block">${c.edition_serial}</span></div>
                        <div><span class="text-stone-500 font-mono">Pemilik Tunggal:</span> <strong class="text-white block">${c.owner_name}</strong></div>
                        <div><span class="text-stone-500 font-mono">Afiliasi / Gelar:</span> <span class="text-stone-400 block">${c.title_company || 'Sovereign Collector'}</span></div>
                        <div><span class="text-stone-500 font-mono">Nomor Seri COA:</span> <span class="font-mono text-amber-200 block">${c.coa_serial}</span></div>
                        <div><span class="text-stone-500 font-mono">Tanggal Peresmian:</span> <span class="font-mono text-stone-400 block">${c.acquired_at}</span></div>
                    </div>
                    ${c.custom_engraving ? `
                        <div class="pt-2 border-t border-emerald-500/30 text-xs text-stone-400">
                            Ukiran Kustom Plat Emas: <span class="italic text-amber-200">"${c.custom_engraving}"</span>
                        </div>
                    ` : ''}
                </div>
            `;
        } else {
            resultBox.innerHTML = `
                <div class="p-5 rounded-xl border border-rose-500/50 bg-rose-950/20 text-rose-300 text-xs text-center flex items-center justify-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span>${data.message}</span>
                </div>
            `;
        }
        if (window.lucide) lucide.createIcons();
    } catch (e) {
        console.error(e);
        resultBox.classList.remove('hidden');
        resultBox.innerHTML = `<div class="p-4 rounded-xl border border-rose-500 bg-rose-950/30 text-rose-300 text-xs text-center">Gagal menghubungi server verifikasi.</div>`;
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
