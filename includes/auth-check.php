<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['user'])) redirect('auth/login.php');

function current_user(): array { return $_SESSION['user']; }
function can(string $permission): bool { return current_user()['role'] === 'Super Admin' || in_array($permission, current_user()['permissions'] ?? [], true); }
function require_permission(string $permission): void { if (!can($permission)) { http_response_code(403); exit('You do not have permission to access this area.'); } }
function record_activity(string $action, string $entity, ?int $entityId = null): void {
    global $pdo;
    $statement = $pdo->prepare('INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, ?, ?, ?, ?)');
    $statement->execute([current_user()['id'], $action, $entity, $entityId, $_SERVER['REMOTE_ADDR'] ?? null]);
}
