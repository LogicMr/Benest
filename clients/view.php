<?php
declare(strict_types=1);
$pageTitle = 'Client profile';
$activePage = 'clients';
require_once __DIR__ . '/../includes/header.php';
require_permission('manage_clients');
$id = (int) ($_GET['id'] ?? 0);
$statement = $pdo->prepare('SELECT * FROM clients WHERE id = ?');
$statement->execute([$id]);
$client = $statement->fetch();
if (!$client) { http_response_code(404); exit('Client not found.'); }
$projectsStatement = $pdo->prepare('SELECT * FROM projects WHERE client_id = ? ORDER BY due_date');
$projectsStatement->execute([$id]);
$projects = $projectsStatement->fetchAll();
$valueStatement = $pdo->prepare('SELECT COALESCE(SUM(budget), 0) FROM projects WHERE client_id = ?');
$valueStatement->execute([$id]);
$portfolioValue = (float) $valueStatement->fetchColumn();
?>
<div class="d-flex justify-content-between align-items-end mb-4"><div><span class="section-kicker">Client relationship</span><h2 class="mt-2"><?= e($client['company']) ?></h2><p class="text-muted mb-0"><?= e($client['name']) ?> · <?= e($client['email']) ?></p></div><div class="d-flex gap-2"><a href="<?= url('clients/edit.php?id=' . $id) ?>" class="btn btn-light"><i class="bi bi-pencil me-2"></i>Edit client</a><a href="<?= url('projects/create.php') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New project</a></div></div>
<div class="dashboard-grid"><section class="panel"><div class="panel-heading"><div><span class="section-kicker">Contact</span><h3>Account details</h3></div><span class="status-badge status-<?= $client['status'] === 'active' ? 'healthy' : 'not-started' ?>"><?= e(ucfirst($client['status'])) ?></span></div><div class="row g-3 small"><div class="col-md-6"><span class="text-muted d-block">Contact</span><strong><?= e($client['name']) ?></strong></div><div class="col-md-6"><span class="text-muted d-block">Email</span><strong><?= e($client['email']) ?></strong></div><div class="col-md-6"><span class="text-muted d-block">Phone</span><strong><?= e($client['phone']) ?></strong></div><div class="col-md-6"><span class="text-muted d-block">Website</span><strong><?= e($client['website'] ?: 'Not provided') ?></strong></div><div class="col-12"><span class="text-muted d-block">Notes</span><strong><?= e($client['notes'] ?: 'No notes yet') ?></strong></div></div></section><section class="panel"><span class="section-kicker">Portfolio value</span><h3 class="mt-2"><?= e(money($portfolioValue)) ?></h3><p class="small text-muted mb-0"><?= count($projects) ?> connected projects</p></section></div>
<section class="panel"><div class="panel-heading"><div><span class="section-kicker">Delivery history</span><h3>Projects</h3></div></div><div class="table-responsive"><table class="table"><thead><tr><th>Project</th><th>Status</th><th>Deadline</th><th>Budget</th><th></th></tr></thead><tbody><?php foreach ($projects as $project): ?><tr><td class="table-title"><?= e($project['name']) ?></td><td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $project['status'])) ?>"><?= e($project['status']) ?></span></td><td><?= e(date('M d, Y', strtotime($project['due_date']))) ?></td><td><?= e(money($project['budget'])) ?></td><td><a class="text-link" href="<?= url('projects/view.php?id=' . $project['id']) ?>">Open <i class="bi bi-arrow-up-right"></i></a></td></tr><?php endforeach; ?></tbody></table></div></section>
<section class="panel"><div class="panel-heading"><div><span class="section-kicker">Account actions</span><h3>Remove client</h3></div><i class="bi bi-trash text-danger"></i></div><p class="small text-muted">Deleting this client also removes linked projects, tasks, and milestones.</p><form method="post" action="<?= url('clients/delete.php?id=' . $id) ?>" data-confirm="Delete this client and all linked projects, tasks, and milestones? This cannot be undone."><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash me-2"></i>Delete client</button></form></section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
