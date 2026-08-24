<?php
declare(strict_types=1);

const APP_NAME = 'BENEST';
const CURRENCY_CODE = 'TZS';
const BASE_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..';

function detect_base_url(): string {
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $appRoot = realpath(BASE_PATH);
    if (!$docRoot || !$appRoot || !str_starts_with($appRoot, $docRoot)) return '';
    $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($appRoot, strlen($docRoot)));
    return implode('/', array_map('rawurlencode', explode('/', trim($relative, '/'))));
}
$detectedBase = detect_base_url();
define('BASE_URL', $detectedBase === '' ? '' : '/' . $detectedBase);

date_default_timezone_set('UTC');

ob_start(static function (string $output): string {
    return preg_replace('/\$(?=[0-9])/', CURRENCY_CODE . ' ', $output) ?? $output;
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionPath = BASE_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0700, true);
    }
    if (is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']),
        'samesite' => 'Lax',
    ]);
    session_start();
}

function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
function money(float|int|string $amount, bool $compact = false): string { $value = (float) $amount; return CURRENCY_CODE . ' ' . ($compact ? number_format($value / 1000, 1) . 'k' : number_format($value, 0)); }
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function redirect(string $path): never { header('Location: ' . url($path)); exit; }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verify_csrf(?string $token): bool { return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token); }
function flash(string $type, string $message): void { $_SESSION['flash'][] = ['type' => $type, 'message' => $message]; }
function pull_flash(): array { $messages = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $messages; }
