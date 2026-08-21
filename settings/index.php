<?php
declare(strict_types=1);
$pageTitle = 'Settings';
$activePage = 'settings';
require_once __DIR__ . '/../includes/header.php';
require_permission('manage_projects');
$profileError = null;
$passwordError = null;
$userStatement = $pdo->prepare('SELECT name, email, password_hash FROM users WHERE id = ?');
$userStatement->execute([current_user()['id']]);
$account = $userStatement->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if (!verify_csrf($_POST['csrf'] ?? null)) {
		$profileError = 'Your session expired. Please try again.';
	} elseif ($action === 'profile') {
		$name = trim((string) ($_POST['name'] ?? ''));
		$email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
		if ($name === '' || !$email) {
			$profileError = 'Enter a valid name and email address.';
		} else {
			try {
				$statement = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
				$statement->execute([$name, $email, current_user()['id']]);
				$_SESSION['user']['name'] = $name;
				$_SESSION['user']['email'] = $email;
				record_activity('Updated personal profile', 'user', (int) current_user()['id']);
				flash('success', 'Your profile was updated.');
				redirect('settings/');
			} catch (PDOException $exception) {
				$profileError = $exception->getCode() === '23000' ? 'That email address is already in use.' : 'The profile could not be updated.';
			}
		}
	} elseif ($action === 'password') {
		$currentPassword = (string) ($_POST['current_password'] ?? '');
		$newPassword = (string) ($_POST['new_password'] ?? '');
		$confirmPassword = (string) ($_POST['confirm_password'] ?? '');
		if (!password_verify($currentPassword, $account['password_hash'])) {
			$passwordError = 'Your current password is incorrect.';
		} elseif (strlen($newPassword) < 8) {
			$passwordError = 'Your new password must be at least 8 characters.';
		} elseif ($newPassword !== $confirmPassword) {
			$passwordError = 'The new passwords do not match.';
		} else {
			$statement = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
			$statement->execute([password_hash($newPassword, PASSWORD_DEFAULT), current_user()['id']]);
			record_activity('Changed personal password', 'user', (int) current_user()['id']);
			flash('success', 'Your password was changed successfully.');
			redirect('settings/');
		}
	}
}
?>
<div class="mb-4"><span class="section-kicker">System</span><h2 class="mt-2">Settings</h2><p class="text-muted mb-0">Customize your profile, BENEST workspace, and access controls.</p></div>
<div class="dashboard-grid"><section class="panel"><div class="panel-heading"><div><span class="section-kicker">Your account</span><h3>Personal profile</h3></div><i class="bi bi-person-circle text-primary"></i></div><?php if ($profileError): ?><div class="alert alert-danger"><?= e($profileError) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="profile"><div class="mb-3"><label class="form-label">Your name<input name="name" class="form-control" value="<?= e($account['name']) ?>" required></label></div><div class="mb-3"><label class="form-label">Email address<input name="email" type="email" class="form-control" value="<?= e($account['email']) ?>" required></label></div><button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-2"></i>Save profile</button></form></section><section class="panel"><div class="panel-heading"><div><span class="section-kicker">Security</span><h3>Change password</h3></div><i class="bi bi-shield-lock text-primary"></i></div><?php if ($passwordError): ?><div class="alert alert-danger"><?= e($passwordError) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="password"><div class="mb-3"><label class="form-label">Current password<input name="current_password" type="password" class="form-control" required></label></div><div class="mb-3"><label class="form-label">New password<input name="new_password" type="password" minlength="8" class="form-control" required></label></div><div class="mb-3"><label class="form-label">Confirm new password<input name="confirm_password" type="password" minlength="8" class="form-control" required></label></div><button class="btn btn-light" type="submit"><i class="bi bi-key me-2"></i>Change password</button></form></section></div>
<div class="dashboard-grid"><section class="panel"><div class="panel-heading"><div><span class="section-kicker">General</span><h3>Workspace settings</h3></div><i class="bi bi-sliders text-primary"></i></div><p class="small text-muted">System name, company details, logo, and default currency.</p><div class="setting-row"><span>Currency</span><strong>TZS · Tanzanian shilling</strong></div><div class="setting-row"><span>System name</span><strong>BENEST</strong></div></section><section class="panel"><div class="panel-heading"><div><span class="section-kicker">Appearance</span><h3>Theme</h3></div><i class="bi bi-moon-stars text-primary"></i></div><p class="small text-muted">Choose the interface appearance for this browser.</p><button class="btn btn-light theme-setting" data-theme-toggle type="button"><i class="bi bi-moon-stars me-2"></i>Toggle dark mode</button></section></div>
<section class="panel"><div class="panel-heading"><div><span class="section-kicker">Administration</span><h3>Team & access</h3></div><i class="bi bi-people text-primary"></i></div><p class="small text-muted">Add Project Managers, Developers, Accountants, Clients, and additional Super Admin users. Activate or deactivate access without deleting account history.</p><a class="btn btn-primary" href="<?= url('settings/users.php') ?>"><i class="bi bi-person-plus me-2"></i>Manage users and roles</a></section><section class="panel"><div class="panel-heading"><div><span class="section-kicker">Security</span><h3>Access controls</h3></div><i class="bi bi-shield-check text-primary"></i></div><p class="small text-muted mb-0">Role-based permissions, sessions, password requirements, and audit logging are managed here.</p></section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>