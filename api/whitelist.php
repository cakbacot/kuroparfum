<?php
// ===================================================
// Kuro Atelier - Whitelist & Private Access API
// Manages Curated Applications & Exclusive Invite Codes
// ===================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

initSession();
$db = Database::getConnection();
$input = getJsonInput();
$action = $_GET['action'] ?? ($input['action'] ?? '');

switch ($action) {
    case 'apply':
        $fullName = trim($input['full_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $organizationTitle = trim($input['organization_title'] ?? '');
        $statementOfIntent = trim($input['statement_of_intent'] ?? '');
        $olfactoryPreference = trim($input['olfactory_preference'] ?? '');

        if (empty($fullName) || empty($email) || empty($organizationTitle) || empty($statementOfIntent)) {
            jsonResponse(false, 'Harap lengkapi semua kolom identitas dan pernyataan minat.', [], 400);
        }

        $currentUser = getCurrentUser();
        $userId = $currentUser ? $currentUser['id'] : null;

        // If user not logged in, check if user exists or create unverified account
        if (!$userId) {
            $stmtUser = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmtUser->execute([$email]);
            $existing = $stmtUser->fetch();

            if ($existing) {
                $userId = $existing['id'];
            } else {
                // Auto create account with temporary secure password
                $tempPassword = password_hash('kuro' . rand(1000, 9999), PASSWORD_BCRYPT);
                $stmtNew = $db->prepare("
                    INSERT INTO users (name, email, password_hash, role, membership_status, title_company, created_at)
                    VALUES (?, ?, ?, 'client', 'pending', ?, NOW())
                ");
                $stmtNew->execute([$fullName, $email, $tempPassword, $organizationTitle]);
                $userId = (int)$db->lastInsertId();
                $_SESSION['user_id'] = $userId;
            }
        }

        // Update user status to pending
        $db->prepare("UPDATE users SET membership_status = 'pending' WHERE id = ?")->execute([$userId]);

        // Save whitelist application
        $stmtApp = $db->prepare("
            INSERT INTO whitelist_requests (user_id, full_name, email, phone, organization_title, statement_of_intent, olfactory_preference, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmtApp->execute([$userId, $fullName, $email, $phone, $organizationTitle, $statementOfIntent, $olfactoryPreference]);
        $requestId = (int)$db->lastInsertId();

        jsonResponse(true, 'Permohonan kurasi akses privat Anda telah dicatat dalam arsip Kuro Atelier. Tim kurasi Kuro akan meninjau latar belakang Anda dalam waktu 24 jam.', [
            'request_id' => $requestId,
            'status' => 'pending'
        ]);
        break;

    case 'verify_code':
        $code = strtoupper(trim($input['code'] ?? ''));
        if (empty($code)) {
            jsonResponse(false, 'Masukkan kode undangan eksklusif Anda.', [], 400);
        }

        $stmt = $db->prepare("SELECT * FROM invite_codes WHERE code = ? AND is_active = 1");
        $stmt->execute([$code]);
        $invite = $stmt->fetch();

        if (!$invite) {
            jsonResponse(false, 'Kode undangan tidak valid atau tidak terdaftar dalam arsip Kuro.', [], 404);
        }

        if ($invite['times_used'] >= $invite['max_uses']) {
            jsonResponse(false, 'Kode undangan ini telah mencapai kuota pemakaian maksimum.', [], 400);
        }

        $currentUser = getCurrentUser();
        if ($currentUser) {
            // Upgrade user directly to approved
            $db->prepare("UPDATE users SET membership_status = 'approved', invite_code_used = ? WHERE id = ?")->execute([$code, $currentUser['id']]);
            $db->prepare("UPDATE invite_codes SET times_used = times_used + 1 WHERE id = ?")->execute([$invite['id']]);

            jsonResponse(true, 'Kode undangan diverifikasi! Anda kini resmi menjadi anggota Sovereign Collector Kuro.', [
                'code' => $code,
                'membership_status' => 'approved',
                'description' => $invite['description']
            ]);
        } else {
            // Code is valid, prompt user to register or login with this code
            jsonResponse(true, 'Kode undangan valid! Silakan daftarkan nama Anda untuk mengaktivasi keanggotaan privat.', [
                'code' => $code,
                'description' => $invite['description'],
                'valid' => true
            ]);
        }
        break;

    case 'list_requests':
        requireAdmin();
        $statusFilter = $_GET['status'] ?? 'all';
        $query = "SELECT r.*, u.name as user_name FROM whitelist_requests r LEFT JOIN users u ON r.user_id = u.id";
        
        if ($statusFilter !== 'all') {
            $query .= " WHERE r.status = :status ORDER BY r.created_at DESC";
            $stmt = $db->prepare($query);
            $stmt->execute(['status' => $statusFilter]);
        } else {
            $query .= " ORDER BY r.created_at DESC";
            $stmt = $db->query($query);
        }

        $requests = $stmt->fetchAll();
        jsonResponse(true, 'Daftar permohonan kurasi akses Kuro', ['requests' => $requests]);
        break;

    case 'review_request':
        requireAdmin();
        $requestId = (int)($input['request_id'] ?? 0);
        $decision = $input['decision'] ?? ''; // 'approved' or 'rejected'
        $adminNote = trim($input['admin_note'] ?? '');

        if (!$requestId || !in_array($decision, ['approved', 'rejected'])) {
            jsonResponse(false, 'Parameter ulasan tidak valid.', [], 400);
        }

        $stmtReq = $db->prepare("SELECT * FROM whitelist_requests WHERE id = ?");
        $stmtReq->execute([$requestId]);
        $request = $stmtReq->fetch();

        if (!$request) {
            jsonResponse(false, 'Data permohonan tidak ditemukan.', [], 404);
        }

        // Update request record
        $updReq = $db->prepare("UPDATE whitelist_requests SET status = ?, admin_note = ?, reviewed_at = NOW() WHERE id = ?");
        $updReq->execute([$decision, $adminNote, $requestId]);

        // If user exists, update user membership status
        if (!empty($request['user_id'])) {
            $db->prepare("UPDATE users SET membership_status = ? WHERE id = ?")->execute([$decision, $request['user_id']]);
        }

        $statusLabel = ($decision === 'approved') ? 'Disetujui (Approved)' : 'Ditolak (Rejected)';
        jsonResponse(true, "Permohonan {$request['full_name']} telah berhasil diperbarui menjadi: {$statusLabel}", [
            'request_id' => $requestId,
            'decision' => $decision
        ]);
        break;

    default:
        jsonResponse(false, 'Aksi whitelist tidak dikenal.', [], 400);
}
