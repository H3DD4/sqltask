<?php
header('Content-Type: application/json; charset=UTF-8');

$dbFile = __DIR__ . DIRECTORY_SEPARATOR . 'data.sqlite';
$db = new SQLite3($dbFile);
$db->exec('PRAGMA foreign_keys = ON');

$db->exec('CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE,
  password TEXT,
  email TEXT,
  role TEXT,
  status TEXT
)');

$count = (int) $db->querySingle('SELECT COUNT(*) FROM users');
if ($count === 0) {
  $db->exec("INSERT INTO users (username, password, email, role, status) VALUES
    ('admin', 'supersecret123', 'admin@harbor.local', 'Owner', 'Active'),
    ('alice', 'alice_pw', 'alice@harbor.local', 'Member', 'Active')
  ");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['ok' => false, 'message' => 'Unsupported request.']);
  exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'register') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($username === '' || $password === '') {
    echo json_encode(['ok' => false, 'message' => 'Please fill in both fields.']);
    exit;
  }

  $checkSql = "SELECT id FROM users WHERE username = '$username' LIMIT 1";
  $exists = $db->querySingle($checkSql);
  if ($exists) {
    echo json_encode(['ok' => false, 'message' => 'Username already taken.']);
    exit;
  }

  $email = $username . '@harbor.local';
  $insertSql = "INSERT INTO users (username, password, email, role, status)
    VALUES ('$username', '$password', '$email', 'Member', 'Active')";
  $ok = $db->exec($insertSql);

  if (!$ok) {
    echo json_encode(['ok' => false, 'message' => 'Unable to create account.']);
    exit;
  }

  echo json_encode(['ok' => true]);
  exit;
}

if ($action === 'login') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  if (trim($username) === '' || trim($password) === '') {
    echo json_encode(['ok' => false, 'message' => 'Invalid username or password.']);
    exit;
  }

  $sql = "SELECT id, username, email, role FROM users WHERE username = '$username' AND password = '$password'";
  $result = $db->query($sql);
  $rows = [];

  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $rows[] = $row;
  }

  if (count($rows) === 0) {
    echo json_encode(['ok' => false, 'message' => 'Invalid username or password.']);
    exit;
  }

  echo json_encode(['ok' => true, 'user' => $rows[0], 'rows' => $rows]);
  exit;
}

echo json_encode(['ok' => false, 'message' => 'Unknown action.']);
