<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth-check.php';
require_permission('manage_clients');
$id = (int) ($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? null)) { http_response_code(400); exit('Invalid delete request.'); }
$clientStatement = $pdo->prepare('SELECT name, company FROM clients WHERE id = ?');
$clientStatement->execute([$id]);
$client = $clientStatement->fetch();
if (!$client) { flash('danger', 'Client not found.'); redirect('clients/'); }
try {
	$pdo->beginTransaction();
	$projectStatement = $pdo->prepare('DELETE FROM projects WHERE client_id = ?');
	$projectStatement->execute([$id]);
	$statement = $pdo->prepare('DELETE FROM clients WHERE id = ?');
	$statement->execute([$id]);
	$pdo->commit();
} catch (Throwable $exception) {
	if ($pdo->inTransaction()) $pdo->rollBack();
	flash('danger', 'The client could not be deleted. No records were changed.');
	redirect('clients/view.php?id=' . $id);
}
record_activity('Deleted client: ' . $client['company'], 'client', $id);
flash('success', 'Client deleted.');
redirect('clients/');
