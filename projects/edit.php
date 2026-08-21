<?php
declare(strict_types=1);
$pageTitle = 'Edit project';
$activePage = 'projects';
require_once __DIR__ . '/../includes/header.php';
require_permission('manage_projects');
$id = (int) ($_GET['id'] ?? 0);
$projectStatement = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
$projectStatement->execute([$id]);
$project = $projectStatement->fetch();
if (!$project) { http_response_code(404); exit('Project not found.'); }
$clients = $pdo->query('SELECT id, company FROM clients WHERE status = "active" ORDER BY company')->fetchAll();
$managers = $pdo->query('SELECT id, name FROM users WHERE status = "active" ORDER BY name')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        flash('danger', 'Security token expired.');
    } else {
        $statement = $pdo->prepare('UPDATE projects SET client_id = ?, manager_id = ?, name = ?, description = ?, start_date = ?, due_date = ?, budget = ?, status = ?, priority = ?, technologies = ?, repository_url = ?, development_url = ?, staging_url = ?, production_url = ? WHERE id = ?');
        $statement->execute([(int) $_POST['client_id'], (int) $_POST['manager_id'] ?: null, trim($_POST['name']), trim($_POST['description']), $_POST['start_date'], $_POST['due_date'], (float) $_POST['budget'], $_POST['status'], $_POST['priority'], trim($_POST['technologies']), trim($_POST['repository_url']), trim($_POST['development_url']), trim($_POST['staging_url']), trim($_POST['production_url']), $id]);
        record_activity('Updated project', 'project', $id);
        flash('success', 'Project updated.');
        redirect('projects/view.php?id=' . $id);
    }
}
?>
<div class="mb-4"><span class="section-kicker">Delivery portfolio</span><h2 class="mt-2">Edit project</h2><p class="text-muted mb-0">Update <?= e($project['name']) ?> and keep its delivery details current.</p></div>
<form method="post" class="panel form-panel"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><div class="row g-3"><div class="col-md-8"><label class="form-label">Project name<input name="name" class="form-control" value="<?= e($project['name']) ?>" required></label></div><div class="col-md-4"><label class="form-label">Client<select name="client_id" class="form-select" required><?php foreach ($clients as $client): ?><option value="<?= $client['id'] ?>" <?= (int) $project['client_id'] === (int) $client['id'] ? 'selected' : '' ?>><?= e($client['company']) ?></option><?php endforeach; ?></select></label></div><div class="col-md-6"><label class="form-label">Project manager<select name="manager_id" class="form-select"><option value="">Unassigned</option><?php foreach ($managers as $manager): ?><option value="<?= $manager['id'] ?>" <?= (int) $project['manager_id'] === (int) $manager['id'] ? 'selected' : '' ?>><?= e($manager['name']) ?></option><?php endforeach; ?></select></label></div><div class="col-md-3"><label class="form-label">Status<select name="status" class="form-select"><?php foreach (['Planning','Not Started','In Progress','On Hold','Under Review','Testing','Completed','Cancelled'] as $status): ?><option <?= $project['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></label></div><div class="col-md-3"><label class="form-label">Priority<select name="priority" class="form-select"><?php foreach (['Low','Medium','High','Critical'] as $priority): ?><option <?= $project['priority'] === $priority ? 'selected' : '' ?>><?= e($priority) ?></option><?php endforeach; ?></select></label></div><div class="col-md-4"><label class="form-label">Start date<input name="start_date" type="date" class="form-control" value="<?= e($project['start_date']) ?>" required></label></div><div class="col-md-4"><label class="form-label">Expected completion<input name="due_date" type="date" class="form-control" value="<?= e($project['due_date']) ?>" required></label></div><div class="col-md-4"><label class="form-label">Budget<input name="budget" type="number" step="0.01" class="form-control" value="<?= e((string) $project['budget']) ?>"></label></div><div class="col-12"><label class="form-label">Technologies<input name="technologies" class="form-control" value="<?= e($project['technologies']) ?>"></label></div><div class="col-12"><label class="form-label">Description<textarea name="description" class="form-control" rows="4"><?= e($project['description']) ?></textarea></label></div><div class="col-md-6"><label class="form-label">Repository URL<input name="repository_url" type="url" class="form-control" value="<?= e($project['repository_url']) ?>"></label></div><div class="col-md-6"><label class="form-label">Development URL<input name="development_url" type="url" class="form-control" value="<?= e($project['development_url']) ?>"></label></div><div class="col-md-6"><label class="form-label">Staging URL<input name="staging_url" type="url" class="form-control" value="<?= e($project['staging_url']) ?>"></label></div><div class="col-md-6"><label class="form-label">Production URL<input name="production_url" type="url" class="form-control" value="<?= e($project['production_url']) ?>"></label></div></div><div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Save changes</button><a href="<?= url('projects/view.php?id=' . $id) ?>" class="btn btn-light">Cancel</a></div></form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
