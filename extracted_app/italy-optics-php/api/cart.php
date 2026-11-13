<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
    exit;
}

$action = $input['action'] ?? '';

if ($action === 'sync') {
    $_SESSION['cart'] = $input['items'] ?? [];
    echo json_encode(['status' => 'ok', 'count' => count($_SESSION['cart'])]);
    exit;
}

if ($action === 'place_order') {
    $db = getDB();
    $customerName = trim($input['name'] ?? '');
    $customerPhone = trim($input['phone'] ?? '');
    $customerEmail = trim($input['email'] ?? '');
    $address = trim($input['address'] ?? '12 Jamal Khan Road, Chattogram');
    $deliveryType = $input['deliveryType'] ?? $input['delivery_type'] ?? 'home';
    $paymentMethod = $input['paymentMethod'] ?? $input['payment_method'] ?? 'cod';
    $items = $input['items'] ?? [];

    if (empty($customerName) || empty($customerPhone) || empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide complete order details.']);
        exit;
    }

    $orderNumber = 'IO-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $subtotal = 0;
    foreach ($items as $it) {
        $subtotal += ($it['price'] * $it['quantity']);
    }
    $shippingFee = ($deliveryType === 'pickup' || $subtotal >= 2000) ? 0 : 60;
    $grandTotal = $subtotal + $shippingFee;

    $userId = $_SESSION['user']['id'] ?? null;

    if ($db) {
        try {
            $stmt = $db->prepare("INSERT INTO orders 
                (order_number, user_id, customer_name, customer_phone, customer_email, delivery_type, delivery_address, payment_method, shipping_fee, total_amount, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')");
            $stmt->execute([
                $orderNumber,
                $userId,
                $customerName,
                $customerPhone,
                $customerEmail,
                $deliveryType,
                $address,
                $paymentMethod,
                $shippingFee,
                $grandTotal
            ]);
            $orderId = $db->lastInsertId();

            $itemStmt = $db->prepare("INSERT INTO order_items
                (order_id, product_id, product_name, color_name, color_hex, unit_price, quantity, subtotal, prescription_note)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($items as $it) {
                $pId = is_numeric($it['id']) ? (int)$it['id'] : null;
                $lineSub = $it['price'] * $it['quantity'];
                $itemStmt->execute([
                    $orderId,
                    $pId,
                    $it['name'],
                    $it['color'] ?? 'Classic Black',
                    $it['hex'] ?? '#000000',
                    $it['price'],
                    $it['quantity'],
                    $lineSub,
                    $it['prescription'] ?? null
                ]);
            }
        } catch (PDOException $e) {
            error_log("Order save error: " . $e->getMessage());
        }
    }

    // Clear session cart
    $_SESSION['cart'] = [];

    echo json_encode([
        'status' => 'ok',
        'order_number' => $orderNumber,
        'total' => $grandTotal,
        'customer_name' => $customerName,
        'customer_phone' => $customerPhone,
        'whatsapp_url' => 'https://wa.me/88' . preg_replace('/[^0-9]/', '', STORE_PHONE) . '?text=' . urlencode("Hello Italy Optics, I placed order #{$orderNumber} for ৳{$grandTotal}. Name: {$customerName}.")
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
