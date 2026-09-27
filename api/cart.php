<?php
// ===================================================
// Kuro Atelier - Shopping Cart & Series 24 API
// Handles Add to Cart, Quantity, and Checkout Review
// ===================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

initSession();
$user = getCurrentUser();
$db = Database::getConnection();
$input = getJsonInput();
$action = $_GET['action'] ?? ($input['action'] ?? 'get');

// If user is not yet logged in:
if (!$user) {
    if ($action === 'get') {
        // Return empty cart for unauthenticated visitors without creating or modifying session
        jsonResponse(true, 'Keranjang belanja kosong (Mode Tamu).', [
            'items' => [],
            'total_items' => 0,
            'subtotal' => 0,
            'subtotal_formatted' => 'Rp 0'
        ]);
    } else {
        jsonResponse(false, 'Silakan masuk ke akun Anda terlebih dahulu untuk berbelanja.', [], 401);
    }
}

switch ($action) {
    case 'get':
        $stmt = $db->prepare("
            SELECT c.id as cart_item_id, c.quantity, p.id as product_id, p.name, p.subtitle, 
                   p.japanese_name, p.edition_type, p.edition_serial, p.price, p.stock, 
                   p.image_url, p.status
            FROM cart_items c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $items = $stmt->fetchAll();

        $subtotal = 0;
        foreach ($items as &$item) {
            $item['price_raw'] = (float)$item['price'];
            $item['price_formatted'] = formatRupiah((float)$item['price']);
            $itemTotal = (float)$item['price'] * (int)$item['quantity'];
            $item['item_total'] = $itemTotal;
            $item['item_total_formatted'] = formatRupiah($itemTotal);
            $subtotal += $itemTotal;
        }
        unset($item);

        jsonResponse(true, 'Keranjang belanja Kuro berhasil dimuat.', [
            'items' => $items,
            'total_items' => array_sum(array_column($items, 'quantity')),
            'subtotal' => $subtotal,
            'subtotal_formatted' => formatRupiah($subtotal),
            'user' => $user
        ]);
        break;

    case 'add':
        $productId = (int)($input['product_id'] ?? 0);
        $qty = max(1, (int)($input['quantity'] ?? 1));

        if (!$productId) {
            jsonResponse(false, 'Produk tidak valid.', [], 400);
        }

        // Verify product & stock
        $stmtP = $db->prepare("SELECT id, name, edition_type, stock, status, price FROM products WHERE id = ?");
        $stmtP->execute([$productId]);
        $prod = $stmtP->fetch();

        if (!$prod) {
            jsonResponse(false, 'Produk tidak ditemukan.', [], 404);
        }

        if ($prod['status'] !== 'available' || (int)$prod['stock'] < 1) {
            jsonResponse(false, 'Mohon maaf, stok alokasi botol ini telah habis.', [], 409);
        }

        // Check if item already in cart
        $checkStmt = $db->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
        $checkStmt->execute([$user['id'], $productId]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            $newQty = $existing['quantity'] + $qty;
            if ($newQty > $prod['stock']) {
                $newQty = $prod['stock'];
            }
            $upd = $db->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $upd->execute([$newQty, $existing['id']]);
        } else {
            if ($qty > $prod['stock']) {
                $qty = $prod['stock'];
            }
            $ins = $db->prepare("INSERT INTO cart_items (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
            $ins->execute([$user['id'], $productId, $qty]);
        }

        // Get total count in cart
        $countStmt = $db->prepare("SELECT SUM(quantity) FROM cart_items WHERE user_id = ?");
        $countStmt->execute([$user['id']]);
        $totalCount = (int)$countStmt->fetchColumn();

        jsonResponse(true, "{$prod['name']} berhasil ditambahkan ke keranjang belanja Anda.", [
            'total_items' => $totalCount,
            'product_name' => $prod['name'],
            'user' => $user
        ]);
        break;

    case 'update':
        $cartItemId = (int)($input['cart_item_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 1);

        if (!$cartItemId) {
            jsonResponse(false, 'Item keranjang tidak valid.', [], 400);
        }

        if ($quantity <= 0) {
            $db->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?")->execute([$cartItemId, $user['id']]);
            jsonResponse(true, 'Item berhasil dihapus dari keranjang.');
        } else {
            // Check stock limit
            $stmtC = $db->prepare("
                SELECT c.id, p.stock 
                FROM cart_items c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.id = ? AND c.user_id = ?
            ");
            $stmtC->execute([$cartItemId, $user['id']]);
            $item = $stmtC->fetch();

            if (!$item) {
                jsonResponse(false, 'Item tidak ditemukan.', [], 404);
            }

            if ($quantity > $item['stock']) {
                $quantity = $item['stock'];
            }

            $db->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?")->execute([$quantity, $cartItemId]);
            jsonResponse(true, 'Kuantitas berhasil diperbarui.', ['quantity' => $quantity]);
        }
        break;

    case 'remove':
        $cartItemId = (int)($input['cart_item_id'] ?? 0);
        $db->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?")->execute([$cartItemId, $user['id']]);
        jsonResponse(true, 'Item berhasil dihapus dari keranjang.');
        break;

    case 'clear':
        $db->prepare("DELETE FROM cart_items WHERE user_id = ?")->execute([$user['id']]);
        jsonResponse(true, 'Keranjang belanja telah dikosongkan.');
        break;

    case 'checkout':
        // CHECKOUT FLOW (Stops at checkout review/order confirmation, BEFORE payment method per user instruction)
        $deliveryAddress = trim($input['delivery_address'] ?? '');
        $recipientName = trim($input['recipient_name'] ?? $user['name']);
        $recipientPhone = trim($input['recipient_phone'] ?? '');
        $orderNotes = trim($input['order_notes'] ?? '');

        if (empty($deliveryAddress)) {
            jsonResponse(false, 'Alamat pengiriman wajib diisi untuk memproses checkout.', [], 400);
        }

        // Fetch cart items with full product details
        $stmt = $db->prepare("
            SELECT c.product_id, c.quantity, p.name, p.subtitle, p.japanese_name, p.price, p.stock, 
                   p.edition_type, p.edition_serial, p.image_url, p.concentration, p.volume_ml
            FROM cart_items c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user['id']]);
        $items = $stmt->fetchAll();

        if (empty($items)) {
            jsonResponse(false, 'Keranjang belanja Anda masih kosong.', [], 400);
        }

        // Start transaction
        $db->beginTransaction();
        try {
            $totalAmount = 0;
            $formattedItems = [];
            foreach ($items as $item) {
                // Verify stock
                if ($item['stock'] < $item['quantity']) {
                    throw new Exception("Stok untuk {$item['name']} tidak mencukupi (Tersisa: {$item['stock']}).");
                }
                $itemSubtotal = (float)$item['price'] * (int)$item['quantity'];
                $totalAmount += $itemSubtotal;
                $formattedItems[] = array_merge($item, [
                    'price_formatted' => formatRupiah((float)$item['price']),
                    'subtotal_raw' => $itemSubtotal,
                    'subtotal_formatted' => formatRupiah($itemSubtotal)
                ]);
            }

            $orderNumber = 'KURO-ORD-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $invoiceNumber = 'INV/KURO-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $invoiceHash = strtoupper(hash('sha256', $invoiceNumber . $orderNumber . $user['id'] . $totalAmount . microtime(true)));
            $fullAddress = "Penerima: {$recipientName} ({$recipientPhone})\nAlamat: {$deliveryAddress}\nCatatan: {$orderNotes}";

            // Insert master order (payment_status = 'checkout_review', payment_method NULL)
            $stmtOrder = $db->prepare("
                INSERT INTO orders (
                    order_number, invoice_number, invoice_hash, user_id, product_id, total_amount, payment_method, 
                    payment_status, delivery_address, custom_engraving, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NULL, 'checkout_review', ?, ?, NOW())
            ");
            // primary product id
            $primaryProductId = $items[0]['product_id'];
            $stmtOrder->execute([$orderNumber, $invoiceNumber, $invoiceHash, $user['id'], $primaryProductId, $totalAmount, $fullAddress, $orderNotes]);
            $orderId = (int)$db->lastInsertId();

            // Insert order items & reduce stock
            $stmtItem = $db->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price_per_item, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtStock = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            foreach ($formattedItems as $fItem) {
                $stmtItem->execute([$orderId, $fItem['product_id'], $fItem['quantity'], $fItem['price'], $fItem['subtotal_raw']]);
                $stmtStock->execute([$fItem['quantity'], $fItem['product_id']]);
            }

            // Clear user's cart
            $db->prepare("DELETE FROM cart_items WHERE user_id = ?")->execute([$user['id']]);

            $db->commit();

            // Format Indonesian date for invoice
            $monthsIndo = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $monthNum = (int)date('n');
            $issuedDate = date('d') . ' ' . $monthsIndo[$monthNum] . ' ' . date('Y') . ', ' . date('H:i') . ' WIB';

            jsonResponse(true, 'Bukti Invoice Resmi berhasil diterbitkan! Alokasi pesanan Kuro Series 24 Anda telah tercatat dan dikunci.', [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'invoice_number' => $invoiceNumber,
                'invoice_hash' => $invoiceHash,
                'issued_at' => $issuedDate,
                'customer_name' => $user['name'],
                'customer_email' => $user['email'],
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'delivery_address' => $deliveryAddress,
                'order_notes' => $orderNotes ?: '-',
                'items_count' => count($formattedItems),
                'items' => $formattedItems,
                'shipping_fee' => 0,
                'shipping_fee_formatted' => 'GRATIS (VIP Concierge Courier)',
                'tax_fee' => 0,
                'tax_fee_formatted' => 'Termasuk (0% Pajak Eksekutif)',
                'subtotal_amount' => $totalAmount,
                'subtotal_formatted' => formatRupiah($totalAmount),
                'total_amount' => $totalAmount,
                'total_formatted' => formatRupiah($totalAmount),
                'status' => 'checkout_review',
                'status_label' => 'Alokasi Terkunci (Tahap Review Selesai - Menunggu Pembayaran)'
            ]);

        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Gagal memproses checkout: ' . $e->getMessage(), [], 400);
        }
        break;

    default:
        jsonResponse(false, 'Aksi keranjang tidak dikenal.', [], 400);
}
