<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth-check.php';
require_permission('manage_projects');
$id = (int) ($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? null)) { http_response_code(400); exit('Invalid delete request.'); }
$projectStatement = $pdo->prepare('SELECT name FROM projects WHERE id = ?');
$projectStatement->execute([$id]);
$project = $projectStatement->fetch();
if (!$project) { flash('danger', 'Project not found.'); redirect('projects/'); }
$statement = $pdo->prepare('DELETE FROM projects WHERE id = ?');
$statement->execute([$id]);
record_activity('Deleted project: ' . $project['name'], 'project', $id);
flash('success', 'Project deleted.');
redirect('projects/');
