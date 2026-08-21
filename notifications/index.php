<?php
declare(strict_types=1);
$pageTitle = 'Notifications';
$activePage = 'notifications';
require_once __DIR__ . '/../includes/header.php';
$today = new DateTimeImmutable('today');
$taskLimit = $today->modify('+7 days')->format('Y-m-d');
$projectLimit = $today->modify('+30 days')->format('Y-m-d');
$taskStatement = $pdo->prepare("SELECT t.id, t.title, t.due_date, t.status, p.id project_id, p.name project_name FROM tasks t JOIN projects p ON p.id = t.project_id WHERE t.status <> 'Done' AND t.due_date IS NOT NULL AND t.due_date <= ? ORDER BY t.due_date");
$taskStatement->execute([$taskLimit]);
$taskRows = $taskStatement->fetchAll();
$projectStatement = $pdo->prepare("SELECT id, name, due_date, status FROM projects WHERE status NOT IN ('Completed', 'Cancelled') AND due_date IS NOT NULL AND due_date <= ? ORDER BY due_date");
$projectStatement->execute([$projectLimit]);
$projectRows = $projectStatement->fetchAll();
$deadlineLabel = static function (string $dueDate, DateTimeImmutable $today): array {
    $due = new DateTimeImmutable($dueDate);
    $days = (int) $today->diff($due)->format('%r%a');
    if ($days < 0) return ['Overdue by ' . abs($days) . ' day' . (abs($days) === 1 ? '' : 's'), 'danger', 'exclamation-octagon'];
    if ($days === 0) return ['Due today', 'danger', 'alarm'];
    if ($days === 1) return ['Due tomorrow', 'warning', 'clock'];
    return ['Due in ' . $days . ' days', 'warning', 'calendar-event'];
};
?>
<div class="d-flex justify-content-between align-items-end mb-4"><div><span class="section-kicker">Delivery alerts</span><h2 class="mt-2">Notifications</h2><p class="text-muted mb-0">Task completion and project deadline alerts that need attention.</p></div><a href="<?= url('projects/') ?>" class="btn btn-light"><i class="bi bi-briefcase me-2"></i>View projects</a></div>
<div class="dashboard-grid"><section class="panel"><div class="panel-heading"><div><span class="section-kicker">Task deadlines</span><h3>Incomplete tasks</h3></div><span class="status-badge status-<?= count($taskRows) ? 'critical' : 'healthy' ?>"><?= count($taskRows) ?> alerts</span></div><?php if (!$taskRows): ?><div class="empty-state"><i class="bi bi-check2-circle"></i><h3>Tasks are on track</h3><p class="text-muted">No incomplete task is due within the next 7 days.</p></div><?php else: ?><div class="notification-list"><?php foreach ($taskRows as $task): $label = $deadlineLabel($task['due_date'], $today); ?><div class="notification-row"><span class="notification-icon text-<?= e($label[1]) ?>"><i class="bi bi-<?= e($label[2]) ?>"></i></span><div class="notification-content"><strong><?= e($task['title']) ?></strong><p><?= e($task['project_name']) ?> · <?= e($task['status']) ?> · <?= e($label[0]) ?></p></div><time><?= e(date('M d', strtotime($task['due_date']))) ?></time></div><?php endforeach; ?></div><?php endif; ?></section>
<section class="panel"><div class="panel-heading"><div><span class="section-kicker">Project deadlines</span><h3>Active projects</h3></div><span class="status-badge status-<?= count($projectRows) ? 'critical' : 'healthy' ?>"><?= count($projectRows) ?> alerts</span></div><?php if (!$projectRows): ?><div class="empty-state"><i class="bi bi-calendar-check"></i><h3>Projects are on track</h3><p class="text-muted">No active project is due within the next 30 days.</p></div><?php else: ?><div class="notification-list"><?php foreach ($projectRows as $project): $label = $deadlineLabel($project['due_date'], $today); ?><div class="notification-row"><span class="notification-icon text-<?= e($label[1]) ?>"><i class="bi bi-<?= e($label[2]) ?>"></i></span><div class="notification-content"><strong><?= e($project['name']) ?></strong><p><?= e($project['status']) ?> · <?= e($label[0]) ?></p></div><time><?= e(date('M d', strtotime($project['due_date']))) ?></time></div><?php endforeach; ?></div><?php endif; ?></section></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
