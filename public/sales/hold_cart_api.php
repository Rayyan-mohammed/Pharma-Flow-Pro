<?php
require_once '../../app/auth.php';
checkRole(['Administrator', 'Pharmacist', 'Staff']);

header('Content-Type: application/json');

if (!hasPermission('sales.create')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not permitted.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$userId = (int)($_SESSION['currentUser']['user_id'] ?? 0);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // State-changing request: the page must send the session's CSRF token in a header
        $sentToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sentToken)) {
            http_response_code(403);
            throw new Exception('CSRF validation failed. Refresh the page and try again.');
        }
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            throw new Exception('Invalid payload.');
        }

        $action = $payload['action'] ?? '';
        if ($action === 'resume') {
            $id = (int)($payload['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid cart id.');
            }

            $stmt = $db->prepare("SELECT * FROM held_carts WHERE id = :id AND status = 'Held' LIMIT 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || ((int)$row['created_by'] !== $userId && !hasRole('Administrator'))) {
                throw new Exception('Held cart not found.');
            }

            $up = $db->prepare("UPDATE held_carts SET status = 'Resumed', resumed_at = NOW() WHERE id = :id");
            $up->bindValue(':id', $id, PDO::PARAM_INT);
            $up->execute();

            echo json_encode(['success' => true, 'data' => $row]);
            exit;
        }
        if ($action !== 'hold') {
            throw new Exception('Unsupported action.');
        }

        $cartJson = $payload['cart_json'] ?? '[]';
        $billSnapshot = $payload['bill_snapshot_json'] ?? null;
        $customerName = trim($payload['customer_name'] ?? '');
        $customerPhone = trim($payload['customer_phone'] ?? '');

        $holdCode = 'HOLD-' . date('His') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $ins = $db->prepare("INSERT INTO held_carts (hold_code, customer_name, customer_phone, cart_json, bill_snapshot_json, status, created_by) VALUES (:hold_code, :customer_name, :customer_phone, :cart_json, :bill_snapshot_json, 'Held', :created_by)");
        $ins->bindValue(':hold_code', $holdCode);
        $ins->bindValue(':customer_name', $customerName ?: null);
        $ins->bindValue(':customer_phone', $customerPhone ?: null);
        $ins->bindValue(':cart_json', $cartJson);
        $ins->bindValue(':bill_snapshot_json', $billSnapshot ?: null);
        $ins->bindValue(':created_by', $userId, PDO::PARAM_INT);
        $ins->execute();

        echo json_encode(['success' => true, 'hold_code' => $holdCode]);
        exit;
    }

    $action = $_GET['action'] ?? '';
    if ($action === 'list') {
        $isAdmin = hasRole('Administrator');
        $stmt = $db->prepare("SELECT id, hold_code, customer_name, customer_phone, created_at FROM held_carts WHERE status = 'Held'" . ($isAdmin ? '' : ' AND created_by = :uid') . " ORDER BY created_at DESC LIMIT 50");
        if (!$isAdmin) {
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        }
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    throw new Exception('Unsupported action.');
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
