<?php
// ===================================================
// Kuro Atelier - Products & Bespoke Showcase API
// Handles Limited Editions, 1-of-1 Badging & Olfactory Notes
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
                $p['can_acquire'] = false; // Series 24 uses cart checkout
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

    case 'update_status':
        requireAdmin();
        $id = (int)($input['id'] ?? 0);
        $status = $input['status'] ?? '';

        if (!in_array($status, ['available', 'reserved', 'acquired'])) {
            jsonResponse(false, 'Status inventaris tidak valid.', [], 400);
        }

        $db->prepare("UPDATE products SET status = ? WHERE id = ?")->execute([$status, $id]);
        jsonResponse(true, "Status ketersediaan flacon edisi 1-of-1 berhasil diperbarui ke: {$status}", [
            'id' => $id,
            'status' => $status
        ]);
        break;

    case 'create':
        requireAdmin();
        $name = trim($input['name'] ?? '');
        $subtitle = trim($input['subtitle'] ?? '');
        $japaneseName = trim($input['japanese_name'] ?? '');
        $editionType = $input['edition_type'] ?? '1-of-1';
        $editionSerial = trim($input['edition_serial'] ?? '#KURO-00' . rand(4, 9) . '/01');
        $price = (float)($input['price'] ?? 50000000);
        $volumeMl = (int)($input['volume_ml'] ?? 100);
        $concentration = trim($input['concentration'] ?? 'Extrait de Parfum (35%)');
        $description = trim($input['description'] ?? '');
        $philosophy = trim($input['philosophy'] ?? '');
        $flaconCraftsmanship = trim($input['flacon_craftsmanship'] ?? '');
        $imageUrl = trim($input['image_url'] ?? 'assets/images/kuro_sumi.jpg');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        if (empty($name) || empty($description) || $price <= 0) {
            jsonResponse(false, 'Nama parfum, deskripsi, dan harga wajib diisi.', [], 400);
        }

        $stmt = $db->prepare("
            INSERT INTO products (slug, name, subtitle, japanese_name, edition_type, edition_serial, price, volume_ml, concentration, description, philosophy, flacon_craftsmanship, image_url, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', NOW())
        ");
        $stmt->execute([$slug, $name, $subtitle, $japaneseName, $editionType, $editionSerial, $price, $volumeMl, $concentration, $description, $philosophy, $flaconCraftsmanship, $imageUrl]);
        $newId = (int)$db->lastInsertId();

        // If top/heart/base notes were provided
        if (!empty($input['top_notes'])) {
            foreach ((array)$input['top_notes'] as $tn) {
                $db->prepare("INSERT INTO olfactory_notes (product_id, note_type, note_name) VALUES (?, 'top', ?)")->execute([$newId, trim($tn)]);
            }
        }
        if (!empty($input['heart_notes'])) {
            foreach ((array)$input['heart_notes'] as $hn) {
                $db->prepare("INSERT INTO olfactory_notes (product_id, note_type, note_name) VALUES (?, 'heart', ?)")->execute([$newId, trim($hn)]);
            }
        }
        if (!empty($input['base_notes'])) {
            foreach ((array)$input['base_notes'] as $bn) {
                $db->prepare("INSERT INTO olfactory_notes (product_id, note_type, note_name) VALUES (?, 'base', ?)")->execute([$newId, trim($bn)]);
            }
        }

        jsonResponse(true, "Karya parfum baru {$name} berhasil ditambahkan ke Showcase Kuro Atelier.", [
            'id' => $newId,
            'slug' => $slug
        ]);
        break;

    default:
        jsonResponse(false, 'Aksi katalog tidak dikenal.', [], 400);
}
