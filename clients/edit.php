<?php
declare(strict_types=1);
$pageTitle = 'Edit client';
$activePage = 'clients';
require_once __DIR__ . '/../includes/header.php';
require_permission('manage_clients');
$id = (int) ($_GET['id'] ?? 0);
$statement = $pdo->prepare('SELECT * FROM clients WHERE id = ?');
$statement->execute([$id]);
$client = $statement->fetch();
if (!$client) { http_response_code(404); exit('Client not found.'); }
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $company = trim((string) ($_POST['company'] ?? ''));
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        if ($name === '' || $company === '' || !$email) {
            $error = 'Enter a contact name, company, and valid email address.';
        } else {
            $update = $pdo->prepare('UPDATE clients SET name = ?, company = ?, email = ?, phone = ?, website = ?, address = ?, notes = ?, status = ? WHERE id = ?');
            $update->execute([$name, $company, $email, trim($_POST['phone'] ?? ''), trim($_POST['website'] ?? ''), trim($_POST['address'] ?? ''), trim($_POST['notes'] ?? ''), $_POST['status'], $id]);
            record_activity('Updated client', 'client', $id);
            flash('success', 'Client updated.');
            redirect('clients/view.php?id=' . $id);
        }
    }
}
?><div class="mb-4"><span class="section-kicker">Client relationship</span><h2 class="mt-2">Edit client</h2><p class="text-muted mb-0">Update <?= e($client['company']) ?> account details.</p></div><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post" class="panel form-panel"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><div class="row g-3"><div class="col-md-6"><label class="form-label">Contact name<input name="name" class="form-control" value="<?= e($client['name']) ?>" required></label></div><div class="col-md-6"><label class="form-label">Company<input name="company" class="form-control" value="<?= e($client['company']) ?>" required></label></div><div class="col-md-6"><label class="form-label">Email<input name="email" type="email" class="form-control" value="<?= e($client['email']) ?>" required></label></div><div class="col-md-6"><label class="form-label">Phone<input name="phone" class="form-control" value="<?= e($client['phone']) ?>"></label></div><div class="col-md-6"><label class="form-label">Website<input name="website" type="url" class="form-control" value="<?= e($client['website']) ?>"></label></div><div class="col-md-6"><label class="form-label">Status<select name="status" class="form-select"><option value="active" <?= $client['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= $client['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></label></div><div class="col-12"><label class="form-label">Address<textarea name="address" class="form-control" rows="2"><?= e($client['address']) ?></textarea></label></div><div class="col-12"><label class="form-label">Notes<textarea name="notes" class="form-control" rows="4"><?= e($client['notes']) ?></textarea></label></div></div><div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Save changes</button><a href="<?= url('clients/view.php?id=' . $id) ?>" class="btn btn-light">Cancel</a></div></form><?php require_once __DIR__ . '/../includes/footer.php'; ?>
