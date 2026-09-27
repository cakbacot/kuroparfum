<?php
// ===================================================
// Kuro Atelier - Admin / Creator Desk API
// Statistics, Atelier Vault, and VIP Invite Codes
// ===================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

initSession();
$user = requireAdmin();
$db = Database::getConnection();
$input = getJsonInput();
$action = $_GET['action'] ?? ($input['action'] ?? 'stats');

switch ($action) {
    case 'stats':
        // Applicants count
        $pendingReqs = (int)$db->query("SELECT COUNT(*) FROM whitelist_requests WHERE status = 'pending'")->fetchColumn();
        $approvedMembers = (int)$db->query("SELECT COUNT(*) FROM users WHERE membership_status = 'approved'")->fetchColumn();
        
        // Products count
        $totalFlacons = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $availableFlacons = (int)$db->query("SELECT COUNT(*) FROM products WHERE status = 'available'")->fetchColumn();
        $acquiredFlacons = (int)$db->query("SELECT COUNT(*) FROM products WHERE status = 'acquired'")->fetchColumn();

        // Revenue
        $totalRevenue = (float)$db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'confirmed' OR payment_status = 'completed'")->fetchColumn();

        // Unread messages
        $unreadMessages = (int)$db->query("SELECT COUNT(*) FROM concierge_messages WHERE sender_role = 'client' AND is_read = 0")->fetchColumn();

        jsonResponse(true, 'Statistik Atelier Kuro berhasil dimuat.', [
            'pending_requests' => $pendingReqs,
            'approved_members' => $approvedMembers,
            'total_flacons' => $totalFlacons,
            'available_flacons' => $availableFlacons,
            'acquired_flacons' => $acquiredFlacons,
            'total_revenue' => $totalRevenue,
            'total_revenue_formatted' => formatRupiah($totalRevenue),
            'unread_messages' => $unreadMessages
        ]);
        break;

    case 'generate_invite':
        $customCode = strtoupper(trim($input['code'] ?? ''));
        if (empty($customCode)) {
            $customCode = 'KURO-VIP-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        }
        $description = trim($input['description'] ?? 'VIP Patron Access Code');
        $maxUses = (int)($input['max_uses'] ?? 1);

        $stmt = $db->prepare("
            INSERT INTO invite_codes (code, description, max_uses, times_used, is_active, created_at)
            VALUES (?, ?, ?, 0, 1, NOW())
        ");
        $stmt->execute([$customCode, $description, $maxUses]);
        $codeId = (int)$db->lastInsertId();

        jsonResponse(true, "Kode undangan VIP {$customCode} berhasil diterbitkan.", [
            'id' => $codeId,
            'code' => $customCode,
            'max_uses' => $maxUses,
            'description' => $description
        ]);
        break;

    case 'invite_codes':
        $stmt = $db->query("SELECT * FROM invite_codes ORDER BY created_at DESC");
        $codes = $stmt->fetchAll();
        jsonResponse(true, 'Daftar kode undangan VIP Kuro Atelier.', ['codes' => $codes]);
        break;

    default:
        jsonResponse(false, 'Aksi admin tidak dikenal.', [], 400);
}
