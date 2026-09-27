<?php
// ===================================================
// Kuro Atelier - High-Value Acquisition & Orders API
// Handles Private Wire Transfers, Escrow, and Cryptographic COA
// ===================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

initSession();
$user = requireAuth();
$db = Database::getConnection();
$input = getJsonInput();
$action = $_GET['action'] ?? ($input['action'] ?? 'my_acquisitions');

switch ($action) {
    case 'create_acquisition':
        // Check whitelist status
        if (!isWhitelisted()) {
            jsonResponse(false, 'Hak istimewa akuisisi hanya diperuntukkan bagi kolektor yang telah disetujui dalam Whitelist Kuro.', [], 403);
        }

        $productId = (int)($input['product_id'] ?? 0);
        $paymentMethod = trim($input['payment_method'] ?? 'BCA Prioritas Executive Wire Transfer');
        $deliveryAddress = trim($input['delivery_address'] ?? '');
        $customEngraving = trim($input['custom_engraving'] ?? '');
        $wireReference = trim($input['wire_reference'] ?? '');

        if (!$productId || empty($deliveryAddress)) {
            jsonResponse(false, 'Produk dan alamat pengantaran privat wajib disertakan.', [], 400);
        }

        // Verify product availability
        $stmtP = $db->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
        $db->beginTransaction();
        $stmtP->execute([$productId]);
        $product = $stmtP->fetch();

        if (!$product) {
            $db->rollBack();
            jsonResponse(false, 'Karya parfum tidak ditemukan.', [], 404);
        }

        if ($product['status'] === 'acquired') {
            $db->rollBack();
            jsonResponse(false, 'Karya edisi 1-of-1 ini telah resmi diakuisisi oleh kolektor lain dan telah ditutup dalam brankas (Vaulted).', [], 409);
        }

        // Generate high-value order identifier and Certificate of Authenticity (COA)
        $orderNumber = 'KURO-ACQ-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $coaSerial = 'COA-KURO-' . str_pad((string)$product['id'], 3, '0', STR_PAD_LEFT) . '-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        
        // Cryptographic Hash of Ownership: product + collector email + timestamp + atelier salt
        $coaHash = hash('sha256', $product['edition_serial'] . '|' . $user['email'] . '|' . microtime(true) . '|KURO_ATELIER_TOKYO');

        $totalAmount = (float)$product['price'];

        // Insert Order
        $stmtOrder = $db->prepare("
            INSERT INTO orders (
                order_number, user_id, product_id, total_amount, payment_method, 
                payment_status, delivery_address, custom_engraving, wire_reference, 
                coa_serial, coa_hash, acquired_at, created_at
            ) VALUES (?, ?, ?, ?, ?, 'confirmed', ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmtOrder->execute([
            $orderNumber, $user['id'], $product['id'], $totalAmount, $paymentMethod,
            $deliveryAddress, $customEngraving, $wireReference, $coaSerial, $coaHash
        ]);
        $orderId = (int)$db->lastInsertId();

        // Mark product as acquired (since it is 1-of-1 edition, no other person in the world can own it!)
        $db->prepare("UPDATE products SET status = 'acquired' WHERE id = ?")->execute([$productId]);

        // Auto-send confirmation notice to Concierge thread
        $conMsg = "Akuisisi Bersejarah Dikonfirmasi: Anda telah berhasil mengamankan kepemilikan tunggal dunia atas {$product['name']} ({$product['edition_serial']}). Nomor Arsip Akuisisi: {$orderNumber}. Sertifikat Keaslian (COA): {$coaSerial}. Tim pengawalan diplomatik Kuro akan segera menghubungi Anda.";
        $threadId = "thread_client_{$user['id']}";
        $db->prepare("
            INSERT INTO concierge_messages (thread_id, sender_id, sender_name, sender_role, product_id, message, is_read, created_at)
            VALUES (?, 1, 'Kuro (黒) Master Perfumer', 'kuro_admin', ?, ?, 0, NOW())
        ")->execute([$threadId, $productId, $conMsg]);

        $db->commit();

        jsonResponse(true, 'Selamat! Akuisisi karya edisi 1-of-1 dunia ini resmi tercatat atas nama Anda.', [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'coa_serial' => $coaSerial,
            'coa_hash' => $coaHash,
            'product_name' => $product['name'],
            'total_amount' => $totalAmount,
            'total_formatted' => formatRupiah($totalAmount)
        ]);
        break;

    case 'my_acquisitions':
        $stmt = $db->prepare("
            SELECT o.*, p.name as product_name, p.subtitle, p.japanese_name, p.edition_serial, p.image_url, p.concentration
            FROM orders o
            JOIN products p ON o.product_id = p.id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $orders = $stmt->fetchAll();

        foreach ($orders as &$o) {
            $o['total_formatted'] = formatRupiah((float)$o['total_amount']);
        }
        unset($o);

        jsonResponse(true, 'Daftar portofolio akuisisi karya Kuro Anda.', ['acquisitions' => $orders]);
        break;

    case 'verify_coa':
        $serial = trim($_GET['serial'] ?? ($input['serial'] ?? ''));
        if (empty($serial)) {
            jsonResponse(false, 'Nomor seri sertifikat keaslian (COA) wajib dimasukkan.', [], 400);
        }

        $stmt = $db->prepare("
            SELECT o.coa_serial, o.coa_hash, o.order_number, o.acquired_at, o.custom_engraving,
                   u.name as owner_name, u.title_company,
                   p.name as product_name, p.japanese_name, p.edition_serial, p.concentration, p.image_url, p.description
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN products p ON o.product_id = p.id
            WHERE o.coa_serial = ? OR o.coa_hash = ?
        ");
        $stmt->execute([$serial, $serial]);
        $cert = $stmt->fetch();

        if (!$cert) {
            jsonResponse(false, 'Sertifikat tidak terdaftar atau palsu dalam arsip resmi Kuro Tokyo.', [], 404);
        }

        jsonResponse(true, 'Sertifikat Keaslian (Certificate of Authenticity) Terverifikasi 100% Asli.', [
            'certificate' => $cert,
            'verified' => true,
            'atelier_authority' => 'Kuro Atelier Imperial Chamber, Tokyo'
        ]);
        break;

    case 'all_orders':
        requireAdmin();
        $stmt = $db->query("
            SELECT o.*, u.name as client_name, u.email as client_email, u.title_company,
                   p.name as product_name, p.edition_serial
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN products p ON o.product_id = p.id
            ORDER BY o.created_at DESC
        ");
        $orders = $stmt->fetchAll();
        foreach ($orders as &$o) {
            $o['total_formatted'] = formatRupiah((float)$o['total_amount']);
        }
        unset($o);

        jsonResponse(true, 'Seluruh riwayat akuisisi kolektor Kuro.', ['orders' => $orders]);
        break;

    default:
        jsonResponse(false, 'Aksi transaksi tidak dikenal.', [], 400);
}
