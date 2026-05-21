<?php
// backend/api.php - simple REST-ish router for resources
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$resource = isset($_GET['resource']) ? $_GET['resource'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: [];

if (!$resource) {
    http_response_code(400);
    echo json_encode(['error' => 'resource required']);
    exit;
}

try {
    switch ($resource) {
        case 'categories':
            handleGenericTable('categories', $id, $method, $input);
            break;
        case 'menus':
            handleGenericTable('menus', $id, $method, $input);
            break;
        case 'stands':
            handleGenericTable('stands', $id, $method, $input);
            break;
        case 'users':
            handleUsers($id, $method, $input);
            break;
        case 'orders':
            handleOrders($id, $method, $input);
            break;
        case 'payments':
            handleGenericTable('payments', $id, $method, $input);
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'unknown resource']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleGenericTable($table, $id, $method, $input) {
    global $pdo;
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode($stmt->fetch());
        } else {
            $stmt = $pdo->query("SELECT * FROM $table");
            echo json_encode($stmt->fetchAll());
        }
    } elseif ($method === 'POST') {
        // Build insert dynamically (only simple key/value allowed)
        $keys = array_keys($input);
        $cols = implode(',', $keys);
        $placeholders = ':' . implode(',:', $keys);
        $stmt = $pdo->prepare("INSERT INTO $table ($cols) VALUES ($placeholders)");
        $stmt->execute($input);
        echo json_encode(['id' => $pdo->lastInsertId()]);
    } elseif ($method === 'PUT') {
        if (!$id) { http_response_code(400); echo json_encode(['error'=>'id required']); return; }
        $sets = [];
        foreach ($input as $k => $v) $sets[] = "$k = :$k";
        $sql = "UPDATE $table SET " . implode(', ', $sets) . " WHERE id = :id";
        $input['id'] = $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($input);
        echo json_encode(['updated' => $stmt->rowCount()]);
    } elseif ($method === 'DELETE') {
        if (!$id) { http_response_code(400); echo json_encode(['error'=>'id required']); return; }
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = :id");
        $stmt->execute(['id' => $id]);
        echo json_encode(['deleted' => $stmt->rowCount()]);
    } else {
        http_response_code(405);
    }
}

function handleUsers($id, $method, $input) {
    global $pdo;
    // POST /users?action=register or action=login
    $action = isset($_GET['action']) ? $_GET['action'] : null;
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare('SELECT id,name,email,role,created_at FROM users WHERE id = :id');
            $stmt->execute(['id'=>$id]);
            echo json_encode($stmt->fetch());
        } else {
            $stmt = $pdo->query('SELECT id,name,email,role,created_at FROM users');
            echo json_encode($stmt->fetchAll());
        }
    } elseif ($method === 'POST' && $action === 'register') {
        if (empty($input['email']) || empty($input['password']) || empty($input['name'])) {
            http_response_code(400); echo json_encode(['error'=>'name,email,password required']); return;
        }
        $hash = password_hash($input['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (:name,:email,:hash,:role)');
        $stmt->execute(['name'=>$input['name'],'email'=>$input['email'],'hash'=>$hash,'role'=>($input['role'] ?? 'customer')]);
        echo json_encode(['id'=>$pdo->lastInsertId()]);
    } elseif ($method === 'POST' && $action === 'login') {
        if (empty($input['email']) || empty($input['password'])) { http_response_code(400); echo json_encode(['error'=>'email,password required']); return; }
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email'=>$input['email']]);
        $user = $stmt->fetch();
        if ($user && password_verify($input['password'], $user['password_hash'])) {
            // Do not return password_hash
            unset($user['password_hash']);
            echo json_encode(['user'=>$user]);
        } else {
            http_response_code(401); echo json_encode(['error'=>'invalid credentials']);
        }
    } elseif ($method === 'PUT') {
        handleGenericTable('users', $id, $method, $input);
    } elseif ($method === 'DELETE') {
        handleGenericTable('users', $id, $method, $input);
    } else {
        http_response_code(405);
    }
}

function handleOrders($id, $method, $input) {
    global $pdo;
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id');
            $stmt->execute(['id'=>$id]);
            $order = $stmt->fetch();
            if ($order) {
                $stmt2 = $pdo->prepare('SELECT * FROM order_items WHERE order_id = :order_id');
                $stmt2->execute(['order_id'=>$id]);
                $order['items'] = $stmt2->fetchAll();
            }
            echo json_encode($order);
        } else {
            $stmt = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC');
            echo json_encode($stmt->fetchAll());
        }
    } elseif ($method === 'POST') {
        // Expect user_id, stand_id, total, items: [{menu_id,quantity,price},...]
        if (empty($input['user_id']) || empty($input['items']) || !is_array($input['items'])) { http_response_code(400); echo json_encode(['error'=>'user_id and items required']); return; }
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO orders (user_id, stand_id, total, status) VALUES (:user_id, :stand_id, :total, :status)');
        $stmt->execute(['user_id'=>$input['user_id'],'stand_id'=>($input['stand_id'] ?? null),'total'=>$input['total'] ?? 0,'status'=>($input['status'] ?? 'pending')]);
        $orderId = $pdo->lastInsertId();
        $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (:order_id, :menu_id, :quantity, :price)');
        foreach ($input['items'] as $it) {
            $stmtItem->execute(['order_id'=>$orderId,'menu_id'=>$it['menu_id'],'quantity'=>$it['quantity'],'price'=>$it['price']]);
        }
        $pdo->commit();
        echo json_encode(['id'=>$orderId]);
    } elseif ($method === 'PUT') {
        handleGenericTable('orders', $id, $method, $input);
    } elseif ($method === 'DELETE') {
        handleGenericTable('orders', $id, $method, $input);
    } else {
        http_response_code(405);
    }
}
