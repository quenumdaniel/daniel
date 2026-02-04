<?php
header('Content-Type: application/json; charset=utf-8');

$basePath = __DIR__;
$databasePath = $basePath . '/data/database.sqlite';
$schemaPath = $basePath . '/sql/schema.sql';

if (!is_dir($basePath . '/data')) {
  mkdir($basePath . '/data', 0777, true);
}

$db = new PDO('sqlite:' . $databasePath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$schema = file_get_contents($schemaPath);
$db->exec($schema);

$action = $_GET['action'] ?? '';

function json_response($payload) {
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

if ($action === 'add_service') {
  $input = json_decode(file_get_contents('php://input'), true);
  $stmt = $GLOBALS['db']->prepare('INSERT INTO services (service_date, theme, notes) VALUES (:service_date, :theme, :notes)');
  $stmt->execute([
    ':service_date' => $input['service_date'],
    ':theme' => $input['theme'],
    ':notes' => $input['notes'] ?? null,
  ]);
  json_response(['status' => 'ok']);
}

if ($action === 'add_transaction') {
  $input = json_decode(file_get_contents('php://input'), true);
  $stmt = $GLOBALS['db']->prepare('INSERT INTO transactions (service_id, txn_date, type, category, description, amount) VALUES (:service_id, :txn_date, :type, :category, :description, :amount)');
  $stmt->execute([
    ':service_id' => $input['service_id'] ?: null,
    ':txn_date' => $input['txn_date'],
    ':type' => $input['type'],
    ':category' => $input['category'],
    ':description' => $input['description'] ?? null,
    ':amount' => $input['amount'],
  ]);
  json_response(['status' => 'ok']);
}

if ($action === 'bootstrap') {
  $services = $db->query("SELECT s.id, s.service_date, s.theme,
      SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) AS total_income,
      SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) AS total_expense
    FROM services s
    LEFT JOIN transactions t ON s.id = t.service_id
    GROUP BY s.id
    ORDER BY s.service_date DESC")->fetchAll(PDO::FETCH_ASSOC);

  $months = $db->query('SELECT * FROM monthly_summary')->fetchAll(PDO::FETCH_ASSOC);

  $balance = $db->query("SELECT
      SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) AS balance
    FROM transactions")->fetchColumn();

  $recent = $db->query("SELECT txn_date, type, category, amount, description
    FROM transactions ORDER BY txn_date DESC, id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);

  json_response([
    'services' => $services,
    'months' => $months,
    'balance' => $balance ?: 0,
    'recent' => $recent,
  ]);
}

json_response(['status' => 'error', 'message' => 'Action inconnue']);
