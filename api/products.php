<?php
// ===================================================
// Kuro Atelier - Products & Bespoke Showcase API
// Complete CRUD for Admin: Create, Read, Update, Delete & Stock Management
// ===================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

initSession();
$db = Database::getConnection();
$input = getJsonInput();
$action = $_GET['action'] ?? ($input['action'] ?? 'list');

switch ($action) {
    case 'list':
        $whitelisted = isWhitelisted();
        $user        = getCurrentUser();

        // Use view for dynamic series_category from stock
        // Exclude auction products and auction tickets from normal catalog
        $stmt = $db->query("
            SELECT psv.*
            FROM product_series_view psv
            WHERE psv.is_auction = 0
              AND psv.edition_type != 'auction_ticket'
            ORDER BY psv.series_rank ASC, psv.id ASC
        ");
        $products = $stmt->fetchAll();

        // Fetch notes for each product
        $notesStmt = $db->query("SELECT * FROM olfactory_notes ORDER BY product_id, note_type");
        $allNotes = $notesStmt->fetchAll();
        $notesByProduct = [];
        foreach ($allNotes as $note) {
            $notesByProduct[$note['product_id']][$note['note_type']][] = $note;
        }

        // ─── Series-based access rules ───────────────────────────────
        // Limited Edition (stock=1): only whitelisted can BUY; everyone sees it
        // Premium / Deluxe / Reguler: all logged-in users can buy via cart
        // Auction products: handled by /api/auction.php
        foreach ($products as &$p) {
            $p['notes'] = $notesByProduct[$p['id']] ?? ['top' => [], 'heart' => [], 'base' => []];
            $p['stock'] = (int)($p['stock'] ?? 1);
            $category   = $p['series_category'];

            if ($category === 'Limited Edition') {
                // 1-of-1: acquisition model (whitelist only)
                if (!$whitelisted) {
                    $p['price_raw']       = null;
                    $p['price_formatted'] = 'Terkunci — Akses Kolektor Diperlukan';
                    $p['can_acquire']     = false;
                    $p['can_cart']        = false;
                    $p['locked']          = true;
                } else {
                    $p['price_raw']       = (float)$p['price'];
                    $p['price_formatted'] = formatRupiah((float)$p['price']);
                    $p['can_acquire']     = ($p['status'] === 'available');
                    $p['can_cart']        = false;
                    $p['locked']          = false;
                }
            } else {
                // Premium / Deluxe / Reguler: cart purchase, all users
                $p['price_raw']       = (float)$p['price'];
                $p['price_formatted'] = formatRupiah((float)$p['price']);
                $p['can_cart']        = ($p['stock'] > 0 && $user !== null);
                $p['can_acquire']     = false;
                $p['locked']          = false;
            }
        }
        unset($p);

        jsonResponse(true, 'Katalog Kuro berhasil dimuat.', [
            'products'      => $products,
            'is_whitelisted'=> $whitelisted,
        ]);
        break;

    case 'admin_list':
        requireAdmin();
        // Admin sees ALL products including auctions, with series category
        $stmt = $db->query("
            SELECT psv.*,
                   (SELECT COUNT(*) FROM order_items oi WHERE oi.product_id = psv.id) as total_sold_items,
                   (SELECT MAX(ab.bid_amount) FROM auction_bids ab WHERE ab.product_id = psv.id) as current_highest_bid
            FROM product_series_view psv
            ORDER BY psv.series_rank ASC, psv.id DESC
        ");
        $products = $stmt->fetchAll();

        $notesStmt = $db->query("SELECT * FROM olfactory_notes ORDER BY product_id, note_type");
        $allNotes = $notesStmt->fetchAll();
        $notesByProduct = [];
        foreach ($allNotes as $note) {
            $notesByProduct[$note['product_id']][$note['note_type']][] = $note;
        }

        foreach ($products as &$p) {
            $p['price_raw']           = (float)$p['price'];
            $p['price_formatted']     = formatRupiah((float)$p['price']);
            $p['stock']               = (int)($p['stock'] ?? 0);
            $p['notes']               = $notesByProduct[$p['id']] ?? ['top' => [], 'heart' => [], 'base' => []];
            $p['current_highest_bid_fmt'] = $p['current_highest_bid']
                ? formatRupiah((float)$p['current_highest_bid'])
                : null;
        }
        unset($p);

        jsonResponse(true, 'Daftar inventaris produk lengkap untuk Kuro Master Admin.', [
            'products'    => $products,
            'total_count' => count($products)
        ]);
        break;

    case 'detail':
        $id = (int)($_GET['id'] ?? ($input['id'] ?? 0));
        $slug = trim($_GET['slug'] ?? ($input['slug'] ?? ''));

        if ($id > 0) {
            $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
        } elseif (!empty($slug)) {
            $stmt = $db->prepare("SELECT * FROM products WHERE slug = ?");
            $stmt->execute([$slug]);
        } else {
            jsonResponse(false, 'Identifikasi produk tidak valid.', [], 400);
        }

        $product = $stmt->fetch();
        if (!$product) {
            jsonResponse(false, 'Karya parfum Kuro tidak ditemukan.', [], 404);
        }

        // Fetch olfactory notes grouped by type
        $notesStmt = $db->prepare("SELECT * FROM olfactory_notes WHERE product_id = ?");
        $notesStmt->execute([$product['id']]);
        $notes = $notesStmt->fetchAll();

        $pyramid = ['top' => [], 'heart' => [], 'base' => []];
        foreach ($notes as $n) {
            $pyramid[$n['note_type']][] = $n;
        }
        $product['notes_pyramid'] = $pyramid;

        $whitelisted = isWhitelisted();
        $user = getCurrentUser();
        $isSeries24 = ($product['edition_type'] === 'Series-24');
        $product['stock'] = (int)($product['stock'] ?? 1);

        if ($isSeries24) {
            $product['price_raw'] = (float)$product['price'];
            $product['price_formatted'] = formatRupiah((float)$product['price']);
            $product['can_cart'] = ($product['stock'] > 0);
            $product['can_acquire'] = false;
        } else {
            if (!$whitelisted) {
                $product['price_raw'] = null;
                $product['price_formatted'] = 'Terkunci (Akses Whitelist Diperlukan)';
                $product['can_acquire'] = false;
                $product['can_cart'] = false;
            } else {
                $product['price_raw'] = (float)$product['price'];
                $product['price_formatted'] = formatRupiah((float)$product['price']);
                $product['can_acquire'] = ($product['status'] === 'available');
                $product['can_cart'] = false;
            }
        }

        jsonResponse(true, 'Detail karya Kuro ' . $product['name'], [
            'product' => $product,
            'is_whitelisted' => $whitelisted
        ]);
        break;

    case 'create':
        requireAdmin();
        $name                = trim($input['name'] ?? '');
        $subtitle            = trim($input['subtitle'] ?? '');
        $japaneseName        = trim($input['japanese_name'] ?? '');
        $price               = (float)($input['price'] ?? 0);
        $stock               = isset($input['stock']) ? (int)$input['stock'] : 24;
        $volumeMl            = (int)($input['volume_ml'] ?? 50);
        $concentration       = trim($input['concentration'] ?? 'Eau de Parfum Intense (26% Concentration)');
        $description         = trim($input['description'] ?? '');
        $philosophy          = trim($input['philosophy'] ?? ($input['description'] ?? ''));
        $flaconCraftsmanship = trim($input['flacon_craftsmanship'] ?? '');
        $imageUrl            = trim($input['image_url'] ?? 'assets/images/kuro_default.jpg');
        $status              = $input['status'] ?? 'available';
        $isAuction           = (int)($input['is_auction'] ?? 0);
        $auctionStatus       = $isAuction ? ($input['auction_status'] ?? 'upcoming') : null;
        $auctionStartPrice   = ($isAuction && isset($input['auction_start_price'])) ? (float)$input['auction_start_price'] : null;
        $auctionIncrement    = ($isAuction && isset($input['auction_increment'])) ? (float)$input['auction_increment'] : null;
        $auctionEndTime      = ($isAuction && !empty($input['auction_end_time'])) ? trim($input['auction_end_time']) : null;

        // ── Auto-derive Series Category & Edition Type strictly by stock & auction ──
        if ($isAuction && isset($input['edition_type']) && $input['edition_type'] === 'auction_ticket') {
            $seriesCategory = 'Auction Ticket';
            $editionType   = 'auction_ticket';
            $editionSerial = 'Auction Ticket';
        } elseif ($isAuction) {
            $seriesCategory = 'Auction';
            $editionType   = ($stock === 1) ? '1-of-1' : 'Series-24';
            $editionSerial = trim($input['edition_serial'] ?? '') ?: ('#KURO-AUC-' . strtoupper(substr(md5(uniqid()), 0, 4)) . " (Lelang 1 of {$stock})");
        } elseif ($stock === 1) {
            $seriesCategory = 'Limited Edition';
            $editionType   = '1-of-1';
            $editionSerial = trim($input['edition_serial'] ?? '') ?: ('#KURO-' . strtoupper(substr(md5(uniqid()), 0, 6)) . ' (1 of 1 Global Edition)');
        } elseif ($stock <= 8) {
            $seriesCategory = 'Premium Series';
            $editionType   = 'Series-24';
            $editionSerial = trim($input['edition_serial'] ?? '') ?: "Premium Series Edition (Limit {$stock} Botol)";
        } elseif ($stock <= 12) {
            $seriesCategory = 'Deluxe Series';
            $editionType   = 'Series-24';
            $editionSerial = trim($input['edition_serial'] ?? '') ?: "Deluxe Series Edition (Limit {$stock} Botol)";
        } else {
            $seriesCategory = 'Reguler Series';
            $editionType   = 'Series-24';
            $editionSerial = trim($input['edition_serial'] ?? '') ?: "Reguler Series Edition (Limit {$stock} Botol)";
        }

        if (empty($name) || empty($description) || $price <= 0) {
            jsonResponse(false, 'Nama parfum, deskripsi, dan harga wajib diisi.', [], 400);
        }
        if ($stock <= 0) {
            jsonResponse(false, 'Stok produk harus lebih dari 0.', [], 400);
        }

        // Generate unique slug
        $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = $baseSlug;
        $counter = 1;
        while (true) {
            $sCheck = $db->prepare("SELECT id FROM products WHERE slug = ?");
            $sCheck->execute([$slug]);
            if (!$sCheck->fetch()) break;
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $stmt = $db->prepare("
            INSERT INTO products (
                slug, name, subtitle, japanese_name, edition_type, series_category, edition_serial,
                price, stock, initial_stock, volume_ml, concentration, description, philosophy,
                flacon_craftsmanship, image_url, status,
                is_auction, auction_status, auction_start_price, auction_increment, auction_end_time,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $slug, $name, $subtitle, $japaneseName, $editionType, $seriesCategory, $editionSerial,
            $price, $stock, $stock, $volumeMl, $concentration, $description, $philosophy,
            $flaconCraftsmanship, $imageUrl, $status,
            $isAuction, $auctionStatus, $auctionStartPrice, $auctionIncrement, $auctionEndTime,
        ]);
        $newId = (int)$db->lastInsertId();

        // Olfactory notes
        selfInsertNotes($db, $newId, $input);

        jsonResponse(true, "Karya parfum baru '{$name}' ({$seriesCategory}) berhasil ditambahkan ke katalog Kuro Atelier.", [
            'id'              => $newId,
            'name'            => $name,
            'slug'            => $slug,
            'stock'           => $stock,
            'series_category' => $seriesCategory,
            'edition_type'    => $editionType
        ]);
        break;

    case 'update':
        requireAdmin();
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(false, 'ID Produk tidak valid.', [], 400);
        }

        // Check product existence
        $checkStmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $checkStmt->execute([$id]);
        $existing = $checkStmt->fetch();
        if (!$existing) {
            jsonResponse(false, 'Produk tidak ditemukan.', [], 404);
        }

        $name = trim($input['name'] ?? $existing['name']);
        $subtitle = trim($input['subtitle'] ?? $existing['subtitle']);
        $japaneseName = trim($input['japanese_name'] ?? $existing['japanese_name']);
        $price = isset($input['price']) ? (float)$input['price'] : (float)$existing['price'];
        $stock = isset($input['stock']) ? (int)$input['stock'] : (int)$existing['stock'];
        $volumeMl = isset($input['volume_ml']) ? (int)$input['volume_ml'] : (int)$existing['volume_ml'];
        $concentration = trim($input['concentration'] ?? $existing['concentration']);
        $description = trim($input['description'] ?? $existing['description']);
        $philosophy = trim($input['philosophy'] ?? ($existing['philosophy'] ?: $description));
        $flaconCraftsmanship = trim($input['flacon_craftsmanship'] ?? $existing['flacon_craftsmanship']);
        $imageUrl = trim($input['image_url'] ?? $existing['image_url']);
        $status = $input['status'] ?? $existing['status'];

        // Auction flags
        $isAuction = isset($input['is_auction']) ? (int)$input['is_auction'] : (int)$existing['is_auction'];
        $auctionStatus = $isAuction ? ($input['auction_status'] ?? ($existing['auction_status'] ?: 'upcoming')) : null;
        $auctionStartPrice = $isAuction ? (isset($input['auction_start_price']) ? (float)$input['auction_start_price'] : (float)$existing['auction_start_price']) : null;
        $auctionIncrement = $isAuction ? (isset($input['auction_increment']) ? (float)$input['auction_increment'] : (float)$existing['auction_increment']) : null;
        $auctionEndTime = $isAuction ? (!empty($input['auction_end_time']) ? trim($input['auction_end_time']) : $existing['auction_end_time']) : null;

        // ── Auto-derive Series Category & Edition Type strictly by stock & auction ──
        if ($isAuction && ($existing['edition_type'] === 'auction_ticket' || ($input['edition_type'] ?? '') === 'auction_ticket')) {
            $seriesCategory = 'Auction Ticket';
            $editionType   = 'auction_ticket';
        } elseif ($isAuction) {
            $seriesCategory = 'Auction';
            $editionType   = ($stock === 1) ? '1-of-1' : 'Series-24';
        } elseif ($stock === 1) {
            $seriesCategory = 'Limited Edition';
            $editionType   = '1-of-1';
        } elseif ($stock <= 8) {
            $seriesCategory = 'Premium Series';
            $editionType   = 'Series-24';
        } elseif ($stock <= 12) {
            $seriesCategory = 'Deluxe Series';
            $editionType   = 'Series-24';
        } else {
            $seriesCategory = 'Reguler Series';
            $editionType   = 'Series-24';
        }

        // Edition serial
        $editionSerial = trim($input['edition_serial'] ?? '');
        if (empty($editionSerial)) {
            if ($seriesCategory === 'Limited Edition') {
                $editionSerial = $existing['edition_serial'] ?: ('#KURO-' . strtoupper(substr(md5(uniqid()), 0, 6)) . ' (1 of 1 Global Edition)');
            } elseif ($seriesCategory === 'Premium Series') {
                $editionSerial = "Premium Series Edition (Limit {$stock} Botol)";
            } elseif ($seriesCategory === 'Deluxe Series') {
                $editionSerial = "Deluxe Series Edition (Limit {$stock} Botol)";
            } elseif ($seriesCategory === 'Reguler Series') {
                $editionSerial = "Reguler Series Edition (Limit {$stock} Botol)";
            } else {
                $editionSerial = $existing['edition_serial'];
            }
        }

        if (empty($name) || $price <= 0) {
            jsonResponse(false, 'Nama parfum dan harga tidak boleh kosong.', [], 400);
        }

        $initialStock = isset($input['initial_stock']) ? (int)$input['initial_stock'] : ($existing['initial_stock'] ?: $stock);

        $upd = $db->prepare("
            UPDATE products SET
                name = ?, subtitle = ?, japanese_name = ?, edition_type = ?, series_category = ?, edition_serial = ?,
                price = ?, stock = ?, initial_stock = ?, volume_ml = ?, concentration = ?, description = ?,
                philosophy = ?, flacon_craftsmanship = ?, image_url = ?, status = ?,
                is_auction = ?, auction_status = ?, auction_start_price = ?, auction_increment = ?, auction_end_time = ?
            WHERE id = ?
        ");
        $upd->execute([
            $name, $subtitle, $japaneseName, $editionType, $seriesCategory, $editionSerial,
            $price, $stock, $initialStock, $volumeMl, $concentration, $description,
            $philosophy, $flaconCraftsmanship, $imageUrl, $status,
            $isAuction, $auctionStatus, $auctionStartPrice, $auctionIncrement, $auctionEndTime,
            $id
        ]);

        // If notes provided in update, update olfactory notes
        if (isset($input['top_notes']) || isset($input['heart_notes']) || isset($input['base_notes'])) {
            $db->prepare("DELETE FROM olfactory_notes WHERE product_id = ?")->execute([$id]);
            selfInsertNotes($db, $id, $input);
        }

        jsonResponse(true, "Karya parfum '{$name}' ({$seriesCategory}) berhasil diperbarui.", [
            'id'              => $id,
            'name'            => $name,
            'stock'           => $stock,
            'series_category' => $seriesCategory,
            'price_formatted' => formatRupiah($price)
        ]);
        break;

    case 'delete':
        requireAdmin();
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            jsonResponse(false, 'ID Produk tidak valid.', [], 400);
        }

        $checkStmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $checkStmt->execute([$id]);
        $product = $checkStmt->fetch();
        if (!$product) {
            jsonResponse(false, 'Produk tidak ditemukan.', [], 404);
        }

        // Delete related notes, bids, access, cart items, and product
        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM olfactory_notes WHERE product_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM auction_bids WHERE product_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM auction_access WHERE product_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM cart_items WHERE product_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            $db->commit();
            jsonResponse(true, "Karya parfum '{$product['name']}' berhasil dihapus dari inventaris.");
        } catch (Exception $e) {
            $db->rollBack();
            // If foreign key constraint triggered by order history, provide graceful archive status
            $db->prepare("UPDATE products SET status = 'acquired', stock = 0 WHERE id = ?")->execute([$id]);
            jsonResponse(true, "Produk '{$product['name']}' telah memiliki riwayat pesanan, status dialihkan ke Arsip Vault (Acquired/Stok 0).", [
                'archived' => true
            ]);
        }
        break;

    case 'update_status':
        requireAdmin();
        $id = (int)($input['id'] ?? 0);
        $status = $input['status'] ?? '';

        if (!in_array($status, ['available', 'reserved', 'acquired'])) {
            jsonResponse(false, 'Status inventaris tidak valid.', [], 400);
        }

        $db->prepare("UPDATE products SET status = ? WHERE id = ?")->execute([$status, $id]);
        jsonResponse(true, "Status ketersediaan flacon edisi berhasil diperbarui ke: {$status}", [
            'id' => $id,
            'status' => $status
        ]);
        break;

    default:
        jsonResponse(false, 'Aksi katalog tidak dikenal.', [], 400);
}

function selfInsertNotes(PDO $db, int $productId, array $input): void {
    if (!empty($input['top_notes'])) {
        $notes = is_array($input['top_notes']) ? $input['top_notes'] : explode(',', (string)$input['top_notes']);
        foreach ($notes as $n) {
            $nStr = is_array($n) ? ($n['note_name'] ?? '') : trim((string)$n);
            $desc = is_array($n) ? ($n['description'] ?? '') : 'Nuansa top notes segar alami';
            if (!empty($nStr)) {
                $db->prepare("INSERT INTO olfactory_notes (product_id, note_type, note_name, description) VALUES (?, 'top', ?, ?)")->execute([$productId, $nStr, $desc]);
            }
        }
    }
    if (!empty($input['heart_notes'])) {
        $notes = is_array($input['heart_notes']) ? $input['heart_notes'] : explode(',', (string)$input['heart_notes']);
        foreach ($notes as $n) {
            $nStr = is_array($n) ? ($n['note_name'] ?? '') : trim((string)$n);
            $desc = is_array($n) ? ($n['description'] ?? '') : 'Sentuhan heart note olfaktori intim';
            if (!empty($nStr)) {
                $db->prepare("INSERT INTO olfactory_notes (product_id, note_type, note_name, description) VALUES (?, 'heart', ?, ?)")->execute([$productId, $nStr, $desc]);
            }
        }
    }
    if (!empty($input['base_notes'])) {
        $notes = is_array($input['base_notes']) ? $input['base_notes'] : explode(',', (string)$input['base_notes']);
        foreach ($notes as $n) {
            $nStr = is_array($n) ? ($n['note_name'] ?? '') : trim((string)$n);
            $desc = is_array($n) ? ($n['description'] ?? '') : 'Pondasi base note tahan 18+ jam';
            if (!empty($nStr)) {
                $db->prepare("INSERT INTO olfactory_notes (product_id, note_type, note_name, description) VALUES (?, 'base', ?, ?)")->execute([$productId, $nStr, $desc]);
            }
        }
    }
}
