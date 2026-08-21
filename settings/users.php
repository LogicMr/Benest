<?php
declare(strict_types=1);
$pageTitle = 'Team & access';
$activePage = 'settings';
require_once __DIR__ . '/../includes/header.php';
if (current_user()['role'] !== 'Super Admin') { http_response_code(403); exit('Only Super Admin users can manage team access.'); }
$roles = $pdo->query('SELECT id, name FROM roles ORDER BY id')->fetchAll();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'create') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');
        $roleId = (int) ($_POST['role_id'] ?? 0);
        if ($name === '' || !$email || strlen($password) < 8 || !$roleId) {
            $error = 'Enter a name, valid email, role, and password with at least 8 characters.';
        } else {
            try {
                $statement = $pdo->prepare('INSERT INTO users (role_id, name, email, password_hash) VALUES (?, ?, ?, ?)');
                $statement->execute([$roleId, $name, $email, password_hash($password, PASSWORD_DEFAULT)]);
                $userId = (int) $pdo->lastInsertId();
                record_activity('Created team user', 'user', $userId);
                flash('success', 'Team member added successfully.');
                redirect('settings/users.php');
            } catch (PDOException $exception) {
                $error = $exception->getCode() === '23000' ? 'That email address is already in use.' : 'The user could not be created.';
            }
        }
    } elseif (($_POST['action'] ?? '') === 'toggle') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId === (int) current_user()['id']) {
            $error = 'You cannot deactivate your own account.';
        } else {
            $statement = $pdo->prepare("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
            $statement->execute([$userId]);
            record_activity('Updated team user status', 'user', $userId);
            flash('success', 'User status updated.');
            redirect('settings/users.php');
        }
    }
}
$users = $pdo->query('SELECT u.id, u.name, u.email, u.status, u.created_at, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.status DESC, u.name')->fetchAll();
?>
<div class="d-flex justify-content-between align-items-end mb-4"><div><span class="section-kicker">Super Admin</span><h2 class="mt-2">Team & access</h2><p class="text-muted mb-0">Add administrators and control who can access BENEST.</p></div><a href="<?= url('settings/') ?>" class="btn btn-light"><i class="bi bi-arrow-left me-2"></i>Settings</a></div>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<div class="dashboard-grid"><section class="panel"><div class="panel-heading"><div><span class="section-kicker">Invite internally</span><h3>Add team member</h3></div><i class="bi bi-person-plus text-primary"></i></div><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="create"><div class="mb-3"><label class="form-label">Full name<input name="name" class="form-control" required></label></div><div class="mb-3"><label class="form-label">Email address<input name="email" type="email" class="form-control" required></label></div><div class="mb-3"><label class="form-label">Role<select name="role_id" class="form-select" required><option value="">Choose role</option><?php foreach ($roles as $role): ?><option value="<?= $role['id'] ?>"><?= e($role['name']) ?></option><?php endforeach; ?></select></label></div><div class="mb-3"><label class="form-label">Temporary password<input name="password" type="password" minlength="8" class="form-control" required></label><div class="form-text">Use at least 8 characters. The password is stored as a secure hash.</div></div><button class="btn btn-primary" type="submit"><i class="bi bi-person-plus me-2"></i>Add user</button></form></section><section class="panel"><div class="panel-heading"><div><span class="section-kicker">Roles</span><h3>Access levels</h3></div><i class="bi bi-shield-check text-primary"></i></div><div class="setting-row"><span>Super Admin</span><strong>Full system access</strong></div><div class="setting-row"><span>Project Manager</span><strong>Delivery and reporting</strong></div><div class="setting-row"><span>Developer</span><strong>Assigned project work</strong></div><div class="setting-row"><span>Accountant</span><strong>Finance and invoices</strong></div><div class="setting-row"><span>Client</span><strong>Own portal data</strong></div></section></div>
<section class="panel"><div class="panel-heading"><div><span class="section-kicker">Directory</span><h3>Users</h3></div><span class="status-badge status-healthy"><?= count($users) ?> accounts</span></div><div class="table-responsive"><table class="table"><thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th><th></th></tr></thead><tbody><?php foreach ($users as $user): ?><tr><td><span class="avatar me-2"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></span><span class="table-title"><?= e($user['name']) ?></span><span class="table-subtitle ms-5"><?= e($user['email']) ?></span></td><td><?= e($user['role_name']) ?></td><td><span class="status-badge status-<?= $user['status'] === 'active' ? 'healthy' : 'not-started' ?>"><?= e(ucfirst($user['status'])) ?></span></td><td><?= e(date('M d, Y', strtotime($user['created_at']))) ?></td><td><?php if ((int) $user['id'] !== (int) current_user()['id']): ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="user_id" value="<?= $user['id'] ?>"><button class="btn btn-sm btn-light" type="submit"><?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button></form><?php else: ?><span class="text-muted small">Current account</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
