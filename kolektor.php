<?php
// ===================================================
// Kuro Atelier - Portal Anggota Kolektor
// Riwayat Pesanan, Live Fulfillment Tracking, & Concierge
// ===================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

initSession();
$pageTitle = 'Portal Pribadi Kolektor • Kuro Atelier Tokyo';
$currentPage = 'kolektor';

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-16 bg-[#070708] min-h-[90vh]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <?php if (!$currentUser): ?>
            <!-- Guest Gate / Prompt Login -->
            <div class="glass-kuro border border-amber-500/40 rounded-3xl p-10 sm:p-16 text-center max-w-2xl mx-auto space-y-6 shadow-2xl">
                <div class="w-16 h-16 rounded-2xl bg-black border border-gold-subtle mx-auto flex items-center justify-center text-amber-400 font-kanji text-3xl font-bold shadow-lg">
                    黒
                </div>
                <div class="space-y-2">
                    <h1 class="font-serif-luxury text-2xl sm:text-3xl font-bold text-white tracking-wide">
                        PORTAL PRIBADI KOLEKTOR
                    </h1>
                    <p class="text-xs text-stone-400 leading-relaxed font-light">
                        Halaman ini dikhususkan bagi anggota terdaftar untuk memantau status alokasi pesanan, pelacakan proses pengemasan & pengiriman kurir, serta berkonsultasi privat melalui Concierge.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                    <button onclick="KuroApp.openLoginModal()" class="btn-gold px-8 py-3.5 rounded-xl text-xs font-bold w-full sm:w-auto shadow-lg flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>MASUK KE AKUN ANDA</span>
                    </button>
                    <a href="whitelist.php" class="btn-outline-gold px-8 py-3.5 rounded-xl text-xs font-semibold w-full sm:w-auto flex items-center justify-center gap-2">
                        <i data-lucide="key" class="w-4 h-4"></i>
                        <span>AKSES VIP / DAFTAR</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Logged-in Collector Portal -->
            
            <!-- Profile Banner -->
            <div class="glass-kuro border border-amber-500/40 rounded-3xl p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-2xl relative overflow-hidden">
                <div class="space-y-2 relative z-10">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full <?= $isUserWhitelisted || $isUserAdmin ? 'bg-amber-400 animate-ping' : 'bg-emerald-400' ?>"></span>
                        <span class="text-xs font-mono text-amber-400 uppercase tracking-widest">
                            <?= $isUserAdmin ? 'KURO MASTER PERFUMER' : ($isUserWhitelisted ? 'SOVEREIGN VIP COLLECTOR' : 'ANGGOTA RESMI ATELIER') ?>
                        </span>
                    </div>
                    <h1 class="font-serif-luxury text-2xl sm:text-3xl font-bold text-white tracking-wide">
                        Konbanwa, <?= htmlspecialchars($currentUser['name']) ?>
                    </h1>
                    <p class="text-xs text-stone-400 font-mono">
                        <?= htmlspecialchars($currentUser['email']) ?> <?= !empty($currentUser['title_company']) ? '• ' . htmlspecialchars($currentUser['title_company']) : '' ?>
                    </p>
                </div>

                <div class="flex items-center gap-3 relative z-10 w-full md:w-auto">
                    <button onclick="KuroApp.openConcierge()" class="btn-gold px-5 py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-lg flex-1 md:flex-initial">
                        <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                        <span>Concierge Privat</span>
                    </button>
                    <?php if ($isUserAdmin): ?>
                    <a href="admin.php" class="btn-outline-gold px-5 py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 flex-1 md:flex-initial">
                        <i data-lucide="shield" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>Panel Admin</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Portal Tabs -->
            <div class="flex border-b border-stone-800 text-xs font-serif-luxury tracking-wider gap-2 overflow-x-auto pb-1">
                <button id="portal-tab-orders" onclick="switchPortalTab('orders')" class="px-5 py-3 rounded-t-xl bg-stone-900 border-t border-x border-amber-400/60 text-amber-300 font-bold flex items-center gap-2 transition-all">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span>RIWAYAT PESANAN & PENGIRIMAN</span>
                </button>
                <button id="portal-tab-concierge" onclick="switchPortalTab('concierge')" class="px-5 py-3 rounded-t-xl text-stone-400 hover:text-white hover:bg-stone-900/50 flex items-center gap-2 transition-all">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>CONCIERGE ATELIER (1-ON-1)</span>
                </button>
                <button id="portal-tab-vault" onclick="switchPortalTab('vault')" class="px-5 py-3 rounded-t-xl text-stone-400 hover:text-white hover:bg-stone-900/50 flex items-center gap-2 transition-all">
                    <i data-lucide="award" class="w-4 h-4"></i>
                    <span>BRANKAS BOTOL & SERTIFIKAT COA</span>
                </button>
            </div>

            <!-- Tab 1: Orders & Fulfillment Tracker -->
            <div id="portal-panel-orders" class="space-y-6">
                <div class="glass-kuro border border-stone-800 rounded-2xl overflow-hidden shadow-2xl">
                    <div class="p-5 bg-stone-950/80 border-b border-stone-800 flex items-center justify-between">
                        <div>
                            <h3 class="font-serif-luxury text-sm font-bold text-white tracking-wider">STATUS ALOKASI & PELACAKAN PENGIRIMAN</h3>
                            <p class="text-xs text-stone-400">Pantau proses pengemasan kotak kayu Paulownia dan perjalanan kurir sarung tangan sutra hitam.</p>
                        </div>
                        <button onclick="loadUserPortalOrders()" class="text-xs text-amber-400 hover:text-amber-200 flex items-center gap-1.5 transition-colors">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span>Muat Ulang</span>
                        </button>
                    </div>

                    <div id="portal-orders-list" class="divide-y divide-stone-900 p-6 space-y-6">
                        <div class="py-12 text-center text-stone-500 space-y-2">
                            <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-amber-400 mx-auto"></i>
                            <p class="text-xs">Memeriksa catatan pesanan Anda di buku besar Ginza...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Concierge Channel -->
            <div id="portal-panel-concierge" class="space-y-6 hidden">
                <div class="glass-kuro border border-amber-500/30 rounded-2xl overflow-hidden shadow-2xl h-[70vh] flex flex-col">
                    <div class="p-4 bg-stone-950 border-b border-stone-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-amber-500/20 border border-amber-400 flex items-center justify-center text-amber-300 font-kanji font-bold">
                                黒
                            </div>
                            <div>
                                <h3 class="font-serif-luxury text-sm font-bold text-white">Saluran Privat: Kuro Master Perfumer</h3>
                                <p class="text-[10px] text-stone-400">Tokyo Imperial Chamber • Enkripsi Langsung</p>
                            </div>
                        </div>
                        <span class="text-[10px] text-emerald-400 font-mono flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            ONLINE TERHUBUNG
                        </span>
                    </div>

                    <!-- Messages Stream in Portal -->
                    <div id="portal-concierge-stream" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gradient-to-b from-[#070708] via-stone-950/60 to-[#070708]">
                        <!-- Populated dynamically via loadPortalConcierge() -->
                    </div>

                    <!-- Input Bar -->
                    <div class="p-4 border-t border-stone-800 bg-stone-950 flex items-center gap-3">
                        <input type="text" id="portal-concierge-input" onkeydown="if(event.key==='Enter') sendPortalConcierge()" placeholder="Ketik pesan konsultasi privat atau permintaan grafir..." class="flex-1 bg-stone-900 border border-stone-800 focus:border-amber-400 rounded-xl px-4 py-3 text-xs text-white placeholder-stone-500 outline-none">
                        <button onclick="sendPortalConcierge()" class="btn-gold px-6 py-3 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-lg">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>KIRIM</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Vault & Certificate Collection -->
            <div id="portal-panel-vault" class="space-y-6 hidden">
                <div class="glass-kuro border border-stone-800 rounded-2xl p-6 space-y-6">
                    <div>
                        <h3 class="font-serif-luxury text-sm font-bold text-white tracking-wider">BRANKAS KEPEMILIKAN MAHABOTOL & COA</h3>
                        <p class="text-xs text-stone-400">Daftar karya berharga tinggi yang telah resmi diakuisisi dan disegel atas nama Anda.</p>
                    </div>
                    <div id="portal-vault-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Populated via loadUserVault() -->
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>

<script>
function switchPortalTab(tab) {
    const tabs = ['orders', 'concierge', 'vault'];
    tabs.forEach(t => {
        const btn = document.getElementById(`portal-tab-${t}`);
        const panel = document.getElementById(`portal-panel-${t}`);
        if (t === tab) {
            btn.className = 'px-5 py-3 rounded-t-xl bg-stone-900 border-t border-x border-amber-400/60 text-amber-300 font-bold flex items-center gap-2 transition-all';
            panel.classList.remove('hidden');
        } else {
            btn.className = 'px-5 py-3 rounded-t-xl text-stone-400 hover:text-white hover:bg-stone-900/50 flex items-center gap-2 transition-all';
            panel.classList.add('hidden');
        }
    });

    if (tab === 'concierge') {
        loadPortalConcierge();
    }
}

async function loadUserPortalOrders() {
    const container = document.getElementById('portal-orders-list');
    if (!container) return;

    try {
        const res = await fetch('api/orders.php?action=my_acquisitions');
        const data = await res.json();

        if (!data.success || !data.data.acquisitions || data.data.acquisitions.length === 0) {
            container.innerHTML = `
                <div class="p-12 text-center text-stone-500 space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-stone-900 border border-stone-800 mx-auto flex items-center justify-center text-stone-600">
                        <i data-lucide="package-x" class="w-6 h-6"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="font-serif-luxury text-sm text-stone-300">Belum Ada Pesanan Terdaftar</h4>
                        <p class="text-xs text-stone-500 font-light">Jelajahi koleksi Series 24 atau ajukan kurasi untuk karya 1-of-1.</p>
                    </div>
                    <a href="koleksi.php" class="btn-gold inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold">
                        <span>Lihat Ruang Pamer</span>
                    </a>
                </div>
            `;
            if (window.lucide) lucide.createIcons();
            return;
        }

        const orders = data.data.acquisitions;
        let html = '';

        orders.forEach(o => {
            const isPaid = o.payment_status === 'confirmed';
            const fStatus = o.fulfillment_status || 'menunggu';

            let fText = 'Menunggu Konfirmasi';
            let fBadgeColor = 'bg-stone-900 text-stone-400 border-stone-800';
            let step1 = 'bg-amber-400 text-black';
            let step2 = 'bg-stone-800 text-stone-400';
            let step3 = 'bg-stone-800 text-stone-400';
            let step4 = 'bg-stone-800 text-stone-400';

            if (fStatus === 'dikemas') {
                fText = '📦 Sedang Dikemas';
                fBadgeColor = 'bg-amber-950/60 text-amber-300 border-amber-600/50';
                step2 = 'bg-amber-400 text-black';
            } else if (fStatus === 'dikirim') {
                fText = '🚚 Sedang Melakukan Proses Pengiriman';
                fBadgeColor = 'bg-sky-950/60 text-sky-300 border-sky-500/50';
                step2 = 'bg-amber-400 text-black';
                step3 = 'bg-sky-400 text-black font-bold';
            } else if (fStatus === 'selesai') {
                fText = '✅ Barang Sudah Diterima';
                fBadgeColor = 'bg-emerald-950/60 text-emerald-300 border-emerald-500/50';
                step2 = 'bg-amber-400 text-black';
                step3 = 'bg-amber-400 text-black';
                step4 = 'bg-emerald-400 text-black font-bold';
            }

            html += `
                <div class="glass-kuro p-6 rounded-2xl border border-stone-800 space-y-5">
                    <!-- Order Top Row -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-stone-800/80 pb-4">
                        <div>
                            <span class="font-mono text-xs text-amber-300 font-bold block">${o.order_number}</span>
                            <span class="text-[10px] text-stone-500 font-mono">Invoice: ${o.invoice_number || '-'} • Diterbitkan: ${o.created_at}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-mono border ${isPaid ? 'bg-emerald-950/60 text-emerald-300 border-emerald-500/40' : 'bg-stone-900 text-stone-400 border-stone-800'}">
                                ${isPaid ? '● LUNAS' : '○ MENUNGGU PEMBAYARAN'}
                            </span>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-mono border ${fBadgeColor}">
                                ${fText}
                            </span>
                        </div>
                    </div>

                    <!-- Fulfillment Stepper Tracker -->
                    <div class="bg-black/50 p-4 rounded-xl border border-stone-800/60 space-y-3">
                        <div class="text-[10px] font-mono text-stone-500 uppercase tracking-wider">PELACAKAN PENGIRIMAN EKSEKUTIF:</div>
                        <div class="grid grid-cols-4 gap-2 text-center text-[10px] font-mono">
                            <div class="p-2 rounded-lg ${step1}">1. Diterima</div>
                            <div class="p-2 rounded-lg ${step2}">2. Dikemas (Paulownia)</div>
                            <div class="p-2 rounded-lg ${step3}">3. Pengiriman Kurir</div>
                            <div class="p-2 rounded-lg ${step4}">4. Diterima Klien</div>
                        </div>

                        ${o.courier_name || o.tracking_number ? `
                            <div class="pt-2 text-xs text-stone-300 space-y-1">
                                ${o.courier_name ? `<div>Kurir Privat: <strong class="text-amber-200">${o.courier_name}</strong></div>` : ''}
                                ${o.tracking_number ? `<div>No. Resi Pelacakan: <span class="font-mono text-amber-300 font-bold tracking-wider">${o.tracking_number}</span></div>` : ''}
                                ${o.fulfillment_notes ? `<div class="text-[11px] text-stone-400 italic">"${o.fulfillment_notes}"</div>` : ''}
                            </div>
                        ` : ''}
                    </div>

                    <!-- Items List -->
                    <div class="space-y-2">
                        ${(o.items || []).map(it => `
                            <div class="flex items-center justify-between text-xs py-1.5 border-b border-stone-900">
                                <div class="flex items-center gap-3">
                                    <img src="${it.image_url || 'assets/images/kuro_series24_noir.jpg'}" class="w-10 h-10 object-cover rounded-lg border border-stone-800" alt="${it.product_name}">
                                    <div>
                                        <div class="font-semibold text-white">${it.product_name}</div>
                                        <div class="text-[10px] text-stone-500 font-mono">Jumlah: ${it.quantity} unit • ${it.edition_serial || 'Series 24'}</div>
                                    </div>
                                </div>
                                <div class="font-serif-luxury text-amber-300 font-bold">
                                    ${it.subtotal_formatted || it.price_formatted}
                                </div>
                            </div>
                        `).join('')}
                    </div>

                    <!-- Footer of Order Card -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-2">
                        <div>
                            <span class="text-[10px] font-mono text-stone-500 uppercase block">ALAMAT PENGANTARAN</span>
                            <p class="text-xs text-stone-300">${o.delivery_address || '-'}</p>
                        </div>
                        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                            <div class="text-right">
                                <span class="text-[10px] font-mono text-stone-500 uppercase block">TOTAL NILAI</span>
                                <span class="font-serif-luxury text-base text-gold-gradient font-bold">${o.total_formatted}</span>
                            </div>
                            <button onclick="KuroApp.viewOrderInvoiceFromAdmin(${o.id})" class="btn-outline-gold px-4 py-2 rounded-xl text-xs flex items-center gap-1.5 shrink-0">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 text-amber-400"></i>
                                <span>Lihat Invoice</span>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        if (window.lucide) lucide.createIcons();
    } catch (e) {
        console.error(e);
        container.innerHTML = `<div class="p-8 text-center text-rose-400">Gagal memuat pesanan.</div>`;
    }
}

async function loadPortalConcierge() {
    const stream = document.getElementById('portal-concierge-stream');
    if (!stream) return;

    try {
        const res = await fetch('api/concierge.php?action=my_thread');
        const data = await res.json();
        if (data.success && data.data.messages) {
            let html = '';
            data.data.messages.forEach(m => {
                const isAdmin = m.sender_role === 'kuro_admin';
                html += `
                    <div class="flex flex-col ${isAdmin ? 'items-start' : 'items-end'} space-y-1">
                        <div class="flex items-center gap-2 text-[10px] font-mono text-stone-500">
                            <span>${m.sender_name}</span>
                            <span>•</span>
                            <span>${m.created_at}</span>
                        </div>
                        <div class="max-w-md p-3.5 rounded-2xl text-xs leading-relaxed ${isAdmin ? 'bg-amber-950/40 border border-amber-500/30 text-amber-100 rounded-tl-sm' : 'bg-stone-900 border border-stone-800 text-stone-200 rounded-tr-sm'}">
                            ${m.message}
                        </div>
                    </div>
                `;
            });
            stream.innerHTML = html;
            stream.scrollTop = stream.scrollHeight;
        }
    } catch (e) {
        console.error(e);
    }
}

async function sendPortalConcierge() {
    const input = document.getElementById('portal-concierge-input');
    const msg = input ? input.value.trim() : '';
    if (!msg) return;

    try {
        const res = await fetch('api/concierge.php?action=send_message', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: msg })
        });
        const data = await res.json();
        if (data.success) {
            input.value = '';
            loadPortalConcierge();
        } else {
            if (typeof KuroApp !== 'undefined') KuroApp.showToast(data.message, 'error');
        }
    } catch (e) {
        console.error(e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadUserPortalOrders();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
