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
        $stmt = $db->query("SELECT * FROM products ORDER BY id ASC");
        $products = $stmt->fetchAll();

        // Fetch notes for each product
        $notesStmt = $db->query("SELECT * FROM olfactory_notes ORDER BY product_id, note_type");
        $allNotes = $notesStmt->fetchAll();
        $notesByProduct = [];
        foreach ($allNotes as $note) {
            $notesByProduct[$note['product_id']][$note['note_type']][] = $note;
        }

        $user = getCurrentUser();
        foreach ($products as &$p) {
            $p['notes'] = $notesByProduct[$p['id']] ?? ['top' => [], 'heart' => [], 'base' => []];
            $p['stock'] = (int)($p['stock'] ?? 1);
            $isSeries24 = ($p['edition_type'] === 'Series-24');

            if ($isSeries24) {
                // Series 24: Accessible to all users for cart purchase
                $p['price_raw'] = (float)$p['price'];
                $p['price_formatted'] = formatRupiah((float)$p['price']);
                $p['can_cart'] = ($p['stock'] > 0);
                $p['can_acquire'] = false;
            } else {
                // 1-of-1 Bespoke: Whitelist required
                if (!$whitelisted) {
                    $p['price_raw'] = null;
                    $p['price_formatted'] = 'Terkunci (Akses Whitelist Diperlukan)';
                    $p['can_acquire'] = false;
                    $p['can_cart'] = false;
                } else {
                    $p['price_raw'] = (float)$p['price'];
                    $p['price_formatted'] = formatRupiah((float)$p['price']);
                    $p['can_acquire'] = ($p['status'] === 'available');
                    $p['can_cart'] = false;
                }
            }
        }
        unset($p);

        jsonResponse(true, 'Katalog Kuro Bespoke Showcase berhasil dimuat.', [
            'products' => $products,
            'is_whitelisted' => $whitelisted
        ]);
        break;

    case 'admin_list':
        requireAdmin();
        $stmt = $db->query("
            SELECT p.*,
                   (SELECT COUNT(*) FROM order_items oi WHERE oi.product_id = p.id) as total_sold_items
            FROM products p 
            ORDER BY p.id DESC
        ");
        $products = $stmt->fetchAll();

        $notesStmt = $db->query("SELECT * FROM olfactory_notes ORDER BY product_id, note_type");
        $allNotes = $notesStmt->fetchAll();
        $notesByProduct = [];
        foreach ($allNotes as $note) {
            $notesByProduct[$note['product_id']][$note['note_type']][] = $note;
        }

        foreach ($products as &$p) {
            $p['price_raw'] = (float)$p['price'];
            $p['price_formatted'] = formatRupiah((float)$p['price']);
            $p['stock'] = (int)($p['stock'] ?? 0);
            $p['notes'] = $notesByProduct[$p['id']] ?? ['top' => [], 'heart' => [], 'base' => []];
        }
        unset($p);

        jsonResponse(true, 'Daftar inventaris produk lengkap untuk Kuro Master Admin.', [
            'products' => $products,
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
        $name = trim($input['name'] ?? '');
        $subtitle = trim($input['subtitle'] ?? '');
        $japaneseName = trim($input['japanese_name'] ?? '');
        $editionType = $input['edition_type'] ?? 'Series-24';
        $editionSerial = trim($input['edition_serial'] ?? '');
        $price = (float)($input['price'] ?? 0);
        $stock = isset($input['stock']) ? (int)$input['stock'] : ($editionType === 'Series-24' ? 24 : 1);
        $volumeMl = (int)($input['volume_ml'] ?? 50);
        $concentration = trim($input['concentration'] ?? 'Eau de Parfum Intense (26% Concentration)');
        $description = trim($input['description'] ?? '');
        $philosophy = trim($input['philosophy'] ?? '');
        $flaconCraftsmanship = trim($input['flacon_craftsmanship'] ?? '');
        $imageUrl = trim($input['image_url'] ?? 'assets/images/kuro_series24_noir.jpg');
        $status = $input['status'] ?? 'available';

        if (empty($editionSerial)) {
            if ($editionType === 'Series-24') {
                $editionSerial = 'Series 24 Edition (Limit 24 Botol)';
            } else {
                $editionSerial = '#KURO-00' . rand(4, 99) . '/01 (1 of 1 Global Edition)';
            }
        }

        if (empty($name) || empty($description) || $price <= 0) {
            jsonResponse(false, 'Nama parfum, deskripsi, dan harga wajib diisi.', [], 400);
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
                slug, name, subtitle, japanese_name, edition_type, edition_serial, 
                price, stock, volume_ml, concentration, description, philosophy, 
                flacon_craftsmanship, image_url, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $slug, $name, $subtitle, $japaneseName, $editionType, $editionSerial,
            $price, $stock, $volumeMl, $concentration, $description, $philosophy,
            $flaconCraftsmanship, $imageUrl, $status
        ]);
        $newId = (int)$db->lastInsertId();

        // Olfactory notes
        selfInsertNotes($db, $newId, $input);

        jsonResponse(true, "Karya parfum baru '{$name}' berhasil ditambahkan ke katalog Kuro Atelier.", [
            'id' => $newId,
            'name' => $name,
            'slug' => $slug,
            'stock' => $stock
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
        $editionType = $input['edition_type'] ?? $existing['edition_type'];
        $editionSerial = trim($input['edition_serial'] ?? $existing['edition_serial']);
        $price = isset($input['price']) ? (float)$input['price'] : (float)$existing['price'];
        $stock = isset($input['stock']) ? (int)$input['stock'] : (int)$existing['stock'];
        $volumeMl = isset($input['volume_ml']) ? (int)$input['volume_ml'] : (int)$existing['volume_ml'];
        $concentration = trim($input['concentration'] ?? $existing['concentration']);
        $description = trim($input['description'] ?? $existing['description']);
        $philosophy = trim($input['philosophy'] ?? $existing['philosophy']);
        $flaconCraftsmanship = trim($input['flacon_craftsmanship'] ?? $existing['flacon_craftsmanship']);
        $imageUrl = trim($input['image_url'] ?? $existing['image_url']);
        $status = $input['status'] ?? $existing['status'];

        if (empty($name) || $price <= 0) {
            jsonResponse(false, 'Nama parfum dan harga tidak boleh kosong.', [], 400);
        }

        $upd = $db->prepare("
            UPDATE products SET
                name = ?, subtitle = ?, japanese_name = ?, edition_type = ?, edition_serial = ?,
                price = ?, stock = ?, volume_ml = ?, concentration = ?, description = ?,
                philosophy = ?, flacon_craftsmanship = ?, image_url = ?, status = ?
            WHERE id = ?
        ");
        $upd->execute([
            $name, $subtitle, $japaneseName, $editionType, $editionSerial,
            $price, $stock, $volumeMl, $concentration, $description,
            $philosophy, $flaconCraftsmanship, $imageUrl, $status,
            $id
        ]);

        // If notes provided in update, update olfactory notes
        if (isset($input['top_notes']) || isset($input['heart_notes']) || isset($input['base_notes'])) {
            $db->prepare("DELETE FROM olfactory_notes WHERE product_id = ?")->execute([$id]);
            selfInsertNotes($db, $id, $input);
        }

        jsonResponse(true, "Karya parfum '{$name}' berhasil diperbarui.", [
            'id' => $id,
            'name' => $name,
            'stock' => $stock,
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

        // Delete notes, cart items, and product
        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM olfactory_notes WHERE product_id = ?")->execute([$id]);
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
