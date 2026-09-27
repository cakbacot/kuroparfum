<?php
// ===================================================
// Kuro Atelier - Orders & Order Lifecycle Management API
// Handles Acquisitions, Cart Orders, Payment Status & Fulfillment Tracking
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
        $invoiceNumber = 'INV/KURO-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $coaSerial = 'COA-KURO-' . str_pad((string)$product['id'], 3, '0', STR_PAD_LEFT) . '-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        
        // Cryptographic Hash of Ownership: product + collector email + timestamp + atelier salt
        $coaHash = hash('sha256', $product['edition_serial'] . '|' . $user['email'] . '|' . microtime(true) . '|KURO_ATELIER_TOKYO');
        $invoiceHash = strtoupper(hash('sha256', $invoiceNumber . $orderNumber . $user['id'] . $product['price']));

        $totalAmount = (float)$product['price'];

        // Insert Order (Default fulfillment: dikemas / sedang dikemas)
        $stmtOrder = $db->prepare("
            INSERT INTO orders (
                order_number, invoice_number, invoice_hash, user_id, product_id, total_amount, payment_method, 
                payment_status, fulfillment_status, delivery_address, custom_engraving, wire_reference, 
                coa_serial, coa_hash, acquired_at, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', 'dikemas', ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmtOrder->execute([
            $orderNumber, $invoiceNumber, $invoiceHash, $user['id'], $product['id'], $totalAmount, $paymentMethod,
            $deliveryAddress, $customEngraving, $wireReference, $coaSerial, $coaHash
        ]);
        $orderId = (int)$db->lastInsertId();

        // Also add entry to order_items
        $db->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price_per_item, subtotal)
            VALUES (?, ?, 1, ?, ?)
        ")->execute([$orderId, $product['id'], $totalAmount, $totalAmount]);

        // Mark product as acquired (since it is 1-of-1 edition)
        $db->prepare("UPDATE products SET status = 'acquired', stock = 0 WHERE id = ?")->execute([$productId]);

        // Auto-send confirmation notice to Concierge thread
        $conMsg = "Akuisisi Bersejarah Dikonfirmasi: Anda telah berhasil mengamankan kepemilikan tunggal dunia atas {$product['name']} ({$product['edition_serial']}). Nomor Arsip: {$orderNumber}. Invoice: {$invoiceNumber}. COA: {$coaSerial}. Status pengiriman: Sedang Dikemas secara privat oleh Master Kuro.";
        $threadId = "thread_client_{$user['id']}";
        $db->prepare("
            INSERT INTO concierge_messages (thread_id, sender_id, sender_name, sender_role, product_id, message, is_read, created_at)
            VALUES (?, 1, 'Kuro (黒) Master Perfumer', 'kuro_admin', ?, ?, 0, NOW())
        ")->execute([$threadId, $productId, $conMsg]);

        $db->commit();

        jsonResponse(true, 'Selamat! Akuisisi karya edisi 1-of-1 dunia ini resmi tercatat atas nama Anda.', [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'invoice_number' => $invoiceNumber,
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
            LEFT JOIN products p ON o.product_id = p.id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $orders = $stmt->fetchAll();

        // Fetch items for each order
        $orderIds = array_column($orders, 'id');
        $itemsByOrder = [];
        if (!empty($orderIds)) {
            $inPlaceholders = implode(',', array_fill(0, count($orderIds), '?'));
            $itemStmt = $db->prepare("
                SELECT oi.*, p.name as product_name, p.edition_serial, p.image_url, p.edition_type
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id IN ($inPlaceholders)
            ");
            $itemStmt->execute($orderIds);
            $allItems = $itemStmt->fetchAll();
            foreach ($allItems as $it) {
                $it['price_formatted'] = formatRupiah((float)$it['price_per_item']);
                $it['subtotal_formatted'] = formatRupiah((float)$it['subtotal']);
                $itemsByOrder[$it['order_id']][] = $it;
            }
        }

        foreach ($orders as &$o) {
            $o['total_formatted'] = formatRupiah((float)$o['total_amount']);
            $o['items'] = $itemsByOrder[$o['id']] ?? [];
            if (empty($o['items']) && !empty($o['product_name'])) {
                $o['items'][] = [
                    'product_id' => $o['product_id'],
                    'product_name' => $o['product_name'],
                    'edition_serial' => $o['edition_serial'],
                    'image_url' => $o['image_url'],
                    'quantity' => 1,
                    'price_formatted' => formatRupiah((float)$o['total_amount']),
                    'subtotal_formatted' => formatRupiah((float)$o['total_amount'])
                ];
            }
        }
        unset($o);

        jsonResponse(true, 'Daftar portofolio pesanan dan akuisisi karya Kuro Anda.', ['acquisitions' => $orders]);
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
        $statusFilter = trim($_GET['payment_status'] ?? '');
        $fulfillmentFilter = trim($_GET['fulfillment_status'] ?? '');
        $search = trim($_GET['search'] ?? '');

        $sql = "
            SELECT o.*, u.name as client_name, u.email as client_email, u.title_company,
                   p.name as primary_product_name, p.edition_serial as primary_product_serial, p.image_url as primary_image
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN products p ON o.product_id = p.id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($statusFilter)) {
            $sql .= " AND o.payment_status = ?";
            $params[] = $statusFilter;
        }
        if (!empty($fulfillmentFilter)) {
            $sql .= " AND o.fulfillment_status = ?";
            $params[] = $fulfillmentFilter;
        }
        if (!empty($search)) {
            $sql .= " AND (o.order_number LIKE ? OR o.invoice_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY o.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        // Fetch order items for each order
        $orderIds = array_column($orders, 'id');
        $itemsByOrder = [];
        if (!empty($orderIds)) {
            $inPlaceholders = implode(',', array_fill(0, count($orderIds), '?'));
            $itemStmt = $db->prepare("
                SELECT oi.*, p.name as product_name, p.edition_serial, p.image_url, p.edition_type
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id IN ($inPlaceholders)
            ");
            $itemStmt->execute($orderIds);
            $items = $itemStmt->fetchAll();
            foreach ($items as $it) {
                $it['price_formatted'] = formatRupiah((float)$it['price_per_item']);
                $it['subtotal_formatted'] = formatRupiah((float)$it['subtotal']);
                $itemsByOrder[$it['order_id']][] = $it;
            }
        }

        foreach ($orders as &$o) {
            $o['total_formatted'] = formatRupiah((float)$o['total_amount']);
            $o['items'] = $itemsByOrder[$o['id']] ?? [];
            if (empty($o['items']) && !empty($o['primary_product_name'])) {
                $o['items'][] = [
                    'product_id' => $o['product_id'],
                    'product_name' => $o['primary_product_name'],
                    'edition_serial' => $o['primary_product_serial'],
                    'image_url' => $o['primary_image'],
                    'quantity' => 1,
                    'price_formatted' => formatRupiah((float)$o['total_amount']),
                    'subtotal_formatted' => formatRupiah((float)$o['total_amount'])
                ];
            }
        }
        unset($o);

        jsonResponse(true, 'Seluruh riwayat pesanan website berhasil dimuat.', [
            'orders' => $orders,
            'total_count' => count($orders)
        ]);
        break;

    case 'update_order_status':
        requireAdmin();
        $orderId = (int)($input['order_id'] ?? 0);
        $paymentStatus = trim($input['payment_status'] ?? '');
        $fulfillmentStatus = trim($input['fulfillment_status'] ?? '');
        $courierName = trim($input['courier_name'] ?? '');
        $trackingNumber = trim($input['tracking_number'] ?? '');
        $fulfillmentNotes = trim($input['fulfillment_notes'] ?? '');

        if ($orderId <= 0) {
            jsonResponse(false, 'ID Pesanan tidak valid.', [], 400);
        }

        // Fetch current order
        $checkStmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $checkStmt->execute([$orderId]);
        $order = $checkStmt->fetch();

        if (!$order) {
            jsonResponse(false, 'Pesanan tidak ditemukan.', [], 404);
        }

        // Validate statuses if provided
        $validPayments = ['checkout_review', 'pending_verification', 'confirmed', 'completed', 'cancelled'];
        $validFulfillments = ['menunggu', 'dikemas', 'dikirim', 'selesai', 'dibatalkan'];

        $newPayment = (!empty($paymentStatus) && in_array($paymentStatus, $validPayments)) ? $paymentStatus : $order['payment_status'];
        $newFulfillment = (!empty($fulfillmentStatus) && in_array($fulfillmentStatus, $validFulfillments)) ? $fulfillmentStatus : ($order['fulfillment_status'] ?? 'menunggu');

        $updCourier = $courierName !== '' ? $courierName : $order['courier_name'];
        $updTracking = $trackingNumber !== '' ? $trackingNumber : $order['tracking_number'];
        $updNotes = $fulfillmentNotes !== '' ? $fulfillmentNotes : $order['fulfillment_notes'];

        $updStmt = $db->prepare("
            UPDATE orders 
            SET payment_status = ?, fulfillment_status = ?, courier_name = ?, tracking_number = ?, fulfillment_notes = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $updStmt->execute([$newPayment, $newFulfillment, $updCourier, $updTracking, $updNotes, $orderId]);

        // Human-readable labels for client notification
        $fulfillmentLabels = [
            'menunggu' => 'Menunggu Diproses',
            'dikemas' => 'Sedang Dikemas (Packaging)',
            'dikirim' => 'Sedang Dalam Pengiriman',
            'selesai' => 'Barang Sudah Diterima',
            'dibatalkan' => 'Pesanan Dibatalkan'
        ];
        $paymentLabels = [
            'checkout_review' => 'Menunggu Pembayaran (Review)',
            'pending_verification' => 'Menunggu Verifikasi Bank',
            'confirmed' => 'Pembayaran Lunas (Terkonfirmasi)',
            'completed' => 'Transaksi Selesai & Lunas',
            'cancelled' => 'Dibatalkan'
        ];

        // Send Concierge notification to client
        $notifyMsg = "Pembaruan Status Pesanan #{$order['order_number']}:\n" .
                     "• Status Pengiriman: " . ($fulfillmentLabels[$newFulfillment] ?? $newFulfillment) . "\n" .
                     "• Status Pembayaran: " . ($paymentLabels[$newPayment] ?? $newPayment);
        
        if (!empty($updCourier) && !empty($updTracking)) {
            $notifyMsg .= "\n• Ekspedisi / No. Resi: {$updCourier} ({$updTracking})";
        }
        if (!empty($updNotes)) {
            $notifyMsg .= "\n• Catatan Atelier: {$updNotes}";
        }

        $threadId = "thread_client_{$order['user_id']}";
        $db->prepare("
            INSERT INTO concierge_messages (thread_id, sender_id, sender_name, sender_role, product_id, message, is_read, created_at)
            VALUES (?, 1, 'Kuro (黒) Master Perfumer', 'kuro_admin', ?, ?, 0, NOW())
        ")->execute([$threadId, $order['product_id'] ?? null, $notifyMsg]);

        jsonResponse(true, "Status pesanan #{$order['order_number']} berhasil diperbarui.", [
            'order_id' => $orderId,
            'payment_status' => $newPayment,
            'payment_label' => $paymentLabels[$newPayment] ?? $newPayment,
            'fulfillment_status' => $newFulfillment,
            'fulfillment_label' => $fulfillmentLabels[$newFulfillment] ?? $newFulfillment,
            'courier_name' => $updCourier,
            'tracking_number' => $updTracking
        ]);
        break;

    default:
        jsonResponse(false, 'Aksi transaksi tidak dikenal.', [], 400);
}
