<?php
// ===================================================
// Kuro Atelier - Direct Concierge Messaging API
// Encrypted Direct Channel between Collector & Master Perfumer Kuro
// ===================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

initSession();
$user = requireAuth();
$db = Database::getConnection();
$input = getJsonInput();
$action = $_GET['action'] ?? ($input['action'] ?? 'get_thread');

switch ($action) {
    case 'get_thread':
        // Determine thread
        $threadId = $input['thread_id'] ?? ($_GET['thread_id'] ?? '');

        if ($user['role'] === 'kuro_admin') {
            $clientId = (int)($input['client_id'] ?? ($_GET['client_id'] ?? 0));
            if ($clientId > 0) {
                $threadId = "thread_client_{$clientId}";
            } elseif (empty($threadId)) {
                // Default to first active thread or client 2
                $firstThread = $db->query("SELECT thread_id FROM concierge_messages ORDER BY created_at DESC LIMIT 1")->fetch();
                $threadId = $firstThread ? $firstThread['thread_id'] : "thread_client_2";
            }
        } else {
            // Client always sees their own thread with Kuro
            $threadId = "thread_client_{$user['id']}";
        }

        // Fetch messages
        $stmt = $db->prepare("
            SELECT m.*, u.name as user_real_name, u.role as user_role, p.name as product_name, p.edition_serial
            FROM concierge_messages m
            LEFT JOIN users u ON m.sender_id = u.id
            LEFT JOIN products p ON m.product_id = p.id
            WHERE m.thread_id = ?
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$threadId]);
        $messages = $stmt->fetchAll();

        // Mark incoming messages as read
        $markStmt = $db->prepare("
            UPDATE concierge_messages 
            SET is_read = 1 
            WHERE thread_id = ? AND sender_id != ?
        ");
        $markStmt->execute([$threadId, $user['id']]);

        jsonResponse(true, 'Pesan concierge berhasil dimuat.', [
            'thread_id' => $threadId,
            'messages' => $messages,
            'current_user_id' => $user['id']
        ]);
        break;

    case 'send_message':
        $messageText = trim($input['message'] ?? '');
        $productId = !empty($input['product_id']) ? (int)$input['product_id'] : null;
        $threadId = $input['thread_id'] ?? '';

        if (empty($messageText)) {
            jsonResponse(false, 'Pesan tidak boleh kosong.', [], 400);
        }

        if ($user['role'] === 'kuro_admin') {
            $receiverId = (int)($input['receiver_id'] ?? 0);
            if (!$receiverId && !empty($threadId) && preg_match('/thread_client_(\d+)/', $threadId, $matches)) {
                $receiverId = (int)$matches[1];
            }
            if (!$receiverId) {
                // Fallback to client 2
                $receiverId = 2;
            }
            if (empty($threadId)) {
                $threadId = "thread_client_{$receiverId}";
            }
            $senderRole = 'kuro_admin';
            $senderName = 'Kuro (黒) Master Perfumer';
        } else {
            // Sender is client
            $threadId = "thread_client_{$user['id']}";
            $receiverId = 1; // Kuro Admin
            $senderRole = 'client';
            $senderName = $user['name'];
        }

        $stmt = $db->prepare("
            INSERT INTO concierge_messages (thread_id, sender_id, sender_name, sender_role, product_id, message, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([$threadId, $user['id'], $senderName, $senderRole, $productId, $messageText]);
        $msgId = (int)$db->lastInsertId();

        // If client sends message and Kuro is not immediately available, provide an elegant automated Japanese concierge acknowledgment
        if ($user['role'] === 'client') {
            // Optional: simulate Kuro's thoughtful atelier response after inquiry
        }

        jsonResponse(true, 'Pesan terkirim ke saluran privat Kuro Atelier.', [
            'message_id' => $msgId,
            'thread_id' => $threadId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        break;

    case 'list_threads':
        requireAdmin();
        // Get all unique threads with latest message and client info
        $query = "
            SELECT 
                m.thread_id,
                MAX(m.created_at) as last_message_at,
                (SELECT message FROM concierge_messages WHERE thread_id = m.thread_id ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT sender_name FROM concierge_messages WHERE thread_id = m.thread_id ORDER BY created_at DESC LIMIT 1) as last_sender,
                COUNT(CASE WHEN m.is_read = 0 AND m.sender_role = 'client' THEN 1 END) as unread_count,
                u.id as client_id,
                u.name as client_name,
                u.email as client_email,
                u.title_company as client_title
            FROM concierge_messages m
            LEFT JOIN users u ON (m.sender_role = 'client' AND m.sender_id = u.id)
            GROUP BY m.thread_id
            ORDER BY last_message_at DESC
        ";
        $threads = $db->query($query)->fetchAll();

        jsonResponse(true, 'Daftar saluran percakapan kolektor aktif.', ['threads' => $threads]);
        break;

    case 'quick_templates':
        $templates = [
            [
                'title' => 'Personalisasi Ukiran Emas 24K',
                'text' => 'Konbanwa Kuro-sensei. Saya tertarik untuk mengakuisisi flacon ini dengan permintaan ukiran khusus inisial nama saya dengan motif emas Urushi Jepang pada plat botol.'
            ],
            [
                'title' => 'Pengantaran Kurir Pribadi (Private Escrow)',
                'text' => 'Kuro-sensei, apakah pengiriman karya edisi 1-of-1 ini dapat diantar langsung oleh perwakilan resmi Kuro Atelier ke kediaman pribadi saya?'
            ],
            [
                'title' => 'Konsultasi Penyesuaian Konsentrasi Minyak',
                'text' => 'Saya ingin berdiskusi mengenai profil sillage dan longevity aroma ini di iklim tropis, serta opsi pengemasan kotak kayu Paulownia bertanda tangan Anda.'
            ]
        ];
        jsonResponse(true, 'Template konsultasi concierge', ['templates' => $templates]);
        break;

    default:
        jsonResponse(false, 'Aksi concierge tidak dikenal.', [], 400);
}
