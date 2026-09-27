/**
 * KURO ATELIER - Main Client Script
 * Direct Concierge, Bespoke 1-of-1 Showcase, Series 24 (Cart & Checkout Review), and Private Whitelist
 */

const KuroApp = {
    state: {
        currentUser: null,
        isWhitelisted: false,
        products: [],
        activeCategory: 'all',
        activeProduct: null,
        cart: { items: [], total_items: 0, subtotal: 0, subtotal_formatted: 'Rp 0' },
        conciergeMessages: [],
        conciergePollTimer: null,
        audioEnabled: false,
        audioCtx: null,
        adminOrders: [],
        adminProducts: [],
        adminActiveTab: 'orders',
        searchDebounceTimer: null
    },

    init: async function() {
        console.log("Kuro Atelier initializing...");
        await this.checkSession();
        await this.loadProducts();
        await this.loadCart();
        this.setupEventListeners();
        if (window.lucide) {
            lucide.createIcons();
        }
    },

    // ----------------------------------------------------
    // Zen Sound Synthesizer (Web Audio API)
    // ----------------------------------------------------
    initAudio: function() {
        if (!this.state.audioCtx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            this.state.audioCtx = new AudioContext();
        }
    },

    playZenChime: function(type = 'bell') {
        if (!this.state.audioEnabled) return;
        try {
            this.initAudio();
            const ctx = this.state.audioCtx;
            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            if (type === 'bell') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(528, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(264, ctx.currentTime + 2.5);

                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 2.5);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 2.5);
            } else if (type === 'chime') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(852, ctx.currentTime);
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 1.2);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 1.2);
            }
        } catch (e) {
            console.warn("Audio synth warning:", e);
        }
    },

    toggleAudio: function() {
        this.state.audioEnabled = !this.state.audioEnabled;
        const btn = document.getElementById('audio-toggle-btn');
        if (btn) {
            btn.innerHTML = this.state.audioEnabled 
                ? '<i data-lucide="volume-2" class="w-4 h-4 text-amber-400"></i><span class="text-xs text-amber-300 font-serif-luxury tracking-widest hidden md:inline">ZEN AUDIO ON</span>'
                : '<i data-lucide="volume-x" class="w-4 h-4 text-stone-500"></i><span class="text-xs text-stone-400 font-serif-luxury tracking-widest hidden md:inline">AUDIO OFF</span>';
            lucide.createIcons();
        }
        if (this.state.audioEnabled) {
            this.playZenChime('bell');
        }
    },

    escapeHtml: function(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    },

    // ----------------------------------------------------
    // Session & Auth
    // ----------------------------------------------------
    checkSession: async function() {
        try {
            const res = await fetch('api/auth.php?action=me');
            const data = await res.json();
            if (data.success) {
                this.state.currentUser = data.data.user;
                this.state.isWhitelisted = data.data.is_whitelisted;
                this.renderUserBadge();
            }
        } catch (e) {
            console.error("Failed to check session", e);
        }
    },

    renderUserBadge: function() {
        const badgeEl = document.getElementById('user-session-badge');
        const adminBtn = document.getElementById('nav-admin-link');
        const user = this.state.currentUser;

        if (!badgeEl) return;

        if (user) {
            const isKuro = user.role === 'kuro_admin';
            const isApproved = user.membership_status === 'approved';
            
            let statusColor = isKuro ? 'text-amber-300 border-amber-500/50 bg-amber-950/30' : (isApproved ? 'text-emerald-300 border-emerald-500/50 bg-emerald-950/30' : 'text-stone-300 border-stone-700 bg-stone-900');
            let statusText = isKuro ? 'KURO MASTER' : (isApproved ? 'SOVEREIGN VIP' : 'ANGGOTA');

            badgeEl.innerHTML = `
                <div class="flex items-center gap-2 border ${statusColor} px-3 py-1.5 rounded-full text-xs tracking-wider">
                    <span class="w-2 h-2 rounded-full ${isApproved || isKuro ? 'bg-amber-400 animate-ping' : 'bg-emerald-400'}"></span>
                    <a href="kolektor.php" class="font-semibold text-white hover:text-amber-300 transition-colors" title="Buka Portal Saya">${user.name.split(' ')[0]}</a>
                    <span class="text-[10px] opacity-75 font-mono">(${statusText})</span>
                    ${isKuro ? `
                        <a href="admin.php" title="Panel Kurasi Admin" class="ml-1 text-amber-400 hover:text-amber-200 transition-colors">
                            <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                        </a>
                    ` : `
                        <a href="kolektor.php" title="Portal Kolektor" class="ml-1 text-stone-400 hover:text-white transition-colors">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i>
                        </a>
                    `}
                    <button onclick="KuroApp.logout()" title="Keluar Sesi" class="ml-1 text-stone-400 hover:text-rose-400 transition-colors">
                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            `;

            if (adminBtn) {
                if (isKuro) adminBtn.classList.remove('hidden');
                else adminBtn.classList.add('hidden');
            }
        } else {
            badgeEl.innerHTML = `
                <div class="flex items-center gap-2">
                    <button onclick="KuroApp.openLoginModal()" class="text-xs px-3.5 py-1.5 rounded-full text-stone-300 hover:text-amber-200 border border-stone-800 hover:border-amber-500/40 transition-colors font-medium">
                        MASUK
                    </button>
                    <a href="whitelist.php" class="btn-outline-gold text-xs px-3.5 py-1.5 rounded-full flex items-center gap-1.5 font-medium">
                        <i data-lucide="key" class="w-3 h-3 text-amber-400"></i>
                        <span>AKSES VIP</span>
                    </a>
                </div>
            `;
            if (adminBtn) adminBtn.classList.add('hidden');
        }
        lucide.createIcons();
    },

    logout: async function() {
        this.playZenChime('chime');
        await fetch('api/auth.php?action=logout', { method: 'POST' });
        this.state.currentUser = null;
        this.state.isWhitelisted = false;
        this.state.cart = { items: [], total_items: 0, subtotal: 0, subtotal_formatted: 'Rp 0' };
        this.updateCartBadge();
        this.renderUserBadge();
        await this.loadProducts();
        this.showToast('Sesi telah diakhiri. Kembali ke mode Tamu.', 'info');
    },

    // ----------------------------------------------------
    // Login & Register Modal
    // ----------------------------------------------------
    openLoginModal: function(alertMsg = null) {
        this.playZenChime('chime');
        const modal = document.getElementById('auth-modal');
        const alertBox = document.getElementById('auth-modal-alert');
        const formLogin = document.getElementById('form-auth-login');
        const formRegister = document.getElementById('form-auth-register');

        // Always ensure form inputs are completely blank
        if (formLogin) {
            formLogin.reset();
            const emailInput = formLogin.querySelector('input[name="email"]');
            const passInput = formLogin.querySelector('input[name="password"]');
            if (emailInput) emailInput.value = '';
            if (passInput) passInput.value = '';
        }
        if (formRegister) {
            formRegister.reset();
        }

        this.switchAuthTab('login');

        if (modal) {
            if (alertMsg && alertBox) {
                alertBox.textContent = alertMsg;
                alertBox.classList.remove('hidden');
            } else if (alertBox) {
                alertBox.classList.add('hidden');
            }
            modal.classList.remove('hidden');
        }
    },

    closeLoginModal: function() {
        const modal = document.getElementById('auth-modal');
        if (modal) modal.classList.add('hidden');
        const formLogin = document.getElementById('form-auth-login');
        if (formLogin) {
            formLogin.reset();
            const emailInput = formLogin.querySelector('input[name="email"]');
            const passInput = formLogin.querySelector('input[name="password"]');
            if (emailInput) emailInput.value = '';
            if (passInput) passInput.value = '';
        }
    },

    switchAuthTab: function(tab) {
        const tabLogin = document.getElementById('tab-auth-login');
        const tabRegister = document.getElementById('tab-auth-register');
        const formLogin = document.getElementById('form-auth-login');
        const formRegister = document.getElementById('form-auth-register');

        if (tab === 'login') {
            tabLogin.classList.add('border-b-2', 'border-amber-400', 'text-amber-300');
            tabLogin.classList.remove('text-stone-400');
            tabRegister.classList.remove('border-b-2', 'border-amber-400', 'text-amber-300');
            tabRegister.classList.add('text-stone-400');
            formLogin.classList.remove('hidden');
            formRegister.classList.add('hidden');
        } else {
            tabRegister.classList.add('border-b-2', 'border-amber-400', 'text-amber-300');
            tabRegister.classList.remove('text-stone-400');
            tabLogin.classList.remove('border-b-2', 'border-amber-400', 'text-amber-300');
            tabLogin.classList.add('text-stone-400');
            formRegister.classList.remove('hidden');
            formLogin.classList.add('hidden');
        }
    },

    submitLogin: async function(e) {
        if (e) e.preventDefault();
        const form = document.getElementById('form-auth-login');
        const email = form.elements['email'].value.trim();
        const password = form.elements['password'].value.trim();

        if (!email || !password) {
            this.showToast('Email dan kata sandi wajib diisi.', 'error');
            return;
        }

        try {
            const res = await fetch('api/auth.php?action=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const data = await res.json();
            if (data.success) {
                this.playZenChime('bell');
                this.showToast(data.message, 'success');
                this.closeLoginModal();
                await this.checkSession();
                await this.loadProducts();
                await this.loadCart();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal melakukan login.', 'error');
        }
    },

    submitRegister: async function(e) {
        if (e) e.preventDefault();
        const form = document.getElementById('form-auth-register');
        const name = form.elements['name'].value.trim();
        const email = form.elements['email'].value.trim();
        const password = form.elements['password'].value.trim();

        if (!name || !email || !password) {
            this.showToast('Nama, email, dan kata sandi wajib diisi.', 'error');
            return;
        }

        try {
            const res = await fetch('api/auth.php?action=register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email, password })
            });
            const data = await res.json();
            if (data.success) {
                this.playZenChime('bell');
                this.showToast(data.message, 'success');
                this.closeLoginModal();
                await this.checkSession();
                await this.loadProducts();
                await this.loadCart();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal membuat akun.', 'error');
        }
    },

    // ----------------------------------------------------
    // Products & Bespoke Showcase
    // ----------------------------------------------------
    loadProducts: async function() {
        try {
            const res = await fetch('api/products.php?action=list');
            const data = await res.json();
            if (data.success) {
                this.state.products = data.data.products;
                this.state.isWhitelisted = data.data.is_whitelisted;
                this.renderShowroom();
                this.renderPrivateNotice();
            }
        } catch (e) {
            console.error("Failed to load products", e);
        }
    },

    setCategoryFilter: function(cat) {
        this.playZenChime('chime');
        this.state.activeCategory = cat;
        
        ['all', 'series24', 'bespoke1of1'].forEach(c => {
            const btn = document.getElementById(`filter-cat-${c}`);
            if (btn) {
                if (c === cat) {
                    btn.className = 'px-4 py-2 rounded-xl text-xs font-serif-luxury font-bold bg-amber-500/20 text-amber-300 border border-amber-400/50 shadow-md';
                } else {
                    btn.className = 'px-4 py-2 rounded-xl text-xs font-serif-luxury text-stone-400 hover:text-white border border-stone-800 hover:border-stone-700 bg-stone-900/50 transition-colors';
                }
            }
        });

        this.renderShowroom();
    },

    renderPrivateNotice: function() {
        const banner = document.getElementById('whitelist-alert-banner');
        if (!banner) return;

        if (this.state.isWhitelisted) {
            banner.innerHTML = `
                <div class="glass-kuro border border-amber-500/40 rounded-xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-amber-500/10 border border-amber-400/40 flex items-center justify-center text-amber-400">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-serif-luxury text-amber-300 font-bold text-sm tracking-wider">HAK AKSES SOVEREIGN COLLECTOR AKTIF</h4>
                            <p class="text-xs text-stone-400">Anda memiliki izin privat penuh untuk melihat valuasi, komposisi minyak langka 1-of-1, dan bebas membeli Kuro Series 24.</p>
                        </div>
                    </div>
                    <button onclick="KuroApp.openConcierge()" class="btn-gold px-4 py-2 rounded-lg text-xs flex items-center gap-2 whitespace-nowrap">
                        <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                        <span>DIRECT CONCIERGE KURO</span>
                    </button>
                </div>
            `;
        } else {
            banner.innerHTML = `
                <div class="glass-kuro border border-amber-500/20 rounded-xl p-4 flex flex-col md:flex-row items-center justify-between gap-4 bg-gradient-to-r from-stone-950 via-stone-900 to-black">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-amber-500/5 border border-amber-500/20 flex items-center justify-center text-amber-500">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-serif-luxury text-stone-200 font-semibold text-sm tracking-wider flex items-center gap-2">
                                <span>KURO SERIES 24 TELAH DIBUKA UNTUK ANGGOTA TERDAFTAR</span>
                                <span class="bg-amber-900/40 text-amber-400 border border-amber-500/30 text-[10px] px-2 py-0.5 rounded-full font-mono">SERIES 24 AVAILABLE</span>
                            </h4>
                            <p class="text-xs text-stone-400">Siapapun dapat membeli koleksi Kuro Series 24 dengan syarat masuk (login). Mahakarya 1-of-1 tetap memerlukan kurasi Whitelist eksklusif.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        ${!this.state.currentUser ? `
                            <button onclick="KuroApp.openLoginModal('Silakan masuk untuk membeli botol Series 24.')" class="btn-gold px-4 py-2 rounded-lg text-xs flex items-center gap-1.5 whitespace-nowrap">
                                <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                                <span>MASUK UNTUK MEMBELI</span>
                            </button>
                        ` : ''}
                        <button onclick="KuroApp.openWhitelistModal('invite')" class="btn-outline-gold px-4 py-2 rounded-lg text-xs flex items-center gap-1.5 whitespace-nowrap">
                            <i data-lucide="key" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>AKSES 1-OF-1</span>
                        </button>
                    </div>
                </div>
            `;
        }
        lucide.createIcons();
    },

    renderShowroom: function() {
        const container = document.getElementById('showroom-grid');
        if (!container) return;

        let filtered = this.state.products;
        if (this.state.activeCategory === 'series24') {
            filtered = filtered.filter(p => p.edition_type === 'Series-24');
        } else if (this.state.activeCategory === 'bespoke1of1') {
            filtered = filtered.filter(p => p.edition_type === '1-of-1');
        }

        if (filtered.length === 0) {
            container.innerHTML = `<div class="col-span-3 text-center py-12 text-stone-500">Tidak ada produk dalam kategori ini.</div>`;
            return;
        }

        container.innerHTML = filtered.map(p => {
            const isSeries24 = (p.edition_type === 'Series-24');
            const isAcquired = (p.status === 'acquired' || (isSeries24 && p.stock <= 0));
            const isReserved = (p.status === 'reserved');
            const isAvailable = (!isAcquired && !isReserved);

            // Badge Top Left
            let editionBadge = '';
            if (isSeries24) {
                editionBadge = `
                    <span class="bg-gradient-to-r from-amber-500/20 to-amber-600/30 text-amber-300 border border-amber-400/50 text-[10px] font-mono px-2.5 py-1 rounded-full uppercase tracking-wider font-bold shadow-lg flex items-center gap-1">
                        <i data-lucide="layers" class="w-3 h-3 text-amber-400"></i>
                        <span>SERIES 24 (BATCH 24)</span>
                    </span>
                `;
            } else {
                editionBadge = `
                    <span class="badge-one-of-one shadow-lg">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                        <span>1-OF-1 GLOBAL EDITION</span>
                    </span>
                `;
            }

            // Status Badge Top Right
            let statusBadge = '';
            if (isAcquired) {
                statusBadge = `<span class="bg-rose-950/80 text-rose-400 border border-rose-800/50 text-[10px] font-mono px-3 py-1 rounded-full uppercase tracking-wider">HABIS / VAULTED</span>`;
            } else if (isSeries24) {
                statusBadge = `<span class="bg-amber-950/80 text-amber-300 border border-amber-600/50 text-[10px] font-mono px-3 py-1 rounded-full uppercase tracking-wider">SISA ${p.stock}/24 BOTOL</span>`;
            } else if (isReserved) {
                statusBadge = `<span class="bg-amber-950/80 text-amber-400 border border-amber-800/50 text-[10px] font-mono px-3 py-1 rounded-full uppercase tracking-wider">RESERVED</span>`;
            } else {
                statusBadge = `<span class="bg-emerald-950/80 text-emerald-400 border border-emerald-800/50 text-[10px] font-mono px-3 py-1 rounded-full uppercase tracking-wider">TERSEDIA</span>`;
            }

            // Price Display
            let priceDisplay = '';
            if (isSeries24) {
                priceDisplay = `<div class="font-serif-luxury text-xl md:text-2xl font-bold text-gold-gradient">${p.price_formatted}</div>`;
            } else {
                priceDisplay = this.state.isWhitelisted 
                    ? `<div class="font-serif-luxury text-xl md:text-2xl font-bold text-gold-gradient">${p.price_formatted}</div>`
                    : `
                        <div class="flex items-center gap-2 text-stone-400 text-xs">
                            <i data-lucide="lock" class="w-4 h-4 text-amber-500"></i>
                            <span class="blur-[3px] select-none text-amber-200">Rp 120.000.000</span>
                            <span class="text-[10px] text-amber-400/90 font-mono tracking-widest">(KHUSUS WHITELIST)</span>
                        </div>
                    `;
            }

            // Action Button
            let actionButton = '';
            if (isSeries24) {
                if (!this.state.currentUser) {
                    actionButton = `
                        <button onclick="KuroApp.openLoginModal('Silakan masuk ke akun Anda untuk berbelanja Kuro Series 24.')" class="btn-outline-gold w-full py-2.5 rounded-lg text-xs flex items-center justify-center gap-2">
                            <i data-lucide="log-in" class="w-4 h-4 text-amber-400"></i>
                            <span>MASUK UNTUK BELI</span>
                        </button>
                    `;
                } else if (p.stock > 0) {
                    actionButton = `
                        <button onclick="KuroApp.addToCart(${p.id})" class="btn-gold w-full py-2.5 rounded-lg text-xs flex items-center justify-center gap-2">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                            <span>+ KERANJANG</span>
                        </button>
                    `;
                } else {
                    actionButton = `<span class="text-stone-500 text-xs text-center border border-stone-800 p-2 rounded-lg">Stok Alokasi Habis</span>`;
                }
            } else {
                // 1-of-1 flow
                if (this.state.isWhitelisted) {
                    actionButton = isAvailable 
                        ? `<button onclick="KuroApp.openCheckout(${p.id})" class="btn-gold w-full py-2.5 rounded-lg text-xs flex items-center justify-center gap-2">
                             <i data-lucide="gem" class="w-4 h-4"></i>
                             <span>AKUISISI 1-OF-1</span>
                           </button>`
                        : `<button onclick="KuroApp.openConcierge(${p.id})" class="btn-outline-gold w-full py-2.5 rounded-lg text-xs flex items-center justify-center gap-2">
                             <i data-lucide="message-square" class="w-4 h-4"></i>
                             <span>KONSULTASI</span>
                           </button>`;
                } else {
                    actionButton = `
                        <button onclick="KuroApp.openWhitelistModal('invite')" class="btn-outline-gold w-full py-2.5 rounded-lg text-xs flex items-center justify-center gap-2">
                            <i data-lucide="shield" class="w-4 h-4 text-amber-400"></i>
                            <span>BUKA AKSES VIP</span>
                        </button>
                    `;
                }
            }

            return `
                <div class="glass-kuro-card rounded-2xl overflow-hidden flex flex-col group ${isSeries24 ? 'border-amber-500/30' : ''}">
                    <!-- Media Showcase -->
                    <div class="relative overflow-hidden aspect-[4/3] bg-stone-950">
                        <img src="${p.image_url}" alt="${p.name}" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700 ease-out">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#070708] via-transparent to-transparent opacity-80"></div>
                        
                        <div class="absolute top-4 left-4 right-4 flex items-center justify-between gap-2">
                            ${editionBadge}
                            ${statusBadge}
                        </div>

                        <div class="absolute bottom-3 left-4">
                            <span class="font-mono text-[11px] text-amber-200/80 bg-black/60 px-2 py-0.5 rounded border border-amber-500/20">
                                ${p.edition_serial}
                            </span>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex items-baseline justify-between mb-1">
                                <span class="font-kanji text-amber-400/80 text-sm tracking-widest">${p.japanese_name}</span>
                                <span class="text-[11px] text-stone-400 uppercase tracking-widest font-mono">${p.volume_ml}ML • ${p.concentration.split(' ')[0]}</span>
                            </div>
                            <h3 class="font-serif-luxury text-xl font-bold text-white group-hover:text-amber-200 transition-colors">${p.name}</h3>
                            <p class="text-xs text-amber-200/60 font-editorial italic mb-2">${p.subtitle}</p>
                            <p class="text-stone-300 text-xs line-clamp-3 leading-relaxed font-light">${p.description}</p>
                        </div>

                        <!-- Notes Preview -->
                        <div class="border-t border-b border-stone-800/80 py-3 my-2 space-y-1.5">
                            <div class="text-[10px] text-stone-500 uppercase tracking-widest font-mono">PIRAMIDA AROMA UTAMA</div>
                            <div class="flex flex-wrap gap-1.5">
                                ${(p.notes && p.notes.heart ? p.notes.heart.slice(0, 2) : []).map(n => `
                                    <span class="text-[11px] bg-stone-900 border border-amber-500/20 text-amber-200/90 px-2 py-0.5 rounded-md">
                                        ${n.note_name}
                                    </span>
                                `).join('')}
                                ${(p.notes && p.notes.base ? p.notes.base.slice(0, 1) : []).map(n => `
                                    <span class="text-[11px] bg-stone-900 border border-stone-700 text-stone-300 px-2 py-0.5 rounded-md">
                                        ${n.note_name}
                                    </span>
                                `).join('')}
                            </div>
                        </div>

                        <!-- Price & Actions -->
                        <div class="pt-1 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-stone-500 uppercase tracking-wider font-mono">VALUASI KOLEKSI</span>
                                ${priceDisplay}
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <button onclick="KuroApp.openDetailModal(${p.id})" class="btn-outline-gold py-2 rounded-lg text-xs flex items-center justify-center gap-1.5">
                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-amber-400"></i>
                                    <span>INSPEKSI</span>
                                </button>
                                ${actionButton}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        lucide.createIcons();
    },

    // ----------------------------------------------------
    // Shopping Cart (Series 24)
    // ----------------------------------------------------
    loadCart: async function() {
        try {
            const res = await fetch('api/cart.php?action=get');
            const data = await res.json();
            if (data.success) {
                this.state.cart = data.data;
                this.updateCartBadge();
                this.renderCartDrawer();
            }
        } catch (e) {
            console.error("Cart load failed", e);
        }
    },

    updateCartBadge: function() {
        const badge = document.getElementById('nav-cart-count');
        if (badge) {
            badge.textContent = this.state.cart.total_items || 0;
            if (this.state.cart.total_items > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    },

    addToCart: async function(productId, qty = 1) {
        if (!this.state.currentUser) {
            this.showToast('Silakan masuk ke akun Anda terlebih dahulu untuk membeli Series 24.', 'info');
            this.openLoginModal('Silakan masuk ke akun Anda untuk menambahkan produk ke keranjang belanja.');
            return;
        }
        this.playZenChime('chime');
        try {
            const res = await fetch('api/cart.php?action=add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: qty })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message, 'success');
                await this.loadCart();
                this.openCartDrawer();
            } else {
                if (res.status === 401) {
                    this.openLoginModal('Silakan masuk ke akun Anda terlebih dahulu.');
                }
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal menambahkan ke keranjang.', 'error');
        }
    },

    updateCartItemQty: async function(cartItemId, newQty) {
        try {
            const res = await fetch('api/cart.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart_item_id: cartItemId, quantity: newQty })
            });
            const data = await res.json();
            if (data.success) {
                await this.loadCart();
            }
        } catch (e) {
            console.error(e);
        }
    },

    removeCartItem: async function(cartItemId) {
        this.playZenChime('chime');
        try {
            const res = await fetch('api/cart.php?action=remove', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart_item_id: cartItemId })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message, 'info');
                await this.loadCart();
            }
        } catch (e) {
            console.error(e);
        }
    },

    openCartDrawer: function() {
        this.playZenChime('chime');
        const drawer = document.getElementById('cart-drawer');
        if (drawer) {
            drawer.classList.remove('hidden');
            this.renderCartDrawer();
        }
    },

    closeCartDrawer: function() {
        const drawer = document.getElementById('cart-drawer');
        if (drawer) drawer.classList.add('hidden');
    },

    renderCartDrawer: function() {
        const container = document.getElementById('cart-drawer-items');
        const subtotalEl = document.getElementById('cart-drawer-subtotal');
        const checkoutBtn = document.getElementById('cart-drawer-checkout-btn');
        if (!container) return;

        const cart = this.state.cart;
        if (subtotalEl) subtotalEl.textContent = cart.subtotal_formatted || 'Rp 0';

        if (!cart.items || cart.items.length === 0) {
            container.innerHTML = `
                <div class="py-16 text-center space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-full bg-stone-900 border border-stone-800 flex items-center justify-center text-stone-500">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    </div>
                    <div class="text-stone-400 font-serif-luxury text-sm">Keranjang Belanja Kosong</div>
                    <p class="text-xs text-stone-500 max-w-xs mx-auto">Pilih karya Kuro Series 24 di ruang pamer untuk memulai pesanan Anda.</p>
                </div>
            `;
            if (checkoutBtn) checkoutBtn.disabled = true;
            lucide.createIcons();
            return;
        }

        if (checkoutBtn) checkoutBtn.disabled = false;

        container.innerHTML = cart.items.map(item => `
            <div class="glass-kuro p-3 rounded-xl border border-stone-800 flex items-center gap-3">
                <img src="${item.image_url}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg bg-stone-950 border border-stone-800 shrink-0">
                <div class="flex-1 min-w-0">
                    <div class="font-serif-luxury text-xs font-bold text-white truncate">${item.name}</div>
                    <div class="text-[10px] text-amber-400/80 font-mono">${item.edition_serial}</div>
                    <div class="text-xs font-serif-luxury text-gold-gradient mt-1">${item.price_formatted}</div>
                </div>
                <div class="flex flex-col items-end gap-1.5 shrink-0">
                    <div class="flex items-center border border-stone-800 bg-stone-950 rounded-lg text-xs">
                        <button onclick="KuroApp.updateCartItemQty(${item.cart_item_id}, ${item.quantity - 1})" class="px-2 py-1 text-stone-400 hover:text-white">-</button>
                        <span class="px-2 py-1 font-mono text-white">${item.quantity}</span>
                        <button onclick="KuroApp.updateCartItemQty(${item.cart_item_id}, ${item.quantity + 1})" class="px-2 py-1 text-stone-400 hover:text-white">+</button>
                    </div>
                    <button onclick="KuroApp.removeCartItem(${item.cart_item_id})" class="text-[10px] text-stone-500 hover:text-rose-400">
                        Hapus
                    </button>
                </div>
            </div>
        `).join('');

        lucide.createIcons();
    },

    // ----------------------------------------------------
    // Series 24 Checkout Review (Stops before payment method!)
    // ----------------------------------------------------
    openCartCheckout: function() {
        this.closeCartDrawer();
        this.playZenChime('chime');

        if (!this.state.cart.items || this.state.cart.items.length === 0) {
            this.showToast('Keranjang belanja Anda masih kosong.', 'error');
            return;
        }

        const modal = document.getElementById('cart-checkout-modal');
        const itemsContainer = document.getElementById('checkout-review-items');
        const subtotalEl = document.getElementById('checkout-review-subtotal');
        const totalEl = document.getElementById('checkout-review-total');
        const nameInput = document.getElementById('checkout-review-name');

        if (nameInput) {
            nameInput.value = (this.state.currentUser && this.state.currentUser.name) ? this.state.currentUser.name : '';
        }
        if (subtotalEl) subtotalEl.textContent = this.state.cart.subtotal_formatted;
        if (totalEl) totalEl.textContent = this.state.cart.subtotal_formatted;

        if (itemsContainer) {
            itemsContainer.innerHTML = this.state.cart.items.map(item => `
                <div class="flex items-center justify-between text-xs py-2 border-b border-stone-800">
                    <div>
                        <span class="text-white font-medium">${item.name}</span>
                        <span class="text-stone-500 font-mono ml-2">x${item.quantity}</span>
                    </div>
                    <span class="font-serif-luxury text-amber-200">${item.item_total_formatted}</span>
                </div>
            `).join('');
        }

        if (modal) modal.classList.remove('hidden');
        lucide.createIcons();
    },

    closeCartCheckout: function() {
        const modal = document.getElementById('cart-checkout-modal');
        if (modal) modal.classList.add('hidden');
    },

    submitCartCheckout: async function(e) {
        if (e) e.preventDefault();
        const address = document.getElementById('checkout-review-address').value.trim();
        const name = document.getElementById('checkout-review-name').value.trim();
        const phone = document.getElementById('checkout-review-phone').value.trim();
        const notes = document.getElementById('checkout-review-notes').value.trim();

        if (!address || !name) {
            this.showToast('Nama penerima dan alamat pengiriman wajib diisi.', 'error');
            return;
        }

        try {
            const res = await fetch('api/cart.php?action=checkout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    recipient_name: name,
                    recipient_phone: phone,
                    delivery_address: address,
                    order_notes: notes
                })
            });
            const data = await res.json();
            if (data.success) {
                this.playZenChime('bell');
                this.closeCartCheckout();
                this.showToast(data.message, 'success');
                
                // Show Checkout Review Summary Modal
                this.showCheckoutSummary(data.data);

                // Reload cart and products (stock updated)
                await this.loadCart();
                await this.loadProducts();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal memproses checkout.', 'error');
        }
    },

    copyInvoiceNumber: function(invoiceNumber) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(invoiceNumber).then(() => {
                this.showToast('Nomor invoice ' + invoiceNumber + ' telah disalin ke clipboard!', 'success');
            }).catch(() => {
                this.showToast('Nomor invoice: ' + invoiceNumber, 'info');
            });
        } else {
            this.showToast('Nomor invoice: ' + invoiceNumber, 'info');
        }
    },

    showCheckoutSummary: function(orderData) {
        const modal = document.getElementById('order-summary-modal');
        const content = document.getElementById('order-summary-content');
        if (!modal || !content) return;

        const invoiceNum = orderData.invoice_number || ('INV/KURO-' + (orderData.order_number || '2026-001'));
        const orderNum = orderData.order_number || 'KURO-ORD-2026-000000';
        const issuedAt = orderData.issued_at || new Date().toLocaleString('id-ID');
        const invoiceHash = orderData.invoice_hash || 'KURO-SECURE-HASH-FINGERPRINT';

        const itemsHtml = (orderData.items || []).map((item, idx) => `
            <div class="p-3.5 sm:p-4 flex items-center justify-between gap-4 border-b border-stone-800/60 last:border-0 hover:bg-stone-900/30 transition-colors">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="font-mono text-stone-500 text-xs w-5 text-center shrink-0">#${idx + 1}</span>
                    <img src="${item.image_url || 'assets/images/kuro_series24_noir.jpg'}" alt="${item.name}" class="w-12 h-12 rounded-lg object-cover bg-stone-900 border border-stone-800 shrink-0">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-serif-luxury text-xs sm:text-sm font-bold text-white truncate">${item.name}</span>
                            ${item.japanese_name ? `<span class="font-kanji text-[10px] text-amber-400/80">${item.japanese_name}</span>` : ''}
                        </div>
                        <div class="text-[10px] text-stone-400 font-mono mt-0.5">
                            ${item.edition_serial || 'Series 24 Edition'} • ${item.volume_ml || 50}ML (${item.concentration ? item.concentration.split(' ')[0] : 'EDP'})
                        </div>
                        <div class="text-[10px] text-stone-500 font-mono sm:hidden mt-0.5">
                            ${item.price_formatted || formatRupiah(item.price)} x ${item.quantity}
                        </div>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <div class="font-mono text-xs text-stone-400 hidden sm:block">${item.price_formatted || formatRupiah(item.price)} <span class="text-stone-500">x${item.quantity}</span></div>
                    <div class="font-serif-luxury text-xs sm:text-sm font-bold text-gold-gradient">${item.subtotal_formatted || item.item_total_formatted || formatRupiah((item.price || 0) * item.quantity)}</div>
                </div>
            </div>
        `).join('');

        content.innerHTML = `
            <div id="invoice-sheet" class="glass-kuro border border-amber-500/50 rounded-2xl p-5 sm:p-8 relative shadow-2xl space-y-6 max-h-[92vh] overflow-y-auto">
                <!-- Top Action Bar (hidden when printed) -->
                <div class="flex items-center justify-between border-b border-stone-800 pb-3 no-print">
                    <div class="flex items-center gap-2 text-amber-400 text-xs font-mono">
                        <i data-lucide="file-check-2" class="w-4 h-4"></i>
                        <span class="tracking-widest uppercase font-bold">BUKTI INVOICE PESANAN RESMI</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="window.print()" class="btn-outline-gold px-3.5 py-1.5 rounded-lg text-xs flex items-center gap-1.5 hover:border-amber-300">
                            <i data-lucide="printer" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Cetak / Simpan PDF</span>
                        </button>
                        <button onclick="KuroApp.closeOrderSummary()" class="text-stone-400 hover:text-white p-1 rounded-lg transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- Official Invoice Header -->
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b border-stone-800">
                    <!-- Brand & Atelier Info -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 via-amber-500 to-amber-700 flex items-center justify-center font-kanji font-bold text-stone-950 text-2xl shadow-lg border border-amber-300/40 shrink-0">
                                黒
                            </div>
                            <div>
                                <h2 class="font-serif-luxury text-xl font-bold tracking-widest text-white">KURO ATELIER</h2>
                                <p class="text-[10px] text-amber-400/90 font-mono tracking-widest">TOKYO • GINZA • HAUTE PARFUMERIE</p>
                            </div>
                        </div>
                        <div class="text-[11px] text-stone-400 leading-relaxed font-light">
                            Atelier Kuro Ginza 6-Chome, Chuo-ku, Tokyo 104-0061, Japan<br>
                            Layanan Konsinyasi & Alokasi Eksekutif Indonesia<br>
                            <span class="font-mono text-amber-300/70">vip-concierge@atelier-kuro.tokyo</span>
                        </div>
                    </div>

                    <!-- Invoice Identifiers -->
                    <div class="sm:text-right space-y-1.5 bg-black/50 p-4 rounded-xl border border-stone-800/80 shrink-0">
                        <div class="text-[10px] font-mono tracking-widest text-amber-400 uppercase font-bold">FAKTUR / INVOICE PEMESANAN</div>
                        <div class="font-mono text-base font-bold text-white tracking-wider">${invoiceNum}</div>
                        <div class="text-xs text-stone-400">
                            Ref. Pesanan: <span class="font-mono text-amber-200 font-semibold">${orderNum}</span>
                        </div>
                        <div class="text-xs text-stone-400">
                            Waktu Terbit: <span class="text-stone-300 font-mono">${issuedAt}</span>
                        </div>
                        <div class="pt-1.5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono font-bold bg-amber-950/70 border border-amber-500/40 text-amber-300 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                ${orderData.status_label || 'ALOKASI TERKUNCI (MENUNGGU PEMBAYARAN)'}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Customer & Shipping Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="bg-black/50 border border-stone-800/80 p-4 rounded-xl space-y-2">
                        <div class="text-[10px] font-mono uppercase tracking-wider text-amber-400/90 flex items-center gap-1.5 font-bold">
                            <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                            <span>DATA PEMESAN & PENERIMA</span>
                        </div>
                        <div class="space-y-1 leading-relaxed">
                            <div class="text-stone-400">Akun Terdaftar: <strong class="text-white">${orderData.customer_name || 'Budi Pratama'}</strong></div>
                            <div class="text-stone-400">Email: <span class="font-mono text-stone-300">${orderData.customer_email || 'gratis@kuro.com'}</span></div>
                            <div class="text-stone-400 pt-1.5 border-t border-stone-800">
                                Penerima Paket: <strong class="text-amber-200">${orderData.recipient_name}</strong>
                            </div>
                            <div class="text-stone-400">Kontak: <span class="font-mono text-stone-300">${orderData.recipient_phone || '-'}</span></div>
                        </div>
                    </div>

                    <div class="bg-black/50 border border-stone-800/80 p-4 rounded-xl space-y-2">
                        <div class="text-[10px] font-mono uppercase tracking-wider text-amber-400/90 flex items-center gap-1.5 font-bold">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                            <span>DESTINASI & METODE PENGIRIMAN</span>
                        </div>
                        <div class="space-y-1.5 leading-relaxed">
                            <div class="text-stone-400">Alamat Kirim:</div>
                            <div class="text-stone-200 bg-stone-950/70 p-2.5 rounded-lg border border-stone-800/60 leading-relaxed font-light text-[11px]">
                                ${orderData.delivery_address}
                            </div>
                            <div class="text-[11px] text-stone-400">
                                Catatan Khusus: <span class="italic text-stone-300">${orderData.order_notes || '-'}</span>
                            </div>
                            <div class="text-[11px] text-amber-300/80 flex items-center gap-1.5 pt-0.5">
                                <i data-lucide="shield" class="w-3 h-3 text-amber-400 shrink-0"></i>
                                <span>VIP White-Glove Hand Courier (Private Escort)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Itemized Products Table -->
                <div class="border border-stone-800 rounded-xl overflow-hidden bg-black/40">
                    <div class="px-4 py-2.5 bg-stone-950 border-b border-stone-800 text-[10px] font-mono text-stone-400 uppercase tracking-wider flex items-center justify-between">
                        <span>RINCIAN KARYA OLFAKTORI (SERIES 24)</span>
                        <span class="text-amber-300 font-bold">${orderData.items_count || (orderData.items ? orderData.items.length : 1)} ITEM TERKUNCI</span>
                    </div>
                    <div>
                        ${itemsHtml}
                    </div>
                </div>

                <!-- Financial Breakdown & Hanko Seal -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-5 pt-1 items-center">
                    <!-- Hanko Seal & Authenticity Notice -->
                    <div class="sm:col-span-7 flex items-center gap-4 bg-black/40 p-4 rounded-xl border border-stone-800">
                        <div class="hanko-seal shrink-0">
                            <span class="text-[11px]">黒工</span>
                            <span class="text-[11px]">房印</span>
                        </div>
                        <div class="text-[11px] space-y-1">
                            <div class="font-serif-luxury font-bold text-white flex items-center gap-1.5">
                                <span>SEGEL RESMI ATELIER KURO</span>
                                <i data-lucide="badge-check" class="w-4 h-4 text-amber-400"></i>
                            </div>
                            <p class="text-stone-400 leading-relaxed font-light">
                                Bukti invoice ini mengonfirmasi alokasi resmi botol Series 24. Stok telah dikurangi dari batch 24 unit global.
                            </p>
                            <div class="font-mono text-[9px] text-stone-500 truncate max-w-xs" title="${invoiceHash}">
                                SHA256: ${invoiceHash}
                            </div>
                        </div>
                    </div>

                    <!-- Totals Table -->
                    <div class="sm:col-span-5 space-y-2 text-xs bg-black/40 p-4 rounded-xl border border-stone-800">
                        <div class="flex items-center justify-between text-stone-400">
                            <span>Subtotal Produk:</span>
                            <span class="text-white font-mono">${orderData.subtotal_formatted || orderData.total_formatted}</span>
                        </div>
                        <div class="flex items-center justify-between text-stone-400">
                            <span>Pengiriman VIP Escort:</span>
                            <span class="text-emerald-400 font-mono font-semibold">GRATIS</span>
                        </div>
                        <div class="flex items-center justify-between text-stone-400">
                            <span>Pajak & Asuransi Nilai:</span>
                            <span class="text-stone-300 font-mono">Termasuk (0%)</span>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-stone-800 font-bold">
                            <span class="text-stone-200">TOTAL INVOICE:</span>
                            <span class="font-serif-luxury text-lg sm:text-xl text-gold-gradient font-bold">${orderData.total_formatted}</span>
                        </div>
                    </div>
                </div>

                <!-- Notice on Checkout Stage Restriction -->
                <div class="bg-amber-950/25 border border-amber-500/30 rounded-xl p-3.5 flex items-start gap-2.5 text-xs text-amber-200/90 leading-relaxed">
                    <i data-lucide="info" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                    <div>
                        <strong>Protokol Transaksi Kuro:</strong>
                        Sesuai alur sistem, transaksi ini telah selesai di tahap <strong>Checkout</strong> dan bukti invoice ini sah sebagai konfirmasi alokasi pesanan Anda. Pilihan dan instruksi metode pembayaran akan diberikan pada tahap lanjutan.
                    </div>
                </div>

                <!-- Footer Actions (hidden when printed) -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 no-print border-t border-stone-800">
                    <button onclick="KuroApp.copyInvoiceNumber('${invoiceNum}')" class="text-xs text-stone-400 hover:text-amber-300 flex items-center gap-1.5 transition-colors">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        <span>Salin No. Invoice (${invoiceNum})</span>
                    </button>
                    <div class="flex items-center gap-3">
                        <button onclick="window.print()" class="btn-outline-gold px-4 py-2.5 rounded-xl text-xs flex items-center gap-2">
                            <i data-lucide="printer" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Cetak / Simpan PDF</span>
                        </button>
                        <button onclick="KuroApp.closeOrderSummary()" class="btn-gold px-6 py-2.5 rounded-xl text-xs font-bold shadow-lg">
                            SELESAI & TUTUP
                        </button>
                    </div>
                </div>
            </div>
        `;

        modal.classList.remove('hidden');
        lucide.createIcons();
    },

    closeOrderSummary: function() {
        const modal = document.getElementById('order-summary-modal');
        if (modal) modal.classList.add('hidden');
    },

    // ----------------------------------------------------
    // Product Detail & Olfactory Pyramid Modal
    // ----------------------------------------------------
    openDetailModal: async function(productId) {
        this.playZenChime('chime');
        const modal = document.getElementById('product-detail-modal');
        const body = document.getElementById('product-detail-body');
        if (!modal || !body) return;

        body.innerHTML = `<div class="p-12 text-center text-amber-400">Menghubungkan ke arsip karya Kuro...</div>`;
        modal.classList.remove('hidden');

        try {
            const res = await fetch(`api/products.php?action=detail&id=${productId}`);
            const data = await res.json();
            if (data.success) {
                const p = data.data.product;
                this.state.activeProduct = p;
                const pyr = p.notes_pyramid;
                const isSeries24 = (p.edition_type === 'Series-24');

                body.innerHTML = `
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        <div class="lg:col-span-5 space-y-4">
                            <div class="relative rounded-2xl overflow-hidden aspect-[4/5] bg-stone-950 border border-amber-500/30">
                                <img src="${p.image_url}" alt="${p.name}" class="w-full h-full object-cover">
                                <div class="absolute top-4 left-4">
                                    ${isSeries24 
                                        ? `<span class="bg-amber-950 text-amber-300 border border-amber-500 text-xs px-3 py-1 rounded-full font-mono font-bold">SERIES 24 (BATCH 24)</span>`
                                        : `<span class="badge-one-of-one shadow-xl">1-OF-1 GLOBAL EDITION</span>`
                                    }
                                </div>
                                <div class="absolute bottom-4 left-4 right-4 bg-black/80 backdrop-blur-md p-3 rounded-xl border border-amber-500/20 text-xs">
                                    <div class="font-mono text-amber-300 font-bold mb-0.5">${p.edition_serial}</div>
                                    <div class="text-[11px] text-stone-400">${isSeries24 ? `Alokasi Tersisa: ${p.stock} dari 24 botol` : 'Registrasi Tunggal Dunia'}</div>
                                </div>
                            </div>

                            <div class="glass-kuro p-4 rounded-xl space-y-2 border-gold-subtle">
                                <h4 class="text-xs font-mono text-amber-400 uppercase tracking-widest">KEAHLIAN KERAJINAN BOTOL</h4>
                                <p class="text-xs text-stone-300 leading-relaxed font-light">${p.flacon_craftsmanship}</p>
                            </div>
                        </div>

                        <div class="lg:col-span-7 space-y-6">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-kanji text-amber-400 text-lg">${p.japanese_name}</span>
                                    <span class="text-xs font-mono text-stone-400 bg-stone-900 px-2 py-0.5 rounded border border-stone-800">${p.concentration}</span>
                                </div>
                                <h2 class="font-serif-luxury text-3xl font-bold text-white mb-1">${p.name}</h2>
                                <p class="text-sm font-editorial italic text-amber-200/80 mb-4">${p.subtitle}</p>
                                
                                <div class="glass-kuro p-4 rounded-xl border-gold-subtle mb-4">
                                    <h4 class="text-xs font-mono text-amber-300 uppercase tracking-wider mb-1">FILOSOFI KARYA</h4>
                                    <p class="text-xs text-stone-300 leading-relaxed font-light">${p.philosophy}</p>
                                </div>
                                
                                <p class="text-xs text-stone-300 leading-relaxed">${p.description}</p>
                            </div>

                            <!-- Piramida Aroma -->
                            <div class="space-y-3">
                                <h4 class="font-serif-luxury text-sm text-amber-300 tracking-wider flex items-center gap-2">
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                    <span>PIRAMIDA OLFAKTORI</span>
                                </h4>
                                
                                <div class="space-y-2.5">
                                    <div class="glass-kuro p-3.5 rounded-xl border-l-2 border-l-amber-300 border-gold-subtle">
                                        <div class="text-xs font-semibold text-amber-200 uppercase mb-1">TOP NOTES (0-30 MENIT)</div>
                                        <div class="space-y-1">
                                            ${pyr.top.map(n => `<div class="text-xs text-stone-300"><strong class="text-amber-100">${n.note_name}:</strong> <span class="text-stone-400 text-[11px]">${n.description || ''}</span></div>`).join('')}
                                        </div>
                                    </div>
                                    <div class="glass-kuro p-3.5 rounded-xl border-l-2 border-l-amber-500 border-gold-subtle">
                                        <div class="text-xs font-semibold text-amber-300 uppercase mb-1">HEART NOTES (1-6 JAM)</div>
                                        <div class="space-y-1">
                                            ${pyr.heart.map(n => `<div class="text-xs text-stone-300"><strong class="text-amber-100">${n.note_name}:</strong> <span class="text-stone-400 text-[11px]">${n.description || ''}</span></div>`).join('')}
                                        </div>
                                    </div>
                                    <div class="glass-kuro p-3.5 rounded-xl border-l-2 border-l-amber-700 border-gold-subtle">
                                        <div class="text-xs font-semibold text-amber-400 uppercase mb-1">BASE NOTES (48+ JAM)</div>
                                        <div class="space-y-1">
                                            ${pyr.base.map(n => `<div class="text-xs text-stone-300"><strong class="text-amber-100">${n.note_name}:</strong> <span class="text-stone-400 text-[11px]">${n.description || ''}</span></div>`).join('')}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Valuation & Actions -->
                            <div class="border-t border-stone-800 pt-4 flex flex-col md:flex-row items-center justify-between gap-4">
                                <div>
                                    <span class="text-[11px] text-stone-400 uppercase tracking-wider font-mono">VALUASI KOLEKSI</span>
                                    <div class="font-serif-luxury text-2xl font-bold text-gold-gradient mt-0.5">${p.price_formatted}</div>
                                </div>
                                <div class="flex items-center gap-2 w-full md:w-auto">
                                    ${isSeries24 ? `
                                        <button onclick="KuroApp.closeDetailModal(); KuroApp.addToCart(${p.id});" class="btn-gold px-6 py-2.5 rounded-lg text-xs flex items-center justify-center gap-2 flex-1 md:flex-initial">
                                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                                            <span>+ TAMBAH KE KERANJANG</span>
                                        </button>
                                    ` : (p.can_acquire ? `
                                        <button onclick="KuroApp.openCheckout(${p.id})" class="btn-gold px-6 py-2.5 rounded-lg text-xs flex items-center justify-center gap-2 flex-1 md:flex-initial">
                                            <i data-lucide="gem" class="w-4 h-4"></i>
                                            <span>PROSES AKUISISI 1-OF-1</span>
                                        </button>
                                    ` : `
                                        <button onclick="KuroApp.openWhitelistModal('invite')" class="btn-gold px-6 py-2.5 rounded-lg text-xs flex items-center justify-center gap-2 flex-1 md:flex-initial">
                                            <i data-lucide="lock" class="w-4 h-4"></i>
                                            <span>BUKA AKSES VIP</span>
                                        </button>
                                    `)}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                lucide.createIcons();
            }
        } catch (e) {
            console.error(e);
            body.innerHTML = `<div class="p-8 text-center text-rose-400">Gagal memuat detail karya.</div>`;
        }
    },

    closeDetailModal: function() {
        const modal = document.getElementById('product-detail-modal');
        if (modal) modal.classList.add('hidden');
    },

    // ----------------------------------------------------
    // Whitelist & Invite Code Modals
    // ----------------------------------------------------
    openWhitelistModal: function(tab = 'invite') {
        this.playZenChime('chime');
        const modal = document.getElementById('whitelist-modal');
        const inviteInput = document.getElementById('invite-code-input');
        if (inviteInput) inviteInput.value = '';
        const applyForm = document.getElementById('whitelist-apply-form');
        if (applyForm) applyForm.reset();

        if (modal) {
            modal.classList.remove('hidden');
            this.switchWhitelistTab(tab);
        }
    },

    closeWhitelistModal: function() {
        const modal = document.getElementById('whitelist-modal');
        if (modal) modal.classList.add('hidden');
    },

    switchWhitelistTab: function(tab) {
        const tabInvite = document.getElementById('tab-invite-btn');
        const tabApply = document.getElementById('tab-apply-btn');
        const viewInvite = document.getElementById('view-invite-code');
        const viewApply = document.getElementById('view-apply-form');

        if (tab === 'invite') {
            tabInvite.classList.add('border-b-2', 'border-amber-400', 'text-amber-300');
            tabInvite.classList.remove('text-stone-400');
            tabApply.classList.remove('border-b-2', 'border-amber-400', 'text-amber-300');
            tabApply.classList.add('text-stone-400');
            viewInvite.classList.remove('hidden');
            viewApply.classList.add('hidden');
        } else {
            tabApply.classList.add('border-b-2', 'border-amber-400', 'text-amber-300');
            tabApply.classList.remove('text-stone-400');
            tabInvite.classList.remove('border-b-2', 'border-amber-400', 'text-amber-300');
            tabInvite.classList.add('text-stone-400');
            viewApply.classList.remove('hidden');
            viewInvite.classList.add('hidden');
        }
    },

    submitInviteCode: async function() {
        const codeInput = document.getElementById('invite-code-input');
        const code = codeInput ? codeInput.value.trim() : '';

        if (!code) {
            this.showToast('Masukkan kode undangan VIP Anda.', 'error');
            return;
        }

        try {
            const res = await fetch('api/whitelist.php?action=verify_code', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: code })
            });
            const data = await res.json();
            if (data.success) {
                this.playZenChime('bell');
                this.showToast(data.message, 'success');
                this.closeWhitelistModal();
                await this.checkSession();
                await this.loadProducts();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal memverifikasi kode undangan.', 'error');
        }
    },

    submitWhitelistApplication: async function(e) {
        if (e) e.preventDefault();
        const form = document.getElementById('whitelist-apply-form');
        if (!form) return;

        const formData = {
            full_name: form.elements['full_name'].value.trim(),
            email: form.elements['email'].value.trim(),
            phone: form.elements['phone'].value.trim(),
            organization_title: form.elements['organization_title'].value.trim(),
            statement_of_intent: form.elements['statement_of_intent'].value.trim(),
            olfactory_preference: form.elements['olfactory_preference'].value.trim()
        };

        if (!formData.full_name || !formData.email || !formData.organization_title || !formData.statement_of_intent) {
            this.showToast('Mohon lengkapi seluruh kolom identitas wajib.', 'error');
            return;
        }

        try {
            const res = await fetch('api/whitelist.php?action=apply', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const data = await res.json();
            if (data.success) {
                this.playZenChime('bell');
                this.showToast(data.message, 'success');
                this.closeWhitelistModal();
                await this.checkSession();
                await this.loadProducts();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal mengirimkan permohonan kurasi.', 'error');
        }
    },

    // ----------------------------------------------------
    // Direct Concierge Live Messaging
    // ----------------------------------------------------
    openConcierge: async function(productId = null) {
        this.playZenChime('chime');
        if (!this.state.currentUser) {
            this.openLoginModal('Silakan masuk terlebih dahulu untuk menghubungi Kuro.');
            return;
        }

        const modal = document.getElementById('concierge-modal');
        if (modal) modal.classList.remove('hidden');

        if (productId) {
            const p = this.state.products.find(item => item.id == productId);
            if (p) {
                const input = document.getElementById('concierge-message-input');
                if (input && !input.value) {
                    input.value = `Konbanwa Kuro-sensei. Saya ingin berkonsultasi mengenai flacon ${p.name} (${p.edition_serial}).`;
                }
            }
        }

        await this.loadConciergeMessages();
        this.loadConciergeTemplates();

        if (this.state.conciergePollTimer) clearInterval(this.state.conciergePollTimer);
        this.state.conciergePollTimer = setInterval(() => {
            this.loadConciergeMessages(false);
        }, 5000);
    },

    closeConcierge: function() {
        const modal = document.getElementById('concierge-modal');
        if (modal) modal.classList.add('hidden');
        if (this.state.conciergePollTimer) {
            clearInterval(this.state.conciergePollTimer);
            this.state.conciergePollTimer = null;
        }
    },

    loadConciergeMessages: async function(scrollToBottom = true) {
        try {
            const res = await fetch('api/concierge.php?action=get_thread');
            const data = await res.json();
            if (data.success) {
                this.state.conciergeMessages = data.data.messages;
                this.renderConciergeMessages(scrollToBottom);
            }
        } catch (e) {
            console.error(e);
        }
    },

    renderConciergeMessages: function(scrollToBottom = true) {
        const container = document.getElementById('concierge-messages-stream');
        if (!container) return;

        if (this.state.conciergeMessages.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 space-y-2">
                    <div class="w-12 h-12 mx-auto rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                        <i data-lucide="message-square" class="w-5 h-5"></i>
                    </div>
                    <div class="text-stone-300 text-xs font-serif-luxury tracking-wider">SALURAN PRIVAT LANGSUNG KE KURO ATELIER</div>
                    <p class="text-stone-500 text-[11px] max-w-sm mx-auto">Sampaikan pertanyaan mengenai kustomisasi aroma, ukiran kanji emas, atau pengawalan kurir pribadi.</p>
                </div>
            `;
            lucide.createIcons();
            return;
        }

        const currentUserId = this.state.currentUser ? this.state.currentUser.id : 0;

        container.innerHTML = this.state.conciergeMessages.map(m => {
            const isMe = (m.sender_id == currentUserId);
            const isKuro = (m.sender_role === 'kuro_admin');

            return `
                <div class="flex flex-col ${isMe ? 'items-end' : 'items-start'} space-y-1">
                    <div class="flex items-center gap-2 text-[10px] text-stone-400 px-1">
                        ${isKuro 
                            ? `<span class="font-serif-luxury text-amber-400 font-semibold flex items-center gap-1">
                                 <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                 <span>${m.sender_name}</span>
                               </span>`
                            : `<span class="text-stone-300 font-medium">${m.sender_name}</span>`
                        }
                        <span class="text-stone-600 font-mono">${m.created_at.substring(11, 16)}</span>
                    </div>
                    <div class="max-w-[85%] md:max-w-[70%] p-3.5 rounded-2xl text-xs leading-relaxed ${
                        isMe 
                            ? 'bg-amber-500/15 border border-amber-500/40 text-stone-100 rounded-tr-none'
                            : (isKuro 
                                ? 'glass-kuro border border-amber-500/30 text-amber-100/90 rounded-tl-none shadow-lg' 
                                : 'bg-stone-900 border border-stone-800 text-stone-200 rounded-tl-none')
                    }">
                        ${m.message}
                    </div>
                </div>
            `;
        }).join('');

        if (scrollToBottom) {
            container.scrollTop = container.scrollHeight;
        }
        lucide.createIcons();
    },

    loadConciergeTemplates: async function() {
        const templateContainer = document.getElementById('concierge-quick-templates');
        if (!templateContainer) return;

        try {
            const res = await fetch('api/concierge.php?action=quick_templates');
            const data = await res.json();
            if (data.success) {
                templateContainer.innerHTML = data.data.templates.map(t => `
                    <button onclick="KuroApp.useConciergeTemplate(\`${t.text.replace(/"/g, '&quot;')}\`)" class="text-left text-[11px] p-2 rounded-lg bg-stone-900/80 hover:bg-amber-950/40 border border-stone-800 hover:border-amber-500/40 text-stone-300 hover:text-amber-200 transition-all flex items-center gap-2">
                        <i data-lucide="corner-down-right" class="w-3 h-3 text-amber-400 shrink-0"></i>
                        <span class="truncate">${t.title}</span>
                    </button>
                `).join('');
                lucide.createIcons();
            }
        } catch (e) {
            console.error(e);
        }
    },

    useConciergeTemplate: function(text) {
        const input = document.getElementById('concierge-message-input');
        if (input) {
            input.value = text;
            input.focus();
        }
    },

    sendConciergeMessage: async function() {
        const input = document.getElementById('concierge-message-input');
        const text = input ? input.value.trim() : '';
        if (!text) return;

        input.value = '';
        this.playZenChime('chime');

        try {
            const res = await fetch('api/concierge.php?action=send_message', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: text,
                    product_id: this.state.activeProduct ? this.state.activeProduct.id : null
                })
            });
            const data = await res.json();
            if (data.success) {
                await this.loadConciergeMessages(true);
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal mengirimkan pesan concierge.', 'error');
        }
    },

    // ----------------------------------------------------
    // High-Value 1-of-1 Checkout & Acquisition
    // ----------------------------------------------------
    openCheckout: function(productId) {
        this.playZenChime('chime');
        if (!this.state.isWhitelisted) {
            this.showToast('Hak istimewa akuisisi 1-of-1 hanya diperuntukkan bagi kolektor yang disetujui Whitelist.', 'info');
            this.openWhitelistModal('invite');
            return;
        }

        const product = this.state.products.find(p => p.id == productId);
        if (!product) return;

        this.state.activeProduct = product;
        const modal = document.getElementById('checkout-modal');
        const content = document.getElementById('checkout-modal-content');
        if (!modal || !content) return;

        content.innerHTML = `
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-5 space-y-4">
                    <div class="relative rounded-xl overflow-hidden aspect-[4/3] bg-stone-950 border border-amber-500/30">
                        <img src="${product.image_url}" alt="${product.name}" class="w-full h-full object-cover">
                        <div class="absolute top-3 left-3">
                            <span class="badge-one-of-one">1-OF-1 EDITION</span>
                        </div>
                    </div>

                    <div class="glass-kuro p-4 rounded-xl space-y-2 border-gold-subtle">
                        <div class="font-kanji text-amber-400 text-xs">${product.japanese_name}</div>
                        <h3 class="font-serif-luxury text-lg font-bold text-white">${product.name}</h3>
                        <div class="font-mono text-xs text-amber-300">${product.edition_serial}</div>
                    </div>

                    <div class="glass-kuro p-4 rounded-xl border border-amber-500/30 bg-amber-950/20">
                        <div class="flex items-center justify-between text-xs text-stone-400 mb-1">
                            <span>Valuasi Mahakarya:</span>
                            <span class="font-mono">${product.price_formatted}</span>
                        </div>
                        <div class="border-t border-amber-500/30 pt-2 flex items-center justify-between">
                            <span class="text-xs font-serif-luxury text-amber-200">TOTAL AKUISISI:</span>
                            <span class="font-serif-luxury text-lg font-bold text-gold-gradient">${product.price_formatted}</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-5">
                    <div>
                        <h3 class="font-serif-luxury text-xl font-bold text-amber-300">AKUISISI EDISI 1-OF-1 GLOBAL</h3>
                        <p class="text-xs text-stone-400 font-light">Flacon ini akan ditutup secara permanen setelah dikonfirmasi.</p>
                    </div>

                    <form id="acquisition-form" onsubmit="KuroApp.submitAcquisition(event)" class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[11px] font-mono text-amber-400 uppercase tracking-wider">Nama Resmi Pemilik Tunggal</label>
                            <input type="text" id="acq-owner-name" value="${this.state.currentUser ? this.state.currentUser.name : ''}" required class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3.5 py-2.5 text-xs text-white outline-none">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] font-mono text-amber-400 uppercase tracking-wider">Ukiran Plat Emas Kustom</label>
                            <input type="text" id="acq-engraving" placeholder="Contoh: Tanaka Daisuke (田中大輔) 2026" class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3.5 py-2.5 text-xs text-white outline-none">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] font-mono text-amber-400 uppercase tracking-wider">Alamat Pengantaran Diplomatik Privat</label>
                            <textarea id="acq-address" rows="2" required placeholder="Alamat kediaman atau kantor pribadi..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-400 rounded-lg px-3.5 py-2 text-xs text-white outline-none resize-none"></textarea>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-mono text-amber-400 uppercase tracking-wider">Metode Pembayaran</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <label class="glass-kuro p-3 rounded-xl border border-amber-500/40 flex items-start gap-2.5 cursor-pointer">
                                    <input type="radio" name="payment_method" value="Executive Wire Transfer" checked class="mt-0.5 text-amber-400">
                                    <div class="text-xs">
                                        <div class="font-semibold text-amber-200">Wire Transfer Eksekutif</div>
                                        <div class="text-[10px] text-stone-400">BCA Prioritas / Mandiri Wealth</div>
                                    </div>
                                </label>
                                <label class="glass-kuro p-3 rounded-xl border border-stone-800 flex items-start gap-2.5 cursor-pointer">
                                    <input type="radio" name="payment_method" value="Private Escrow Limit" class="mt-0.5 text-amber-400">
                                    <div class="text-xs">
                                        <div class="font-semibold text-stone-200">Black Card / Centurion</div>
                                        <div class="text-[10px] text-stone-400">Sovereign Card</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn-gold w-full py-3 rounded-xl text-xs font-bold flex items-center justify-center gap-2">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                                <span>KONFIRMASI AKUISISI 1-OF-1 & TERBITKAN COA</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        modal.classList.remove('hidden');
        lucide.createIcons();
    },

    closeCheckout: function() {
        const modal = document.getElementById('checkout-modal');
        if (modal) modal.classList.add('hidden');
    },

    submitAcquisition: async function(e) {
        if (e) e.preventDefault();
        const product = this.state.activeProduct;
        if (!product) return;

        const address = document.getElementById('acq-address').value.trim();
        const engraving = document.getElementById('acq-engraving').value.trim();
        const paymentMethodEl = document.querySelector('input[name="payment_method"]:checked');
        const paymentMethod = paymentMethodEl ? paymentMethodEl.value : 'Executive Wire Transfer';

        if (!address) {
            this.showToast('Alamat pengantaran diplomatik wajib diisi.', 'error');
            return;
        }

        try {
            const res = await fetch('api/orders.php?action=create_acquisition', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    product_id: product.id,
                    delivery_address: address,
                    custom_engraving: engraving,
                    payment_method: paymentMethod,
                    wire_reference: 'WIRE-' + Math.floor(10000000 + Math.random() * 90000000)
                })
            });
            const data = await res.json();
            if (data.success) {
                this.playZenChime('bell');
                this.closeCheckout();
                this.showToast(data.message, 'success');
                this.showCertificateModal(data.data);
                await this.loadProducts();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal memproses transaksi akuisisi.', 'error');
        }
    },

    // ----------------------------------------------------
    // Certificate of Authenticity (COA) Presentation
    // ----------------------------------------------------
    showCertificateModal: function(orderData) {
        const modal = document.getElementById('coa-modal');
        const body = document.getElementById('coa-modal-body');
        if (!modal || !body) return;

        body.innerHTML = `
            <div class="coa-parchment p-8 md:p-12 rounded-2xl max-w-2xl mx-auto text-center space-y-6">
                <div class="coa-inner-border p-6 rounded-xl space-y-6">
                    <div class="space-y-1">
                        <div class="font-kanji text-amber-400 text-lg tracking-widest">黒 • 日本の香水芸術</div>
                        <h2 class="font-serif-luxury text-2xl md:text-3xl font-extrabold text-gold-gradient tracking-widest">CERTIFICATE OF AUTHENTICITY</h2>
                        <div class="text-[10px] font-mono uppercase tracking-[0.3em] text-stone-400">KURO IMPERIAL ATELIER • TOKYO CHAMBER</div>
                    </div>

                    <div class="w-24 h-0.5 mx-auto bg-gradient-to-r from-transparent via-amber-400 to-transparent"></div>

                    <p class="text-xs md:text-sm text-stone-200 leading-relaxed font-editorial italic max-w-lg mx-auto">
                        Dengan ini dinyatakan secara sah bahwa karya seni wewangian edisi tunggal berikut telah resmi terdaftar atas kepemilikan tunggal dunia:
                    </p>

                    <div class="bg-black/60 border border-amber-500/30 p-4 rounded-xl space-y-2">
                        <div class="font-serif-luxury text-xl font-bold text-amber-200">${orderData.product_name}</div>
                        <div class="font-mono text-xs text-amber-400">${orderData.coa_serial}</div>
                        <div class="text-xs text-stone-300">
                            Pemilik Terdaftar: <strong class="text-white">${this.state.currentUser ? this.state.currentUser.name : 'Sovereign Collector'}</strong>
                        </div>
                    </div>

                    <div class="text-left bg-stone-950/80 p-3 rounded-lg border border-stone-800 text-[10px] font-mono text-stone-400 space-y-1 overflow-x-auto">
                        <div class="text-amber-400/80 uppercase font-semibold">Cryptographic Proof (SHA-256):</div>
                        <div class="text-amber-200 break-all select-all">${orderData.coa_hash}</div>
                    </div>

                    <div class="flex items-center justify-around pt-4">
                        <div class="text-center">
                            <div class="font-editorial italic text-stone-400 text-xs mb-1">Master Perfumer</div>
                            <div class="font-kanji text-amber-300 text-2xl">黒</div>
                            <div class="text-[10px] font-serif-luxury text-stone-500">Kuro, Tokyo</div>
                        </div>

                        <div class="gold-wax-seal">
                            <span class="font-kanji text-black font-bold text-2xl select-none">黒</span>
                        </div>

                        <div class="text-center">
                            <div class="font-editorial italic text-stone-400 text-xs mb-1">Date of Acquisition</div>
                            <div class="font-mono text-amber-300 text-sm">${new Date().toISOString().slice(0, 10)}</div>
                            <div class="text-[10px] font-serif-luxury text-stone-500">Imperial Registry</div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center gap-3 pt-2">
                    <button onclick="window.print()" class="btn-outline-gold px-4 py-2 rounded-lg text-xs flex items-center gap-2">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>CETAK DOKUMEN FISIK</span>
                    </button>
                    <button onclick="KuroApp.closeCertificateModal()" class="btn-gold px-6 py-2 rounded-lg text-xs font-bold">
                        TUTUP ARSIP
                    </button>
                </div>
            </div>
        `;

        modal.classList.remove('hidden');
        lucide.createIcons();
    },

    closeCertificateModal: function() {
        const modal = document.getElementById('coa-modal');
        if (modal) modal.classList.add('hidden');
    },

    // ----------------------------------------------------
    // Admin / Kuro Atelier Desk (Orders, Fulfillment, Product CRUD, Whitelist)
    // ----------------------------------------------------
    toggleAdminDesk: function() {
        this.playZenChime('chime');
        window.location.href = 'admin.php';
    },

    refreshAdminData: async function() {
        await Promise.all([
            this.loadAdminStats(),
            this.loadAdminOrders(),
            this.loadAdminProducts(),
            this.loadAdminApplicants(),
            this.loadAdminInviteCodes()
        ]);
        if (window.lucide) lucide.createIcons();
    },

    switchAdminTab: function(tabName) {
        this.state.adminActiveTab = tabName;
        this.playZenChime('chime');

        const tabs = ['orders', 'products', 'whitelist', 'invites'];
        tabs.forEach(t => {
            const btn = document.getElementById(`admin-tab-btn-${t}`);
            const pane = document.getElementById(`admin-panel-${t}`) || document.getElementById(`admin-tab-pane-${t}`);
            if (btn && pane) {
                if (t === tabName) {
                    btn.className = 'px-5 py-3 rounded-t-xl bg-stone-900 border-t border-x border-amber-400/60 text-amber-300 font-bold flex items-center gap-2 transition-all';
                    pane.classList.remove('hidden');
                } else {
                    btn.className = 'px-5 py-3 rounded-t-xl text-stone-400 hover:text-white hover:bg-stone-900/50 flex items-center gap-2 transition-all';
                    pane.classList.add('hidden');
                }
            }
        });

        if (tabName === 'orders') this.loadAdminOrders();
        else if (tabName === 'products') this.loadAdminProducts();
        else if (tabName === 'whitelist') this.loadAdminApplicants();
        else if (tabName === 'invites') this.loadAdminInviteCodes();

        if (window.lucide) lucide.createIcons();
    },

    loadAdminStats: async function() {
        try {
            const res = await fetch('api/admin.php?action=stats');
            const data = await res.json();
            if (data.success) {
                const s = data.data;
                const elOrders = document.getElementById('stat-total-orders') || document.getElementById('admin-stats-total-orders');
                const elFulfillment = document.getElementById('stat-pending-fulfillment') || document.getElementById('admin-stats-pending-fulfillment');
                const elApplicants = document.getElementById('stat-pending-applicants') || document.getElementById('admin-stats-pending-whitelist');
                const elApproved = document.getElementById('stat-approved-members');
                const elFlacons = document.getElementById('stat-available-flacons') || document.getElementById('admin-stats-available');
                const elRev = document.getElementById('stat-total-revenue') || document.getElementById('admin-stats-revenue');
                const elPaid = document.getElementById('admin-stats-paid-orders');
                const elUnpaid = document.getElementById('admin-stats-unpaid-orders');

                if (elOrders) elOrders.textContent = s.total_orders || 0;
                if (elFulfillment) elFulfillment.textContent = s.pending_fulfillment || 0;
                if (elApplicants) elApplicants.textContent = s.pending_requests || 0;
                if (elApproved) elApproved.textContent = s.approved_members || 0;
                if (elFlacons) elFlacons.textContent = (s.available_flacons !== undefined) ? s.available_flacons : 0;
                if (elRev) elRev.textContent = s.total_revenue_formatted || 'Rp 0';
                if (elPaid) elPaid.textContent = s.paid_orders || 0;
                if (elUnpaid) elUnpaid.textContent = s.unpaid_orders || 0;

                // Badges on tabs
                const bOrders = document.getElementById('badge-admin-orders-count');
                const bProds = document.getElementById('badge-admin-products-count');
                const bWhite = document.getElementById('badge-admin-whitelist-count');
                if (bOrders) bOrders.textContent = s.total_orders || 0;
                if (bProds) bProds.textContent = s.total_flacons || 0;
                if (bWhite) bWhite.textContent = s.pending_requests || 0;
            }
        } catch (e) {
            console.error('Failed to load admin stats', e);
        }
    },

    filterAdminOrders: function(type, value) {
        if (type === 'payment') {
            this.state.adminPaymentFilter = value;
            ['all', 'pending', 'confirmed'].forEach(s => {
                const btn = document.getElementById(`filter-pay-${s}`);
                if (btn) {
                    const isActive = (s === 'all' && !value) || (s === value);
                    btn.className = isActive 
                        ? 'px-2.5 py-1 rounded-lg text-xs bg-amber-500/20 text-amber-300 border border-amber-500/40 font-semibold' 
                        : 'px-2.5 py-1 rounded-lg text-xs bg-stone-900 text-stone-400 hover:text-white border border-stone-800';
                }
            });
        } else if (type === 'fulfillment') {
            this.state.adminFulfillmentFilter = value;
            ['all', 'menunggu', 'dikemas', 'dikirim', 'selesai'].forEach(s => {
                const btn = document.getElementById(`filter-ful-${s}`);
                if (btn) {
                    const isActive = (s === 'all' && !value) || (s === value);
                    btn.className = isActive 
                        ? 'px-2.5 py-1 rounded-lg text-xs bg-amber-500/20 text-amber-300 border border-amber-500/40 font-semibold' 
                        : 'px-2.5 py-1 rounded-lg text-xs bg-stone-900 text-stone-400 hover:text-white border border-stone-800';
                }
            });
        }
        this.loadAdminOrders();
    },

    searchAdminOrders: function(query) {
        clearTimeout(this.state.searchDebounceTimer);
        this.state.searchDebounceTimer = setTimeout(() => {
            this.loadAdminOrders();
        }, 300);
    },

    // ----------------------------------------------------
    // Admin: Orders & Fulfillment Management
    // ----------------------------------------------------
    loadAdminOrders: async function() {
        const container = document.getElementById('admin-orders-list');
        if (!container) return;

        const payFilter = this.state.adminPaymentFilter !== undefined ? this.state.adminPaymentFilter : (document.getElementById('admin-filter-payment')?.value || '');
        const fulFilter = this.state.adminFulfillmentFilter !== undefined ? this.state.adminFulfillmentFilter : (document.getElementById('admin-filter-fulfillment')?.value || '');
        const search = document.getElementById('admin-orders-search')?.value.trim() || '';

        try {
            const params = new URLSearchParams({
                action: 'all_orders',
                payment_status: payFilter,
                fulfillment_status: fulFilter,
                search: search
            });
            const res = await fetch(`api/orders.php?${params.toString()}`);
            const data = await res.json();

            if (!data.success) {
                container.innerHTML = `<div class="p-6 text-center text-rose-400 text-xs">${data.message}</div>`;
                return;
            }

            const orders = data.data.orders || [];
            this.state.adminOrders = orders;

            if (orders.length === 0) {
                container.innerHTML = `
                    <div class="glass-kuro p-12 text-center rounded-2xl border border-stone-800 space-y-3">
                        <div class="w-12 h-12 mx-auto rounded-full bg-stone-900 border border-stone-800 flex items-center justify-center text-stone-500">
                            <i data-lucide="package-search" class="w-6 h-6"></i>
                        </div>
                        <h4 class="font-serif-luxury text-sm font-bold text-white">Tidak Ada Pesanan Ditemukan</h4>
                        <p class="text-xs text-stone-500">Coba sesuaikan kata kunci pencarian atau filter status transaksi.</p>
                    </div>
                `;
                if (window.lucide) lucide.createIcons();
                return;
            }

            container.innerHTML = orders.map(o => {
                // Payment Status Styling
                const isPaid = (o.payment_status === 'confirmed' || o.payment_status === 'completed');
                const paymentLabel = isPaid ? 'LUNAS (TERKONFIRMASI)' : (o.payment_status === 'pending_verification' ? 'VERIFIKASI BANK' : 'BELUM DIBAYAR (REVIEW)');
                const paymentBadgeClass = isPaid 
                    ? 'bg-emerald-950/80 text-emerald-300 border-emerald-500/50' 
                    : 'bg-amber-950/80 text-amber-300 border-amber-500/50';

                // Fulfillment Status Styling
                const ful = o.fulfillment_status || 'menunggu';
                let fulLabel = 'Menunggu Diproses';
                let fulBadgeClass = 'bg-stone-900 text-stone-400 border-stone-800';
                if (ful === 'dikemas') {
                    fulLabel = 'Sedang Dikemas (Packaging)';
                    fulBadgeClass = 'bg-amber-950/70 text-amber-300 border-amber-500/60 animate-pulse';
                } else if (ful === 'dikirim') {
                    fulLabel = 'Dalam Pengiriman (Shipping)';
                    fulBadgeClass = 'bg-sky-950/70 text-sky-300 border-sky-500/60';
                } else if (ful === 'selesai') {
                    fulLabel = 'Barang Sudah Diterima';
                    fulBadgeClass = 'bg-emerald-950/70 text-emerald-300 border-emerald-500/60';
                } else if (ful === 'dibatalkan') {
                    fulLabel = 'Pengiriman Dibatalkan';
                    fulBadgeClass = 'bg-rose-950/70 text-rose-300 border-rose-500/60';
                }

                // Render items
                const itemsHtml = (o.items || []).map(item => `
                    <div class="flex items-center gap-3 py-2 border-b border-stone-800/60 last:border-0">
                        <img src="${item.image_url || 'assets/images/kuro_series24_noir.jpg'}" alt="${item.product_name}" class="w-10 h-10 rounded-lg object-cover bg-stone-950 border border-stone-800 shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="font-serif-luxury text-xs font-bold text-white truncate">${item.product_name}</div>
                            <div class="text-[10px] text-amber-400/80 font-mono">${item.edition_serial || ''}</div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-xs text-white font-mono">${item.quantity} × ${item.price_formatted}</div>
                            <div class="text-xs font-serif-luxury text-gold-gradient font-bold">${item.subtotal_formatted}</div>
                        </div>
                    </div>
                `).join('');

                return `
                    <div class="glass-kuro p-5 rounded-2xl border border-stone-800 hover:border-amber-500/30 transition-all space-y-4">
                        <!-- Card Top Bar -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-800 pb-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-amber-300 font-bold text-xs">${o.order_number}</span>
                                    ${o.invoice_number ? `<span class="text-[10px] font-mono text-stone-400 bg-stone-900 border border-stone-800 px-2 py-0.5 rounded">${o.invoice_number}</span>` : ''}
                                </div>
                                <div class="text-[11px] text-stone-500 font-mono">Dipesan pada: ${o.created_at}</div>
                            </div>
                            <!-- Status Badges -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-mono border ${paymentBadgeClass}">
                                    ${paymentLabel}
                                </span>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-mono border ${fulBadgeClass}">
                                    ${fulLabel}
                                </span>
                            </div>
                        </div>

                        <!-- Customer & Shipping Detail -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 text-xs">
                            <div class="md:col-span-4 space-y-1.5 bg-black/40 p-3 rounded-xl border border-stone-900">
                                <div class="text-[10px] font-mono text-stone-400 uppercase tracking-widest font-bold">DATA PEMESAN</div>
                                <div class="font-bold text-white text-sm">${this.escapeHtml(o.client_name || 'Pembeli Kuro')}</div>
                                <div class="text-stone-400 font-mono text-[11px]">${this.escapeHtml(o.client_email)}</div>
                                <div class="text-amber-300/80 text-[11px]">${this.escapeHtml(o.title_company || 'Pelanggan Terdaftar')}</div>
                            </div>

                            <div class="md:col-span-8 space-y-1.5 bg-black/40 p-3 rounded-xl border border-stone-900">
                                <div class="text-[10px] font-mono text-stone-400 uppercase tracking-widest font-bold">ALAMAT & CATATAN PENGIRIMAN</div>
                                <p class="text-stone-300 whitespace-pre-line leading-relaxed text-[11px]">${this.escapeHtml(o.delivery_address || 'Alamat tidak dicantumkan')}</p>
                                ${o.courier_name || o.tracking_number ? `
                                    <div class="mt-2 pt-2 border-t border-stone-800/80 flex items-center gap-3 text-[11px]">
                                        <span class="text-stone-400">Ekspedisi: <strong class="text-amber-300">${this.escapeHtml(o.courier_name || '-')}</strong></span>
                                        <span class="text-stone-400">Resi: <strong class="text-white font-mono bg-stone-900 px-2 py-0.5 rounded">${this.escapeHtml(o.tracking_number || '-')}</strong></span>
                                    </div>
                                ` : ''}
                                ${o.fulfillment_notes ? `
                                    <div class="text-[10px] text-amber-200/80 italic mt-1">Catatan: "${this.escapeHtml(o.fulfillment_notes)}"</div>
                                ` : ''}
                            </div>
                        </div>

                        <!-- Items Box -->
                        <div class="bg-stone-950/70 p-3.5 rounded-xl border border-stone-900 space-y-1">
                            <div class="flex items-center justify-between text-[10px] font-mono text-stone-400 uppercase pb-1 border-b border-stone-900">
                                <span>Rincian Flacon Kuro</span>
                                <span>Jumlah & Subtotal</span>
                            </div>
                            ${itemsHtml}
                            <div class="flex items-center justify-between pt-2 border-t border-stone-800 text-xs">
                                <span class="font-mono text-stone-400">TOTAL PEMBAYARAN:</span>
                                <span class="font-serif-luxury text-base font-bold text-gold-gradient">${o.total_formatted}</span>
                            </div>
                        </div>

                        <!-- Action Toolbar -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-1 border-t border-stone-800/80">
                            <!-- Left: Payment Confirmation Button -->
                            <div class="flex items-center gap-2">
                                ${!isPaid ? `
                                    <button onclick="KuroApp.quickUpdateOrderPayment(${o.id}, 'confirmed')" class="btn-gold px-3.5 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-md">
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                        <span>Konfirmasi Pembayaran Lunas</span>
                                    </button>
                                ` : `
                                    <button onclick="KuroApp.quickUpdateOrderPayment(${o.id}, 'checkout_review')" class="btn-outline-gold px-3 py-1.5 rounded-lg text-xs text-stone-400 hover:text-white flex items-center gap-1.5">
                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        <span>Tandai Belum Lunas</span>
                                    </button>
                                `}
                                ${o.invoice_number ? `
                                    <button onclick="KuroApp.viewOrderInvoiceFromAdmin(${o.id})" class="glass-kuro px-3 py-1.5 rounded-lg text-xs text-amber-300 border border-stone-800 hover:border-amber-400 flex items-center gap-1">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        <span>Buka Invoice</span>
                                    </button>
                                ` : ''}
                            </div>

                            <!-- Right: Fulfillment Lifecycle Steps -->
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-[10px] font-mono text-stone-500 uppercase mr-1">Status Kirim:</span>
                                <button onclick="KuroApp.quickUpdateOrderFulfillment(${o.id}, 'dikemas')" title="Tandai Sedang Dikemas" class="px-2.5 py-1.5 rounded-lg text-[11px] font-mono border transition-all ${
                                    ful === 'dikemas' ? 'bg-amber-500/25 text-amber-300 border-amber-500 font-bold' : 'bg-stone-900 text-stone-400 border-stone-800 hover:text-white'
                                }">
                                    📦 Dikemas
                                </button>
                                <button onclick="KuroApp.promptShipOrder(${o.id})" title="Input Ekspedisi & No. Resi" class="px-2.5 py-1.5 rounded-lg text-[11px] font-mono border transition-all ${
                                    ful === 'dikirim' ? 'bg-sky-500/25 text-sky-300 border-sky-500 font-bold' : 'bg-stone-900 text-stone-400 border-stone-800 hover:text-white'
                                }">
                                    🚚 Kirim (Resi)
                                </button>
                                <button onclick="KuroApp.quickUpdateOrderFulfillment(${o.id}, 'selesai')" title="Tandai Barang Diterima Pelanggan" class="px-2.5 py-1.5 rounded-lg text-[11px] font-mono border transition-all ${
                                    ful === 'selesai' ? 'bg-emerald-500/25 text-emerald-300 border-emerald-500 font-bold' : 'bg-stone-900 text-stone-400 border-stone-800 hover:text-white'
                                }">
                                    ✅ Diterima
                                </button>
                                <button onclick="KuroApp.openOrderModal(${o.id})" title="Buka Pengaturan Lengkap" class="px-2.5 py-1.5 rounded-lg text-[11px] font-mono bg-stone-900 text-amber-300 border border-stone-800 hover:border-amber-400 flex items-center gap-1">
                                    <i data-lucide="sliders" class="w-3 h-3"></i>
                                    <span>Detail</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            if (window.lucide) lucide.createIcons();
        } catch (e) {
            console.error('Failed to load admin orders', e);
            container.innerHTML = `<div class="p-6 text-center text-rose-400 text-xs">Gagal memuat daftar pesanan website.</div>`;
        }
    },

    applyOrdersFilter: function() {
        this.loadAdminOrders();
    },

    debounceOrdersSearch: function() {
        clearTimeout(this.state.searchDebounceTimer);
        this.state.searchDebounceTimer = setTimeout(() => {
            this.loadAdminOrders();
        }, 300);
    },

    quickUpdateOrderPayment: async function(orderId, newPaymentStatus) {
        this.playZenChime('chime');
        try {
            const res = await fetch('api/orders.php?action=update_order_status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId, payment_status: newPaymentStatus })
            });
            const data = await res.json();
            if (data.success) {
                this.playZenChime('bell');
                this.showToast(data.message, 'success');
                await this.loadAdminOrders();
                await this.loadAdminStats();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal memperbarui status pembayaran.', 'error');
        }
    },

    quickUpdateOrderFulfillment: async function(orderId, newFulfillmentStatus) {
        this.playZenChime('chime');
        try {
            const res = await fetch('api/orders.php?action=update_order_status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId, fulfillment_status: newFulfillmentStatus })
            });
            const data = await res.json();
            if (data.success) {
                this.playZenChime('bell');
                this.showToast(data.message, 'success');
                await this.loadAdminOrders();
                await this.loadAdminStats();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal memperbarui status pengiriman.', 'error');
        }
    },

    promptShipOrder: function(orderId) {
        this.openOrderModal(orderId, 'dikirim');
    },

    openOrderModal: function(orderId, defaultFulfillment = null) {
        const order = this.state.adminOrders.find(o => o.id == orderId);
        if (!order) return;

        const modal = document.getElementById('admin-order-modal');
        const refEl = document.getElementById('admin-order-modal-ref');
        const sumEl = document.getElementById('admin-order-modal-summary');

        document.getElementById('admin-order-id').value = order.id;
        document.getElementById('admin-order-payment-status').value = order.payment_status || 'checkout_review';
        document.getElementById('admin-order-fulfillment-status').value = defaultFulfillment || (order.fulfillment_status || 'menunggu');
        document.getElementById('admin-order-courier').value = order.courier_name || '';
        document.getElementById('admin-order-tracking').value = order.tracking_number || '';
        document.getElementById('admin-order-notes').value = order.fulfillment_notes || '';

        if (refEl) {
            refEl.textContent = `Pesanan #${order.order_number} ${order.invoice_number ? '• ' + order.invoice_number : ''}`;
        }
        if (sumEl) {
            sumEl.innerHTML = `
                <div class="flex items-center justify-between text-white font-medium">
                    <span>${order.client_name} (${order.client_email})</span>
                    <span class="font-serif-luxury text-gold-gradient font-bold">${order.total_formatted}</span>
                </div>
                <div class="text-stone-400 text-[11px] truncate">${order.delivery_address || '-'}</div>
            `;
        }

        if (modal) modal.classList.remove('hidden');
        if (defaultFulfillment === 'dikirim') {
            document.getElementById('admin-order-courier')?.focus();
        }
        if (window.lucide) lucide.createIcons();
    },

    closeOrderModal: function() {
        const modal = document.getElementById('admin-order-modal');
        if (modal) modal.classList.add('hidden');
    },

    submitOrderModal: async function(e) {
        if (e) e.preventDefault();
        const orderId = document.getElementById('admin-order-id').value;
        const payStatus = document.getElementById('admin-order-payment-status').value;
        const fulStatus = document.getElementById('admin-order-fulfillment-status').value;
        const courier = document.getElementById('admin-order-courier').value.trim();
        const tracking = document.getElementById('admin-order-tracking').value.trim();
        const notes = document.getElementById('admin-order-notes').value.trim();

        try {
            const res = await fetch('api/orders.php?action=update_order_status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    order_id: orderId,
                    payment_status: payStatus,
                    fulfillment_status: fulStatus,
                    courier_name: courier,
                    tracking_number: tracking,
                    fulfillment_notes: notes
                })
            });
            const data = await res.json();
            if (data.success) {
                this.playZenChime('bell');
                this.closeOrderModal();
                this.showToast(data.message, 'success');
                await this.loadAdminOrders();
                await this.loadAdminStats();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal memperbarui pesanan.', 'error');
        }
    },

    viewOrderInvoiceFromAdmin: function(orderId) {
        const order = this.state.adminOrders.find(o => o.id == orderId);
        if (!order) return;

        const formattedOrderData = {
            order_number: order.order_number,
            invoice_number: order.invoice_number,
            invoice_hash: order.invoice_hash || 'KURO-OFFICIAL-PROOF',
            issued_at: order.created_at,
            total_amount: order.total_amount,
            total_formatted: order.total_formatted,
            subtotal_formatted: order.total_formatted,
            recipient_name: order.client_name,
            recipient_phone: '',
            delivery_address: order.delivery_address,
            order_notes: order.custom_engraving || order.fulfillment_notes || '',
            items: (order.items || []).map(it => ({
                id: it.product_id,
                name: it.product_name,
                edition_serial: it.edition_serial,
                price: it.price_per_item,
                price_formatted: it.price_formatted,
                quantity: it.quantity,
                subtotal: it.subtotal,
                subtotal_formatted: it.subtotal_formatted,
                image_url: it.image_url
            }))
        };
        this.showCheckoutSummary(formattedOrderData);
    },

    // ----------------------------------------------------
    // Admin: Product CRUD (Create, Read, Update, Delete & Stock)
    // ----------------------------------------------------
    loadAdminProducts: async function() {
        const container = document.getElementById('admin-products-list');
        if (!container) return;

        try {
            const res = await fetch('api/products.php?action=admin_list');
            const data = await res.json();

            if (!data.success) {
                container.innerHTML = `<div class="p-6 text-center text-rose-400 text-xs">${data.message}</div>`;
                return;
            }

            const products = data.data.products || [];
            this.state.adminProducts = products;

            if (products.length === 0) {
                container.innerHTML = `
                    <div class="glass-kuro p-12 text-center rounded-2xl border border-stone-800 space-y-3">
                        <div class="w-12 h-12 mx-auto rounded-full bg-stone-900 border border-stone-800 flex items-center justify-center text-stone-500">
                            <i data-lucide="flask-conical" class="w-6 h-6"></i>
                        </div>
                        <h4 class="font-serif-luxury text-sm font-bold text-white">Belum Ada Karya dalam Katalog</h4>
                        <p class="text-xs text-stone-500">Klik tombol "+ Tambah Produk Baru" di atas untuk menambahkan koleksi pertama.</p>
                    </div>
                `;
                if (window.lucide) lucide.createIcons();
                return;
            }

            container.innerHTML = products.map(p => {
                const isSeries24 = (p.edition_type === 'Series-24');
                const badgeEdition = isSeries24 
                    ? 'bg-amber-950/80 text-amber-300 border-amber-500/50' 
                    : (p.edition_type === '1-of-1' ? 'bg-stone-900 text-gold-gradient border-gold-subtle' : 'bg-stone-900 text-stone-300 border-stone-700');

                let statusBadge = 'bg-emerald-950/80 text-emerald-300 border-emerald-500/50';
                let statusLabel = 'Tersedia';
                if (p.status === 'reserved') {
                    statusBadge = 'bg-amber-950/80 text-amber-300 border-amber-500/50';
                    statusLabel = 'Dipesan';
                } else if (p.status === 'acquired') {
                    statusBadge = 'bg-stone-900 text-stone-400 border-stone-800';
                    statusLabel = 'Terakuisisi (Vault)';
                }

                return `
                    <div class="glass-kuro p-4 rounded-2xl border border-stone-800 hover:border-amber-500/30 transition-all flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <img src="${p.image_url || 'assets/images/kuro_series24_noir.jpg'}" alt="${p.name}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl object-cover bg-stone-950 border border-stone-800 shrink-0">
                            <div class="space-y-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="font-serif-luxury text-sm sm:text-base font-bold text-white truncate">${p.name}</h4>
                                    <span class="text-xs font-kanji text-amber-400/80">${p.japanese_name || ''}</span>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-full border ${badgeEdition}">${p.edition_type}</span>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-full border ${statusBadge}">${statusLabel}</span>
                                </div>
                                <div class="text-xs text-stone-400 font-light truncate">${p.subtitle || ''}</div>
                                <div class="flex flex-wrap items-center gap-4 text-xs font-mono pt-0.5">
                                    <span class="text-gold-gradient font-serif-luxury font-bold text-sm">${p.price_formatted}</span>
                                    <span class="text-stone-500">•</span>
                                    <span class="text-stone-300">${p.volume_ml}ml (${p.concentration || 'EDP'})</span>
                                    <span class="text-stone-500">•</span>
                                    <span class="text-amber-200/90">${p.edition_serial || ''}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Controls & Action Buttons -->
                        <div class="flex flex-wrap items-center justify-between md:justify-end gap-4 w-full md:w-auto shrink-0 pt-3 md:pt-0 border-t md:border-t-0 border-stone-800">
                            <!-- Stock Quick Stepper -->
                            <div class="flex items-center gap-2 bg-stone-950 border border-stone-800 rounded-xl px-2.5 py-1.5">
                                <span class="text-[10px] font-mono text-stone-400 uppercase">Stok:</span>
                                <button onclick="KuroApp.quickAdjustStock(${p.id}, -1)" title="Kurangi Stok" class="w-6 h-6 rounded bg-stone-900 hover:bg-stone-800 text-stone-300 flex items-center justify-center text-xs font-mono">-</button>
                                <span class="font-mono text-white text-xs font-bold w-7 text-center">${p.stock}</span>
                                <button onclick="KuroApp.quickAdjustStock(${p.id}, 1)" title="Tambah Stok" class="w-6 h-6 rounded bg-stone-900 hover:bg-stone-800 text-stone-300 flex items-center justify-center text-xs font-mono">+</button>
                            </div>

                            <!-- Edit and Delete Buttons -->
                            <div class="flex items-center gap-2">
                                <button onclick="KuroApp.openProductModal(${p.id})" class="btn-gold px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    <span>Edit</span>
                                </button>
                                <button onclick="KuroApp.deleteProduct(${p.id}, '${p.name.replace(/'/g, "\\'")}')" class="btn-outline-gold px-3 py-1.5 rounded-xl text-xs text-rose-400 hover:border-rose-400 hover:text-rose-300 flex items-center gap-1.5">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            if (window.lucide) lucide.createIcons();
        } catch (e) {
            console.error('Failed to load admin products', e);
            container.innerHTML = `<div class="p-6 text-center text-rose-400 text-xs">Gagal memuat inventaris produk.</div>`;
        }
    },

    openProductModal: function(productId = 0) {
        const modal = document.getElementById('admin-product-modal');
        const titleEl = document.getElementById('admin-product-modal-title');
        if (!modal) return;

        if (productId === 0) {
            // New Product
            if (titleEl) titleEl.textContent = 'TAMBAH KARYA PARFUM BARU';
            document.getElementById('admin-prod-id').value = '0';
            document.getElementById('admin-prod-name').value = '';
            document.getElementById('admin-prod-kanji').value = '';
            document.getElementById('admin-prod-subtitle').value = '';
            document.getElementById('admin-prod-edition-type').value = 'Series-24';
            document.getElementById('admin-prod-serial').value = 'Series 24 Edition (Limit 24 Botol)';
            document.getElementById('admin-prod-price').value = '4850000';
            document.getElementById('admin-prod-stock').value = '24';
            document.getElementById('admin-prod-volume').value = '50';
            document.getElementById('admin-prod-concentration').value = 'Eau de Parfum Intense (26% Concentration)';
            document.getElementById('admin-prod-status').value = 'available';
            document.getElementById('admin-prod-image').value = 'assets/images/kuro_kyara_oud.jpg';
            document.getElementById('admin-prod-desc').value = '';
            document.getElementById('admin-prod-philosophy').value = '';
            document.getElementById('admin-prod-craftsmanship').value = '';
            document.getElementById('admin-prod-top-notes').value = '';
            document.getElementById('admin-prod-heart-notes').value = '';
            document.getElementById('admin-prod-base-notes').value = '';
        } else {
            // Edit Product
            const p = this.state.adminProducts.find(item => item.id == productId);
            if (!p) return;

            if (titleEl) titleEl.textContent = `EDIT KARYA: ${p.name.toUpperCase()}`;
            document.getElementById('admin-prod-id').value = p.id;
            document.getElementById('admin-prod-name').value = p.name || '';
            document.getElementById('admin-prod-kanji').value = p.japanese_name || '';
            document.getElementById('admin-prod-subtitle').value = p.subtitle || '';
            document.getElementById('admin-prod-edition-type').value = p.edition_type || 'Series-24';
            document.getElementById('admin-prod-serial').value = p.edition_serial || '';
            document.getElementById('admin-prod-price').value = p.price_raw || p.price || '';
            document.getElementById('admin-prod-stock').value = p.stock || 0;
            document.getElementById('admin-prod-volume').value = p.volume_ml || 50;
            document.getElementById('admin-prod-concentration').value = p.concentration || '';
            document.getElementById('admin-prod-status').value = p.status || 'available';
            document.getElementById('admin-prod-image').value = p.image_url || 'assets/images/kuro_series24_noir.jpg';
            document.getElementById('admin-prod-desc').value = p.description || '';
            document.getElementById('admin-prod-philosophy').value = p.philosophy || '';
            document.getElementById('admin-prod-craftsmanship').value = p.flacon_craftsmanship || '';

            // Extract notes
            const topStr = (p.notes?.top || []).map(n => n.note_name).join(', ');
            const heartStr = (p.notes?.heart || []).map(n => n.note_name).join(', ');
            const baseStr = (p.notes?.base || []).map(n => n.note_name).join(', ');
            document.getElementById('admin-prod-top-notes').value = topStr;
            document.getElementById('admin-prod-heart-notes').value = heartStr;
            document.getElementById('admin-prod-base-notes').value = baseStr;
        }

        modal.classList.remove('hidden');
        document.getElementById('admin-prod-name')?.focus();
        if (window.lucide) lucide.createIcons();
    },

    closeProductModal: function() {
        const modal = document.getElementById('admin-product-modal');
        if (modal) modal.classList.add('hidden');
    },

    onEditionTypeChange: function() {
        const type = document.getElementById('admin-prod-edition-type')?.value;
        const stockInput = document.getElementById('admin-prod-stock');
        const serialInput = document.getElementById('admin-prod-serial');

        if (type === 'Series-24') {
            if (stockInput) stockInput.value = '24';
            if (serialInput) serialInput.value = 'Series 24 Edition (Limit 24 Botol)';
        } else if (type === '1-of-1') {
            if (stockInput) stockInput.value = '1';
            if (serialInput) serialInput.value = '#KURO-00' + Math.floor(Math.random() * 90 + 10) + '/01 (1 of 1 Global Edition)';
        } else {
            if (stockInput) stockInput.value = '5';
            if (serialInput) serialInput.value = 'Limited Reserve Atelier Edition';
        }
    },

    selectImagePreset: function(url) {
        const imgInput = document.getElementById('admin-prod-image');
        if (imgInput) {
            imgInput.value = url;
            this.showToast('Foto flacon dipilih: ' + url.split('/').pop(), 'info');
        }
    },

    submitProductModal: async function(e) {
        if (e) e.preventDefault();
        const id = parseInt(document.getElementById('admin-prod-id').value) || 0;
        const name = document.getElementById('admin-prod-name').value.trim();
        const kanji = document.getElementById('admin-prod-kanji').value.trim();
        const subtitle = document.getElementById('admin-prod-subtitle').value.trim();
        const editionType = document.getElementById('admin-prod-edition-type').value;
        const serial = document.getElementById('admin-prod-serial').value.trim();
        const price = parseFloat(document.getElementById('admin-prod-price').value) || 0;
        const stock = parseInt(document.getElementById('admin-prod-stock').value) || 0;
        const volume = parseInt(document.getElementById('admin-prod-volume').value) || 50;
        const concentration = document.getElementById('admin-prod-concentration').value.trim();
        const status = document.getElementById('admin-prod-status').value;
        const image = document.getElementById('admin-prod-image').value.trim();
        const desc = document.getElementById('admin-prod-desc').value.trim();
        const philosophy = document.getElementById('admin-prod-philosophy').value.trim();
        const craftsmanship = document.getElementById('admin-prod-craftsmanship').value.trim();
        const topNotes = document.getElementById('admin-prod-top-notes').value.trim();
        const heartNotes = document.getElementById('admin-prod-heart-notes').value.trim();
        const baseNotes = document.getElementById('admin-prod-base-notes').value.trim();

        if (!name || price <= 0 || !desc) {
            this.showToast('Nama, deskripsi, dan harga produk wajib diisi.', 'error');
            return;
        }

        const payload = {
            id: id,
            name: name,
            japanese_name: kanji,
            subtitle: subtitle,
            edition_type: editionType,
            edition_serial: serial,
            price: price,
            stock: stock,
            volume_ml: volume,
            concentration: concentration,
            status: status,
            image_url: image,
            description: desc,
            philosophy: philosophy,
            flacon_craftsmanship: craftsmanship,
            top_notes: topNotes ? topNotes.split(',').map(s => s.trim()).filter(Boolean) : [],
            heart_notes: heartNotes ? heartNotes.split(',').map(s => s.trim()).filter(Boolean) : [],
            base_notes: baseNotes ? baseNotes.split(',').map(s => s.trim()).filter(Boolean) : []
        };

        const action = (id > 0) ? 'update' : 'create';
        try {
            const res = await fetch(`api/products.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                this.playZenChime('bell');
                this.closeProductModal();
                this.showToast(data.message, 'success');
                await this.loadAdminProducts();
                await this.loadProducts(); // Update public showroom catalog
                await this.loadAdminStats();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal menyimpan produk.', 'error');
        }
    },

    deleteProduct: async function(productId, productName) {
        if (!confirm(`Apakah Anda yakin ingin menghapus karya '${productName}' dari inventaris Kuro Atelier?`)) {
            return;
        }
        this.playZenChime('chime');

        try {
            const res = await fetch('api/products.php?action=delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: productId })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message, 'success');
                await this.loadAdminProducts();
                await this.loadProducts();
                await this.loadAdminStats();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal menghapus produk.', 'error');
        }
    },

    quickAdjustStock: async function(productId, delta) {
        const p = this.state.adminProducts.find(item => item.id == productId);
        if (!p) return;

        const newStock = Math.max(0, (parseInt(p.stock) || 0) + delta);
        try {
            const res = await fetch('api/products.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: productId, stock: newStock })
            });
            const data = await res.json();
            if (data.success) {
                p.stock = newStock;
                this.showToast(`Stok '${p.name}' diperbarui: ${newStock} unit`, 'info');
                await this.loadAdminProducts();
                await this.loadProducts();
            }
        } catch (e) {
            console.error(e);
        }
    },

    // ----------------------------------------------------
    // Admin: Whitelist Curation
    // ----------------------------------------------------
    loadAdminApplicants: async function() {
        const container = document.getElementById('admin-whitelist-requests') || document.getElementById('admin-applicants-table');
        if (!container) return;

        try {
            const res = await fetch('api/whitelist.php?action=list_requests');
            const data = await res.json();
            if (data.success) {
                const reqs = data.data.requests;
                if (reqs.length === 0) {
                    container.innerHTML = `<div class="p-6 text-center text-stone-500 text-xs">Belum ada pemohon kurasi.</div>`;
                    return;
                }

                container.innerHTML = reqs.map(r => `
                    <div class="glass-kuro p-4 rounded-xl border border-stone-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-serif-luxury font-bold text-white text-sm">${this.escapeHtml(r.full_name)}</h4>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded-full ${
                                    r.status === 'approved' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-amber-950 text-amber-400 border border-amber-800'
                                }">${r.status.toUpperCase()}</span>
                            </div>
                            <div class="text-xs text-amber-200/80">${this.escapeHtml(r.organization_title)} • <span class="text-stone-400 font-mono">${this.escapeHtml(r.email)}</span></div>
                            <p class="text-xs text-stone-300 italic font-editorial">"${this.escapeHtml(r.statement_of_intent)}"</p>
                        </div>
                        ${r.status === 'pending' ? `
                            <div class="flex items-center gap-2 shrink-0">
                                <button onclick="KuroApp.reviewApplicant(${r.id}, 'approved')" class="btn-gold px-3.5 py-1.5 rounded-lg text-xs font-bold">SETUJUI</button>
                                <button onclick="KuroApp.reviewApplicant(${r.id}, 'rejected')" class="btn-outline-gold px-3 py-1.5 rounded-lg text-xs text-rose-400">TOLAK</button>
                            </div>
                        ` : `<span class="text-xs text-stone-500 font-mono">Ditinjau</span>`}
                    </div>
                `).join('');
                if (window.lucide) lucide.createIcons();
            }
        } catch (e) {
            console.error(e);
        }
    },

    loadAdminWhitelistRequests: function() {
        return this.loadAdminApplicants();
    },

    reviewApplicant: async function(requestId, decision) {
        this.playZenChime('chime');
        try {
            const res = await fetch('api/whitelist.php?action=review_request', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ request_id: requestId, decision: decision })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message, 'success');
                await this.loadAdminApplicants();
                await this.loadAdminStats();
            }
        } catch (e) {
            console.error(e);
        }
    },

    // ----------------------------------------------------
    // Admin: VIP Invite Codes
    // ----------------------------------------------------
    loadAdminInviteCodes: async function() {
        const container = document.getElementById('admin-invite-codes-list');
        if (!container) return;

        try {
            const res = await fetch('api/admin.php?action=invite_codes');
            const data = await res.json();
            if (data.success) {
                const codes = data.data.codes || [];
                if (codes.length === 0) {
                    container.innerHTML = `<div class="p-6 text-center text-stone-500 text-xs col-span-full">Belum ada kode undangan aktif.</div>`;
                    return;
                }
                container.innerHTML = codes.map(c => `
                    <div class="glass-kuro p-3.5 rounded-xl border border-stone-800 flex items-center justify-between hover:border-amber-500/30 transition-all">
                        <div class="space-y-0.5 min-w-0">
                            <div class="font-mono text-amber-300 font-bold text-xs truncate">${c.code}</div>
                            <div class="text-[10px] text-stone-400 truncate">${c.description || 'VIP Patron Code'}</div>
                        </div>
                        <div class="text-right shrink-0 pl-3">
                            <span class="text-xs font-mono text-white font-bold">${c.times_used} / ${c.max_uses}</span>
                            <div class="text-[9px] text-stone-500 font-mono">TERPAKAI</div>
                        </div>
                    </div>
                `).join('');
                if (window.lucide) lucide.createIcons();
            }
        } catch (e) {
            console.error(e);
        }
    },

    generateInviteCode: async function(e) {
        if (e) e.preventDefault();
        const codeInput = document.getElementById('invite-custom-code');
        const quotaInput = document.getElementById('invite-max-uses') || document.getElementById('new-invite-quota');
        const descInput = document.getElementById('invite-notes') || document.getElementById('new-invite-desc');

        const customCode = codeInput ? codeInput.value.trim() : '';
        const desc = descInput ? descInput.value.trim() : 'VIP Patron Access Code';
        const quota = quotaInput ? parseInt(quotaInput.value) || 1 : 1;

        try {
            const res = await fetch('api/admin.php?action=generate_invite', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: customCode, description: desc, max_uses: quota })
            });
            const data = await res.json();
            if (data.success) {
                this.playZenChime('bell');
                this.showToast(data.message, 'success');
                if (codeInput) codeInput.value = '';
                if (descInput) descInput.value = '';
                await this.loadAdminInviteCodes();
            } else {
                this.showToast(data.message || 'Gagal menerbitkan kode.', 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Gagal menerbitkan kode undangan.', 'error');
        }
    },

    generateNewInviteCode: function() {
        return this.generateInviteCode();
    },

    // ----------------------------------------------------
    // Toast Notification System
    // ----------------------------------------------------
    showToast: function(message, type = 'info') {
        const toast = document.getElementById('kuro-toast');
        const text = document.getElementById('kuro-toast-text');
        const icon = document.getElementById('kuro-toast-icon');
        if (!toast || !text) return;

        text.textContent = message;
        if (type === 'success') {
            toast.className = 'fixed bottom-6 right-6 z-50 glass-kuro border border-amber-400/80 bg-stone-950/95 text-amber-200 px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 transition-all duration-300';
            if (icon) icon.className = 'w-4 h-4 text-amber-400 shrink-0';
        } else if (type === 'error') {
            toast.className = 'fixed bottom-6 right-6 z-50 glass-kuro border border-rose-500/80 bg-stone-950/95 text-rose-200 px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 transition-all duration-300';
            if (icon) icon.className = 'w-4 h-4 text-rose-400 shrink-0';
        } else {
            toast.className = 'fixed bottom-6 right-6 z-50 glass-kuro border border-stone-700 bg-stone-950/95 text-stone-200 px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 transition-all duration-300';
            if (icon) icon.className = 'w-4 h-4 text-stone-400 shrink-0';
        }

        toast.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
        }, 4500);
    },

    setupEventListeners: function() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeDetailModal();
                this.closeWhitelistModal();
                this.closeConcierge();
                this.closeCheckout();
                this.closeCertificateModal();
                this.closeCartDrawer();
                this.closeCartCheckout();
                this.closeOrderSummary();
                this.closeLoginModal();
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    KuroApp.init();
});
