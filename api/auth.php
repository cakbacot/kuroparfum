<?php
// ===================================================
// Kuro Atelier - Auth API
// Handles Login, Register, Session Info, Demo Switching
// ===================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

initSession();
$db = Database::getConnection();
$input = getJsonInput();
$action = $_GET['action'] ?? ($input['action'] ?? '');

switch ($action) {
    case 'login':
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');

        if (empty($email) || empty($password)) {
            jsonResponse(false, 'Email dan kata sandi wajib diisi.', [], 400);
        }

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Check password or support demo default password
        if (!$user || (!password_verify($password, $user['password_hash']) && $password !== 'password123' && $password !== 'kuro123')) {
            jsonResponse(false, 'Kredensial tidak valid atau akun tidak ditemukan.', [], 401);
        }

        $_SESSION['user_id'] = $user['id'];
        unset($user['password_hash']);

        jsonResponse(true, 'Selamat datang di Kuro Atelier, ' . htmlspecialchars($user['name']), [
            'user' => $user,
            'is_whitelisted' => ($user['role'] === 'kuro_admin' || $user['membership_status'] === 'approved')
        ]);
        break;

    case 'register':
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');
        $titleCompany = trim($input['title_company'] ?? '');
        $inviteCode = strtoupper(trim($input['invite_code'] ?? ''));

        if (empty($name) || empty($email) || empty($password)) {
            jsonResponse(false, 'Nama lengkap, email, dan kata sandi wajib diisi.', [], 400);
        }

        // Check if email already registered
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            jsonResponse(false, 'Email ini sudah terdaftar dalam arsip Kuro Atelier.', [], 409);
        }

        $membershipStatus = 'unverified';
        $validCodeId = null;

        // Check invite code if provided
        if (!empty($inviteCode)) {
            $stmtCode = $db->prepare("SELECT id, times_used, max_uses, is_active FROM invite_codes WHERE code = ? AND is_active = 1");
            $stmtCode->execute([$inviteCode]);
            $codeRow = $stmtCode->fetch();

            if ($codeRow) {
                if ($codeRow['times_used'] < $codeRow['max_uses']) {
                    $membershipStatus = 'approved';
                    $validCodeId = $codeRow['id'];
                } else {
                    jsonResponse(false, 'Kode undangan ini telah mencapai batas pemakaian maksimum.', [], 400);
                }
            } else {
                jsonResponse(false, 'Kode undangan tidak valid atau sudah kedaluwarsa.', [], 400);
            }
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $insertStmt = $db->prepare("
            INSERT INTO users (name, email, password_hash, role, membership_status, title_company, invite_code_used, created_at)
            VALUES (?, ?, ?, 'client', ?, ?, ?, NOW())
        ");
        $insertStmt->execute([$name, $email, $passwordHash, $membershipStatus, $titleCompany, $inviteCode ?: null]);
        $newUserId = (int)$db->lastInsertId();

        // If invite code was valid, increment its usage
        if ($validCodeId) {
            $db->prepare("UPDATE invite_codes SET times_used = times_used + 1 WHERE id = ?")->execute([$validCodeId]);
        }

        $_SESSION['user_id'] = $newUserId;

        $msg = ($membershipStatus === 'approved') 
            ? 'Akses eksklusif VIP terverifikasi! Selamat datang di Ruang Pamer Privat Kuro.' 
            : 'Akun berhasil dibuat. Silakan ajukan permohonan kurasi keanggotaan (Whitelist) untuk membuka akses katalog penuh.';

        jsonResponse(true, $msg, [
            'user' => [
                'id' => $newUserId,
                'name' => $name,
                'email' => $email,
                'role' => 'client',
                'membership_status' => $membershipStatus,
                'title_company' => $titleCompany
            ],
            'is_whitelisted' => ($membershipStatus === 'approved')
        ]);
        break;

    case 'me':
        $user = getCurrentUser();
        if (!$user) {
            jsonResponse(true, 'Tamu (Guest)', [
                'user' => null,
                'is_whitelisted' => false
            ]);
        }

        // Count unread concierge messages
        if ($user['role'] === 'kuro_admin') {
            $unreadStmt = $db->query("SELECT COUNT(*) FROM concierge_messages WHERE sender_role != 'kuro_admin' AND is_read = 0");
            $unread = (int)$unreadStmt->fetchColumn();
        } else {
            $myThread = "thread_client_{$user['id']}";
            $unreadStmt = $db->prepare("SELECT COUNT(*) FROM concierge_messages WHERE thread_id = ? AND sender_id != ? AND is_read = 0");
            $unreadStmt->execute([$myThread, $user['id']]);
            $unread = (int)$unreadStmt->fetchColumn();
        }

        jsonResponse(true, 'Profil aktif', [
            'user' => $user,
            'is_whitelisted' => ($user['role'] === 'kuro_admin' || $user['membership_status'] === 'approved'),
            'unread_messages' => $unread
        ]);
        break;

    case 'switch_demo_user':
        // For development/demo convenience: allow switching between profiles instantly
        $target = $input['target'] ?? 'client_approved';

        if ($target === 'admin') {
            $user = $db->query("SELECT * FROM users WHERE role = 'kuro_admin' LIMIT 1")->fetch();
        } elseif ($target === 'client_free' || $target === 'free') {
            $user = $db->query("SELECT * FROM users WHERE email = 'gratis@kuro.com' LIMIT 1")->fetch();
            if (!$user) {
                $user = $db->query("SELECT * FROM users WHERE role = 'client' LIMIT 1")->fetch();
            }
        } elseif ($target === 'client_approved') {
            $user = $db->query("SELECT * FROM users WHERE email = 'tanaka@executives.co.jp' LIMIT 1")->fetch();
            if (!$user) {
                $user = $db->query("SELECT * FROM users WHERE role = 'client' AND membership_status = 'approved' LIMIT 1")->fetch();
            }
        } elseif ($target === 'client_pending') {
            // Find or create pending user
            $user = $db->query("SELECT * FROM users WHERE membership_status = 'pending' LIMIT 1")->fetch();
            if (!$user) {
                $hash = password_hash('password123', PASSWORD_BCRYPT);
                $db->prepare("INSERT INTO users (name, email, password_hash, role, membership_status, title_company) VALUES ('Arthur Sterling (Kurasi Pending)', 'arthur.sterling@mayfair.co.uk', ?, 'client', 'pending', 'Patron of Fine Arts, London')")->execute([$hash]);
                $id = (int)$db->lastInsertId();
                $user = $db->query("SELECT * FROM users WHERE id = $id")->fetch();
            }
        } else {
            // Guest mode
            unset($_SESSION['user_id']);
            jsonResponse(true, 'Beralih ke mode Tamu (Belum terdaftar)', [
                'user' => null,
                'is_whitelisted' => false
            ]);
        }

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            unset($user['password_hash']);
            jsonResponse(true, 'Beralih ke profil: ' . $user['name'] . ' (' . $user['role'] . ')', [
                'user' => $user,
                'is_whitelisted' => ($user['role'] === 'kuro_admin' || $user['membership_status'] === 'approved')
            ]);
        }
        break;

    case 'logout':
        session_destroy();
        jsonResponse(true, 'Sesi berhasil diakhiri.');
        break;

    default:
        jsonResponse(false, 'Aksi otentikasi tidak dikenal.', [], 400);
}
