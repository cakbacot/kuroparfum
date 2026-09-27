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

        // Strict Bcrypt password verification
        if (!$user || !password_verify($password, $user['password_hash'])) {
            jsonResponse(false, 'Kredensial tidak valid atau akun tidak ditemukan.', [], 401);
        }

        // Regenerate session ID to prevent Session Fixation attacks
        session_regenerate_id(true);
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

    case 'logout':
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        jsonResponse(true, 'Sesi berhasil diakhiri.');
        break;

    default:
        jsonResponse(false, 'Aksi otentikasi tidak dikenal.', [], 400);
}
