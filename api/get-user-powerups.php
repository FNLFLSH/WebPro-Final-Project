<?php
// api/get-user-powerups.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = requireAuth();
$mysqli = getMySQLi();

// Get user's power-ups with quantities
$stmt = $mysqli->prepare("
    SELECT powerup_type, SUM(quantity) as total_quantity
    FROM user_powerups
    WHERE user_id = ?
    GROUP BY powerup_type
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$powerups = [];
while ($row = $result->fetch_assoc()) {
    $powerups[$row['powerup_type']] = (int)$row['total_quantity'];
}
$stmt->close();

jsonResponse([
    'success' => true,
    'powerups' => [
        'freeze_timer' => $powerups['freeze_timer'] ?? 0,
        'smart_shuffle' => $powerups['smart_shuffle'] ?? 0
    ]
]);

