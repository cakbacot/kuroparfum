<?php
// ===================================================
// Kuro Atelier - Helper Functions & Session Handling
// ===================================================

function initSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure cookie params if possible
        session_set_cookie_params([
            'lifetime' => 86400 * 7, // 7 days
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

function jsonResponse(bool $success, string $message, array $data = [], int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success'   => $success,
        'message'   => $message,
        'data'      => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function getJsonInput(): array {
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return $_POST;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? array_merge($_POST, $decoded) : $_POST;
}

function getCurrentUser(): ?array {
    initSession();
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    require_once __DIR__ . '/database.php';
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT id, name, email, role, membership_status, title_company, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }

    return $user;
}

function requireAuth(): array {
    $user = getCurrentUser();
    if (!$user) {
        jsonResponse(false, 'Akses terbatas. Silakan masuk terlebih dahulu.', [], 401);
    }
    return $user;
}

function requireAdmin(): array {
    $user = requireAuth();
    if ($user['role'] !== 'kuro_admin') {
        jsonResponse(false, 'Akses ditolak. Fitur ini hanya untuk Kuro (Master Perfumer / Admin).', [], 403);
    }
    return $user;
}

function isWhitelisted(): bool {
    $user = getCurrentUser();
    if (!$user) return false;
    return ($user['role'] === 'kuro_admin' || $user['membership_status'] === 'approved');
}

function isAdmin(): bool {
    $user = getCurrentUser();
    return ($user && $user['role'] === 'kuro_admin');
}

function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
