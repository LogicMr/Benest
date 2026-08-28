<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
if (!empty($_SESSION['user'])) redirect('');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) { $error = 'Your session expired. Please try again.'; }
    else {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = (string)($_POST['password'] ?? '');
        $statement = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = ? AND u.status = "active" LIMIT 1');
        $statement->execute([$email ?: '']); $user = $statement->fetch();
        if ($user && password_verify($password, $user['password_hash'])) { session_regenerate_id(true); $_SESSION['user'] = ['id'=>(int)$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role_name'],'permissions'=>[]]; record_login($pdo, (int)$user['id']); redirect(''); }
        $error = 'The email or password is incorrect.';
    }
}
function record_login(PDO $pdo, int $userId): void { $statement = $pdo->prepare('INSERT INTO activity_logs (user_id, action, entity_type, ip_address) VALUES (?, ?, ?, ?)'); $statement->execute([$userId, 'Signed in', 'auth', $_SERVER['REMOTE_ADDR'] ?? null]); }
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign in | BENEST</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"><link href="<?= url('assets/css/app.css') ?>" rel="stylesheet"></head><body class="auth-page"><div class="auth-panel"><div class="auth-brand"><div class="brand-mark">B</div><div><strong>BENEST</strong><span>Project workspace</span></div></div><div class="auth-copy"><span class="section-kicker">Welcome back</span><h1>Make progress visible.</h1><p>One calm workspace for projects, people, and the details that keep delivery moving.</p></div><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post" class="auth-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label>Email address<input class="form-control" type="email" name="email" required autocomplete="email" placeholder="you@company.com"></label><label>Password<input class="form-control" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"></label><div class="form-check d-flex justify-content-between"><label class="form-check-label"><input class="form-check-input" type="checkbox" name="remember"> Remember me</label><a href="#">Forgot password?</a></div><button class="btn btn-primary w-100" type="submit">Sign in <i class="bi bi-arrow-right ms-2"></i></button></form><p class="auth-footnote">Spanish</p></div><div class="auth-aside"><span class="aside-tag">BENEST / 01</span><div class="aside-art"><div class="pulse-line"></div><div class="metric-orbit orbit-one"></div><div class="metric-orbit orbit-two"></div><div class="aside-stat"><strong>87%</strong><span>Project health</span></div></div><p class="aside-note">Designed for teams who care about the handoff, the deadline, and the last 10%.</p></div></body></html>
