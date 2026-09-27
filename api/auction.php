<?php
// ===================================================
// Kuro Atelier - Auction API
// Handles bidding, auction access/tickets, admin management
// ===================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

initSession();
$db    = Database::getConnection();
$input = getJsonInput();
$action = $_GET['action'] ?? ($input['action'] ?? '');

switch ($action) {

    // ─── PUBLIC: List all auction products ───────────────────────────
    case 'list':
        $user       = getCurrentUser();
        $isLoggedIn = $user !== null;
        $userId     = $user ? (int)$user['id'] : 0;
        $isApprovedOrAdmin = $user && ($user['role'] === 'kuro_admin' || ($user['membership_status'] ?? '') === 'approved');

        $stmt = $db->query("
            SELECT p.*,
                   psv.series_category,
                   (SELECT MAX(ab.bid_amount) FROM auction_bids ab WHERE ab.product_id = p.id) AS current_bid,
                   (SELECT COUNT(*) FROM auction_bids ab WHERE ab.product_id = p.id) AS total_bids
            FROM products p
            LEFT JOIN product_series_view psv ON psv.id = p.id
            WHERE p.is_auction = 1
            ORDER BY 
                CASE p.auction_status
                    WHEN 'open' THEN 1
                    WHEN 'upcoming' THEN 2
                    WHEN 'closed' THEN 3
                    ELSE 4
                END ASC,
                p.auction_end_time ASC
        ");
        $auctions = $stmt->fetchAll();

        // Mark which auctions this user has access to
        $accessMap = [];
        if ($userId > 0) {
            $accStmt = $db->prepare("SELECT product_id FROM auction_access WHERE user_id = ?");
            $accStmt->execute([$userId]);
            foreach ($accStmt->fetchAll() as $row) {
                $accessMap[$row['product_id']] = true;
            }
        }

        foreach ($auctions as &$a) {
            $a['price_formatted']  = formatRupiah((float)$a['auction_start_price']);
            $a['current_bid_fmt']  = $a['current_bid'] ? formatRupiah((float)$a['current_bid']) : null;
            $a['increment_fmt']    = $a['auction_increment'] ? formatRupiah((float)$a['auction_increment']) : null;
            $hasTicket             = isset($accessMap[$a['id']]);
            $a['has_access']       = $hasTicket || $isApprovedOrAdmin;
            $a['can_bid']          = $a['has_access'] && $a['auction_status'] === 'open';
            $a['next_minimum_bid'] = $a['current_bid']
                ? (float)$a['current_bid'] + (float)$a['auction_increment']
                : (float)$a['auction_start_price'];
            $a['next_minimum_bid_fmt'] = formatRupiah($a['next_minimum_bid']);
        }
        unset($a);

        jsonResponse(true, 'Daftar lelang Kuro berhasil dimuat.', [
            'auctions'     => $auctions,
            'is_logged_in' => $isLoggedIn,
            'is_verified'  => $isApprovedOrAdmin,
        ]);
        break;

    // ─── PUBLIC: Get auction detail + bid history ─────────────────────
    case 'detail':
        $productId = (int)($_GET['id'] ?? ($input['id'] ?? 0));
        if ($productId <= 0) jsonResponse(false, 'ID produk tidak valid.', [], 400);

        $stmt = $db->prepare("
            SELECT p.*, psv.series_category
            FROM products p
            LEFT JOIN product_series_view psv ON psv.id = p.id
            WHERE p.id = ? AND p.is_auction = 1
        ");
        $stmt->execute([$productId]);
        $auction = $stmt->fetch();
        if (!$auction) jsonResponse(false, 'Produk lelang tidak ditemukan.', [], 404);

        $user   = getCurrentUser();
        $userId = $user ? (int)$user['id'] : 0;
        $isApprovedOrAdmin = $user && ($user['role'] === 'kuro_admin' || ($user['membership_status'] ?? '') === 'approved');
        $hasAccess = $isApprovedOrAdmin;

        if ($userId > 0 && !$hasAccess) {
            $accStmt = $db->prepare("SELECT id FROM auction_access WHERE user_id = ? AND product_id = ?");
            $accStmt->execute([$userId, $productId]);
            $hasAccess = (bool)$accStmt->fetch();
        }

        // Bid history (public, show top 15)
        $bidsStmt = $db->prepare("
            SELECT ab.bid_amount, ab.bid_at,
                   CONCAT(SUBSTRING(u.name, 1, 2), REPEAT('*', GREATEST(0, CHAR_LENGTH(u.name)-3)), SUBSTRING(u.name, -1)) AS bidder_name,
                   ab.user_id
            FROM auction_bids ab
            JOIN users u ON u.id = ab.user_id
            WHERE ab.product_id = ?
            ORDER BY ab.bid_amount DESC
            LIMIT 15
        ");
        $bidsStmt->execute([$productId]);
        $bids = $bidsStmt->fetchAll();

        $currentBid = count($bids) > 0 ? (float)$bids[0]['bid_amount'] : (float)$auction['auction_start_price'];
        $nextMin    = $currentBid + (float)$auction['auction_increment'];

        $auction['price_formatted']      = formatRupiah((float)$auction['auction_start_price']);
        $auction['current_bid']          = $currentBid;
        $auction['current_bid_fmt']      = formatRupiah($currentBid);
        $auction['next_minimum_bid']     = $nextMin;
        $auction['next_minimum_bid_fmt'] = formatRupiah($nextMin);
        $auction['has_access']           = $hasAccess;
        $auction['can_bid']              = $hasAccess && $auction['auction_status'] === 'open';

        foreach ($bids as &$b) {
            $b['bid_amount_fmt'] = formatRupiah((float)$b['bid_amount']);
            $b['is_mine']        = ($userId > 0 && (int)$b['user_id'] === $userId);
        }
        unset($b);

        jsonResponse(true, 'Detail lelang ' . $auction['name'], [
            'auction'     => $auction,
            'bids'        => $bids,
            'has_access'  => $hasAccess,
            'can_bid'     => $auction['can_bid'],
            'is_verified' => $isApprovedOrAdmin,
        ]);
        break;

    // ─── USER: Place a bid ────────────────────────────────────────────
    case 'bid':
        requireLogin();
        $user      = getCurrentUser();
        $userId    = (int)$user['id'];
        $productId = (int)($input['product_id'] ?? 0);
        $bidAmount = (float)($input['bid_amount'] ?? 0);

        if ($productId <= 0 || $bidAmount <= 0) {
            jsonResponse(false, 'Data penawaran bid tidak valid atau nominal kosong.', [], 400);
        }

        // Verify auction is open
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND is_auction = 1");
        $stmt->execute([$productId]);
        $auction = $stmt->fetch();
        if (!$auction) jsonResponse(false, 'Karya lelang tidak ditemukan.', [], 404);
        if ($auction['auction_status'] !== 'open') {
            jsonResponse(false, 'Lelang untuk karya ini belum dibuka atau telah ditutup.', [], 403);
        }

        // Check end time
        if ($auction['auction_end_time'] && strtotime($auction['auction_end_time']) < time()) {
            $db->prepare("UPDATE products SET auction_status = 'closed' WHERE id = ?")->execute([$productId]);
            jsonResponse(false, 'Waktu masa penawaran lelang telah berakhir.', [], 403);
        }

        // Verify access: either user has ticket or is approved/whitelisted/admin
        $isApprovedOrAdmin = ($user['role'] === 'kuro_admin' || ($user['membership_status'] ?? '') === 'approved');
        $accStmt = $db->prepare("SELECT id FROM auction_access WHERE user_id = ? AND product_id = ?");
        $accStmt->execute([$userId, $productId]);
        $hasTicket = (bool)$accStmt->fetch();

        if (!$hasTicket && !$isApprovedOrAdmin) {
            jsonResponse(false, 'Kurasi VIP / Akun terverifikasi diperlukan untuk menempatkan penawaran lelang.', [], 403);
        }

        // Get current highest bid
        $maxBidStmt = $db->prepare("SELECT MAX(bid_amount) as max_bid FROM auction_bids WHERE product_id = ?");
        $maxBidStmt->execute([$productId]);
        $maxRow     = $maxBidStmt->fetch();
        $currentMax = $maxRow['max_bid'] ? (float)$maxRow['max_bid'] : (float)$auction['auction_start_price'];
        $minRequired = $maxRow['max_bid'] ? ($currentMax + (float)$auction['auction_increment']) : (float)$auction['auction_start_price'];

        if ($bidAmount < $minRequired) {
            jsonResponse(false, sprintf(
                'Bid minimum adalah %s (kelipatan kenaikan: %s).',
                formatRupiah($minRequired),
                formatRupiah((float)$auction['auction_increment'])
            ), ['min_required' => $minRequired], 400);
        }

        // Insert bid
        $db->prepare("INSERT INTO auction_bids (product_id, user_id, bid_amount) VALUES (?, ?, ?)")
           ->execute([$productId, $userId, $bidAmount]);

        jsonResponse(true, 'Penawaran bid Anda sebesar ' . formatRupiah($bidAmount) . ' berhasil dicatat resmi di dewan kurasi lelang Kuro.', [
            'bid_amount'     => $bidAmount,
            'bid_amount_fmt' => formatRupiah($bidAmount),
            'product_id'     => $productId,
        ]);
        break;

    // ─── ADMIN: Create / Update auction product ────────────────────────
    case 'admin_set_auction':
        requireAdmin();
        $productId      = (int)($input['product_id'] ?? 0);
        if ($productId <= 0) jsonResponse(false, 'ID produk tidak valid.', [], 400);

        $prodStmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $prodStmt->execute([$productId]);
        $prod = $prodStmt->fetch();
        if (!$prod) jsonResponse(false, 'Produk tidak ditemukan.', [], 404);

        $isAuction      = isset($input['is_auction']) ? (int)$input['is_auction'] : 1;
        $auctionStatus  = $input['auction_status'] ?? ($prod['auction_status'] ?: 'open');
        $startPriceIn   = (float)($input['auction_start_price'] ?? 0);
        $startPrice     = ($startPriceIn > 1) ? $startPriceIn : (float)($prod['auction_start_price'] ?: $prod['price']);
        $incrementIn    = (float)($input['auction_increment'] ?? 0);
        $increment      = ($incrementIn > 1) ? $incrementIn : (float)($prod['auction_increment'] ?: 1000000);
        $endTime        = !empty($input['auction_end_time']) ? trim($input['auction_end_time']) : ($prod['auction_end_time'] ?: null);

        if (!in_array($auctionStatus, ['upcoming','open','closed','cancelled'])) {
            jsonResponse(false, 'Status lelang tidak valid.', [], 400);
        }

        $db->prepare("
            UPDATE products SET
                is_auction = ?,
                auction_status = ?,
                auction_start_price = ?,
                auction_increment = ?,
                auction_end_time = ?
            WHERE id = ?
        ")->execute([$isAuction, $auctionStatus, $startPrice, $increment, $endTime, $productId]);

        jsonResponse(true, "Konfigurasi lelang '{$prod['name']}' berhasil diperbarui (Status: {$auctionStatus}).", ['product_id' => $productId]);
        break;

    // ─── ADMIN: Close auction & determine winner ───────────────────────
    case 'admin_close_auction':
        requireAdmin();
        $productId = (int)($input['product_id'] ?? 0);
        if ($productId <= 0) jsonResponse(false, 'ID produk tidak valid.', [], 400);

        $db->beginTransaction();
        try {
            // Find highest bid
            $bidStmt = $db->prepare("
                SELECT ab.user_id, ab.bid_amount, u.name as winner_name
                FROM auction_bids ab
                JOIN users u ON u.id = ab.user_id
                WHERE ab.product_id = ?
                ORDER BY ab.bid_amount DESC
                LIMIT 1
            ");
            $bidStmt->execute([$productId]);
            $winner = $bidStmt->fetch();

            if ($winner) {
                $db->prepare("
                    UPDATE products SET
                        auction_status = 'closed',
                        auction_winner_id = ?,
                        auction_winning_bid = ?,
                        status = 'acquired'
                    WHERE id = ?
                ")->execute([$winner['user_id'], $winner['bid_amount'], $productId]);
            } else {
                $db->prepare("UPDATE products SET auction_status = 'closed' WHERE id = ?")->execute([$productId]);
            }
            $db->commit();

            jsonResponse(true, 'Lelang berhasil ditutup.', [
                'product_id'     => $productId,
                'winner_user_id' => $winner ? $winner['user_id'] : null,
                'winner_name'    => $winner ? $winner['winner_name'] : 'Tidak ada pemenang',
                'winning_bid'    => $winner ? (float)$winner['bid_amount'] : 0,
                'winning_bid_fmt'=> $winner ? formatRupiah((float)$winner['bid_amount']) : '-',
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, 'Gagal menutup lelang: ' . $e->getMessage(), [], 500);
        }
        break;

    // ─── ADMIN: Grant auction access to a user ─────────────────────────
    case 'admin_grant_access':
        requireAdmin();
        $userId    = (int)($input['user_id'] ?? 0);
        $productId = (int)($input['product_id'] ?? 0);
        if ($userId <= 0 || $productId <= 0) jsonResponse(false, 'user_id dan product_id wajib diisi.', [], 400);

        $db->prepare("
            INSERT IGNORE INTO auction_access (user_id, product_id)
            VALUES (?, ?)
        ")->execute([$userId, $productId]);

        jsonResponse(true, 'Akses lelang berhasil diberikan kepada user ID ' . $userId . '.', []);
        break;

    // ─── USER: Check my auction access ─────────────────────────────────
    case 'my_access':
        requireLogin();
        $user   = getCurrentUser();
        $userId = (int)$user['id'];

        $stmt = $db->prepare("
            SELECT aa.product_id, aa.granted_at, p.name, p.auction_status, p.auction_end_time
            FROM auction_access aa
            JOIN products p ON p.id = aa.product_id
            WHERE aa.user_id = ?
        ");
        $stmt->execute([$userId]);
        $access = $stmt->fetchAll();

        jsonResponse(true, 'Akses lelang Anda.', ['access' => $access]);
        break;

    // ─── ADMIN: List all bids for a product ─────────────────────────────
    case 'admin_bids':
        requireAdmin();
        $productId = (int)($_GET['product_id'] ?? ($input['product_id'] ?? 0));
        if ($productId <= 0) jsonResponse(false, 'ID produk tidak valid.', [], 400);

        $stmt = $db->prepare("
            SELECT ab.*, u.name as bidder_name, u.email as bidder_email
            FROM auction_bids ab
            JOIN users u ON u.id = ab.user_id
            WHERE ab.product_id = ?
            ORDER BY ab.bid_amount DESC
        ");
        $stmt->execute([$productId]);
        $bids = $stmt->fetchAll();

        foreach ($bids as &$b) {
            $b['bid_amount_fmt'] = formatRupiah((float)$b['bid_amount']);
        }
        unset($b);

        jsonResponse(true, 'Riwayat bid produk ' . $productId, ['bids' => $bids]);
        break;

    default:
        jsonResponse(false, 'Aksi lelang tidak dikenal.', [], 400);
}
