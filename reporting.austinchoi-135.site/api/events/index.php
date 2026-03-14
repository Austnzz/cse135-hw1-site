<?php
declare(strict_types=1);

header('Content-Type: application/json');

$dbHost = '127.0.0.1';
$dbName = 'cse135_hw3';
$dbUser = 'cse135';
$dbPass = 'Megachain#49';

function readJsonBody(): array {
  $raw = file_get_contents('php://input');
  if ($raw === false || trim($raw) === '') return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

function respond(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_SLASHES);
  exit;
}

try {
  $pdo = new PDO(
    "pgsql:host=$dbHost;port=5432;dbname=$dbName",
    $dbUser,
    $dbPass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );

  $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

  // id will be injected by rewrite rules for /api/events/{id}
  $id = null;
  if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id <= 0) $id = null;
  }

  if ($method === 'GET') {
    if ($id !== null) {
      $stmt = $pdo->prepare("SELECT id, received_at, session_id, page, event_type, ts_ms, payload FROM events WHERE id = :id");
      $stmt->execute([':id' => $id]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!$row) respond(404, ['error' => 'Not found']);
      respond(200, $row);
    }

    $limit = isset($_GET['limit']) ? max(1, min(500, (int)$_GET['limit'])) : 100;
    $stmt = $pdo->prepare("SELECT id, received_at, session_id, page, event_type, ts_ms, payload FROM events ORDER BY id DESC LIMIT :lim");
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    respond(200, $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  if ($method === 'POST') {
    if ($id !== null) respond(400, ['error' => 'Do not include an id in POST']);

    $b = readJsonBody();
    $sessionId = $b['session_id'] ?? null;
    $page = $b['page'] ?? null;
    $eventType = $b['event_type'] ?? null;
    $tsMs = $b['ts_ms'] ?? null;
    $payload = $b['payload'] ?? null;

    if (!is_string($sessionId) || $sessionId === '' || !is_string($eventType) || $eventType === '' || !is_array($payload)) {
      respond(400, ['error' => 'Missing session_id, event_type, or payload']);
    }

    $stmt = $pdo->prepare(
      "INSERT INTO events (session_id, page, event_type, ts_ms, payload)
       VALUES (:session_id, :page, :event_type, :ts_ms, :payload::jsonb)
       RETURNING id"
    );

    $stmt->execute([
      ':session_id' => $sessionId,
      ':page' => is_string($page) ? $page : null,
      ':event_type' => $eventType,
      ':ts_ms' => is_int($tsMs) ? $tsMs : null,
      ':payload' => json_encode($payload, JSON_UNESCAPED_SLASHES),
    ]);

    respond(201, ['ok' => true, 'id' => (int)$stmt->fetchColumn()]);
  }

  if ($method === 'PUT') {
    if ($id === null) respond(400, ['error' => 'Missing id in URL']);

    $b = readJsonBody();
    $page = $b['page'] ?? null;
    $eventType = $b['event_type'] ?? null;
    $payload = $b['payload'] ?? null;

    if (($eventType !== null && !is_string($eventType)) || ($payload !== null && !is_array($payload))) {
      respond(400, ['error' => 'Invalid fields']);
    }

    $stmt = $pdo->prepare(
      "UPDATE events
       SET page = COALESCE(:page, page),
           event_type = COALESCE(:event_type, event_type),
           payload = COALESCE(:payload::jsonb, payload)
       WHERE id = :id"
    );

    $stmt->execute([
      ':id' => $id,
      ':page' => is_string($page) ? $page : null,
      ':event_type' => is_string($eventType) ? $eventType : null,
      ':payload' => is_array($payload) ? json_encode($payload, JSON_UNESCAPED_SLASHES) : null,
    ]);

    if ($stmt->rowCount() === 0) respond(404, ['error' => 'Not found']);
    respond(200, ['ok' => true, 'updated' => $stmt->rowCount()]);
  }

  if ($method === 'DELETE') {
    if ($id === null) respond(400, ['error' => 'Missing id in URL']);

    $stmt = $pdo->prepare("DELETE FROM events WHERE id = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) respond(404, ['error' => 'Not found']);
    respond(200, ['ok' => true, 'deleted' => $stmt->rowCount()]);
  }

  respond(405, ['error' => 'Method Not Allowed']);
} catch (Throwable $e) {
  error_log("CSE135 reporting API error: " . $e->getMessage());
  respond(500, ['error' => 'Server error']);
}
