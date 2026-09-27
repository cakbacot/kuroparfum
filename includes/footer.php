    </main>

    <!-- ===================================================
         GLOBAL LUXURY FOOTER
         =================================================== -->
    <footer class="border-t border-stone-900 bg-black/80 py-16 text-xs text-stone-500 font-light mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
                <!-- Brand Col -->
                <div class="space-y-4 md:col-span-1">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-black border border-gold-subtle flex items-center justify-center text-amber-400 font-kanji text-xl font-bold">
                            黒
                        </div>
                        <div>
                            <span class="font-serif-luxury font-bold text-white tracking-[0.2em] text-sm block">KURO ATELIER</span>
                            <span class="text-[9px] font-mono tracking-widest text-amber-400/80">TOKYO • GINZA DISTRICT</span>
                        </div>
                    </div>
                    <p class="leading-relaxed text-stone-400 text-[11px]">
                        Atelier wewangian independen yang mendedikasikan diri pada penciptaan karya 1-of-1 bersertifikat kepemilikan mutlak dan edisi terbatas Series 24.
                    </p>
                    <div class="flex items-center gap-2 text-[10px] font-mono text-amber-400/70">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>SERVER STATUS: SECURE & VERIFIED</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="space-y-3">
                    <h4 class="font-serif-luxury text-stone-300 font-semibold tracking-wider text-xs uppercase">EKSPLORASI ATELIER</h4>
                    <ul class="space-y-2 text-[11px]">
                        <li><a href="index.php" class="hover:text-amber-300 transition-colors">Beranda Utama</a></li>
                        <li><a href="koleksi.php" class="hover:text-amber-300 transition-colors">Ruang Pamer Koleksi</a></li>
                        <li><a href="lelang.php" class="hover:text-amber-300 transition-colors flex items-center gap-1.5"><i data-lucide="gavel" class="w-3 h-3 text-amber-400"></i><span>Ruang Lelang Eksklusif</span></a></li>
                        <li><a href="filosofi.php" class="hover:text-amber-300 transition-colors">Filosofi & Craftsmanship</a></li>
                        <li><a href="whitelist.php" class="hover:text-amber-300 transition-colors">Akses VIP & Kurasi</a></li>
                        <li><a href="verifikasi.php" class="hover:text-amber-300 transition-colors">Verifikasi Sertifikat COA</a></li>
                    </ul>
                </div>

                <!-- Collector Services -->
                <div class="space-y-3">
                    <h4 class="font-serif-luxury text-stone-300 font-semibold tracking-wider text-xs uppercase">LAYANAN KOLEKTOR</h4>
                    <ul class="space-y-2 text-[11px]">
                        <li><a href="kolektor.php" class="hover:text-amber-300 transition-colors">Portal Pribadi Kolektor</a></li>
                        <li><button onclick="KuroApp.openConcierge()" class="hover:text-amber-300 transition-colors text-left">Konsultasi Concierge Privat</button></li>
                        <li><button onclick="KuroApp.openCartDrawer()" class="hover:text-amber-300 transition-colors text-left">Keranjang Belanja Series 24</button></li>
                        <li><button onclick="KuroApp.openLoginModal()" class="hover:text-amber-300 transition-colors text-left">Portal Masuk / Pendaftaran</button></li>
                    </ul>
                </div>

                <!-- Hanko Japanese Seal & Heritage -->
                <div class="space-y-3 md:col-span-1">
                    <h4 class="font-serif-luxury text-stone-300 font-semibold tracking-wider text-xs uppercase">SEGEL KEASLIAN</h4>
                    <div class="p-4 rounded-xl bg-stone-950 border border-stone-800 space-y-2 text-[11px]">
                        <div class="flex items-center gap-2">
                            <span class="font-kanji text-rose-500 font-bold text-lg">黒工房印</span>
                            <span class="text-stone-300 font-serif-luxury text-[10px] tracking-widest">HANKO IMPERIAL SEAL</span>
                        </div>
                        <p class="text-stone-500 text-[10px] leading-relaxed">
                            Setiap botol parfum diikat dengan tali sutra hitam dan disegel dengan stempel cap lilin merah berukirkan kanji otentik Kuro.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 border-t border-stone-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-[10px] font-mono text-stone-600">
                <div>
                    &copy; <?= date('Y') ?> KURO ATELIER TOKYO. All Rights Reserved. Hak Cipta Dilindungi Undang-Undang.
                </div>
                <div class="flex items-center gap-6">
                    <a href="verifikasi.php" class="hover:text-amber-400 transition-colors">Sertifikasi Kriptografis</a>
                    <a href="filosofi.php" class="hover:text-amber-400 transition-colors">Standar Shibui</a>
                    <a href="whitelist.php" class="hover:text-amber-400 transition-colors">Protokol Kerahasiaan</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ===================================================
         GLOBAL SHARED MODALS & DRAWERS
         =================================================== -->

    <!-- 1. MODAL: LOGIN & REGISTER -->
    <div id="auth-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="glass-kuro border border-amber-500/40 rounded-2xl max-w-md w-full p-6 md:p-8 space-y-6 relative shadow-2xl">
            <button onclick="KuroApp.closeLoginModal()" class="absolute top-5 right-5 text-stone-400 hover:text-white transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <!-- Brand Header -->
            <div class="text-center space-y-1">
                <div class="w-12 h-12 rounded-xl bg-black border border-gold-subtle mx-auto flex items-center justify-center text-amber-400 font-kanji text-2xl font-bold shadow-lg">
                    黒
                </div>
                <h3 class="font-serif-luxury text-xl font-bold text-white tracking-widest pt-2">KURO ATELIER</h3>
                <p class="text-[10px] text-amber-400/80 font-mono tracking-wider uppercase">AKSES ANGGOTA • PORTAL KOLEKTOR</p>
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
            <form id="form-auth-login" onsubmit="KuroApp.submitLogin(event)" class="space-y-4 text-left" autocomplete="off">
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Alamat Email</label>
                    <input type="email" name="email" id="login-email" required placeholder="email@domain.com" autocomplete="off" value="" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Kata Sandi</label>
                    <input type="password" name="password" id="login-password" required placeholder="••••••••" autocomplete="new-password" value="" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
                </div>

                <button type="submit" class="btn-gold w-full py-3 rounded-xl text-xs font-bold">
                    MASUK KE AKUN SAYA
                </button>
            </form>

            <!-- Form: Register -->
            <form id="form-auth-register" onsubmit="KuroApp.submitRegister(event)" class="space-y-3 text-left hidden" autocomplete="off">
                <div>
                    <label class="text-[10px] font-mono text-stone-400 uppercase">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Nama Lengkap Anda" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3 py-2 text-xs text-white outline-none">
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

    <!-- 2. DRAWER: SHOPPING CART (SERIES 24) -->
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
                        Pesanan ini akan dicatat ke dalam antrean alokasi inventaris Kuro Atelier.
                    </p>
                    <button id="cart-drawer-checkout-btn" onclick="KuroApp.openCartCheckout()" class="btn-gold w-full py-3.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-lg">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>LANJUT KE CHECKOUT REVIEW</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. MODAL: SERIES 24 CHECKOUT REVIEW -->
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
                    Lengkapi alamat pengiriman untuk mengunci alokasi botol Series 24 Anda.
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

    <!-- 4. MODAL: OFFICIAL INVOICE & ORDER SUMMARY -->
    <div id="order-summary-modal" class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-6 hidden overflow-y-auto">
        <div id="order-summary-content" class="w-full max-w-3xl my-auto">
            <!-- Dynamically populated via KuroApp.showCheckoutSummary() -->
        </div>
    </div>

    <!-- 5. MODAL: PRODUCT DETAIL & OLFACTORY PYRAMID -->
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

    <!-- 6. MODAL: CONCIERGE CHAT -->
    <div id="concierge-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="glass-kuro border border-amber-500/40 rounded-2xl max-w-2xl w-full h-[85vh] flex flex-col relative shadow-2xl overflow-hidden">
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

    <!-- 7. MODAL: 1-OF-1 HIGH-VALUE CHECKOUT & BESPOKE ACQUISITION -->
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

    <!-- 8. MODAL: CERTIFICATE OF AUTHENTICITY (COA) -->
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

    <!-- 9. TOAST NOTIFICATION COMPONENT -->
    <div id="kuro-toast" class="fixed bottom-6 right-6 z-50 glass-kuro border border-amber-400/80 bg-stone-950/95 text-amber-200 px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 opacity-0 translate-y-4 pointer-events-none transition-all duration-300">
        <i id="kuro-toast-icon" data-lucide="info" class="w-4 h-4 text-amber-400 shrink-0"></i>
        <span id="kuro-toast-text" class="text-xs font-medium">Notifikasi</span>
    </div>

    <!-- Main Client Script -->
    <script src="assets/js/kuro.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
