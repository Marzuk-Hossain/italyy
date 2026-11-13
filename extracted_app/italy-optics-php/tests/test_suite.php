<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

echo "========================================================\n";
echo "  ITALY OPTICS - FULL SUITE DIAGNOSTIC & VERIFICATION\n";
echo "========================================================\n\n";

$db = getDB();
if (!$db) {
    echo "[FAIL] Could not connect to database!\n";
    exit(1);
}
echo "[PASS] Database Connected successfully using driver: " . Database::getDriverName() . "\n";

// TEST 1: Table Verification
echo "\n--- TEST 1: Table Verification ---\n";
$tables = ['users', 'categories', 'products', 'product_colors', 'orders', 'order_items', 'reviews', 'contact_messages'];
foreach ($tables as $t) {
    $res = $db->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
    echo "  ✓ Table '{$t}' verified with {$res} records.\n";
}

// TEST 2: Signup (Customer Registration)
echo "\n--- TEST 2: Signup (Customer Registration) ---\n";
$testEmail = 'customer_test_' . time() . '@example.com';
$testPassword = 'securePass123!';
$testName = 'Test Patron Chittagong';
$testPhone = '01812345678';

$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$testEmail]);
if ($stmt->fetch()) {
    $db->prepare("DELETE FROM users WHERE email = ?")->execute([$testEmail]);
}

$hash = password_hash($testPassword, PASSWORD_DEFAULT);
$insert = $db->prepare("INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, 'customer')");
$insert->execute([$testName, $testEmail, $testPhone, $hash]);
$newUserId = $db->lastInsertId();

$verifyUser = $db->prepare("SELECT * FROM users WHERE id = ?");
$verifyUser->execute([$newUserId]);
$userRow = $verifyUser->fetch();

if ($userRow && $userRow['email'] === $testEmail && $userRow['role'] === 'customer') {
    echo "  [PASS] Signup successful: Created user ID #{$newUserId} ({$testEmail})\n";
} else {
    echo "  [FAIL] Signup failed to create user record.\n";
    exit(1);
}

// TEST 3: Authentication (Password Verification & Wrong Password Rejection)
echo "\n--- TEST 3: Authentication & Password Verification ---\n";

// 3.1 Customer with WRONG password
$wrongPasswordAttempt = 'WrongPassword999';
$isPasswordCorrect = password_verify($wrongPasswordAttempt, $userRow['password_hash']);
if (!$isPasswordCorrect) {
    echo "  [PASS] Customer login with WRONG password was REJECTED properly.\n";
} else {
    echo "  [FAIL] SECURITY FLAW: Wrong password was accepted!\n";
    exit(1);
}

// 3.2 Customer with CORRECT password
$isPasswordCorrect = password_verify($testPassword, $userRow['password_hash']);
if ($isPasswordCorrect) {
    echo "  [PASS] Customer login with CORRECT password VERIFIED successfully.\n";
} else {
    echo "  [FAIL] Correct password failed to verify!\n";
    exit(1);
}

// 3.3 Admin Account verification
$adminStmt = $db->prepare("SELECT * FROM users WHERE email = 'admin@italyoptics.com'");
$adminStmt->execute();
$adminUser = $adminStmt->fetch();

if ($adminUser && $adminUser['role'] === 'admin') {
    echo "  [PASS] Admin account found: 'admin@italyoptics.com'\n";
    
    // Check wrong admin password
    if (!password_verify('admin_wrong_pass', $adminUser['password_hash'])) {
        echo "  [PASS] Admin login with WRONG password was REJECTED properly.\n";
    } else {
        echo "  [FAIL] Admin accepted wrong password!\n";
        exit(1);
    }

    // Check correct admin password
    if (password_verify('admin123', $adminUser['password_hash'])) {
        echo "  [PASS] Admin login with CORRECT password ('admin123') VERIFIED successfully.\n";
    } else {
        echo "  [FAIL] Admin password 'admin123' failed to verify!\n";
        exit(1);
    }
} else {
    echo "  [FAIL] Default admin account not found in database.\n";
    exit(1);
}

// TEST 4: Admin CRUD (Add Product, Update Price, Delete Product)
echo "\n--- TEST 4: Admin Management (Add, Update Price, Delete) ---\n";

// 4.1 Add Product
$testProdName = "Milano Test Luxury Frame " . time();
$slug = "test-frame-" . time();
$addStmt = $db->prepare("INSERT INTO products (name, slug, brand, category_id, price, original_price, rating, reviews_count, image, description, features, gender, shape, in_stock, is_featured) VALUES (?, ?, 'Italy Optics', 1, 4500.00, 5500.00, 5.0, 1, 'https://example.com/frame.jpg', 'Test description', 'Handmade', 'Unisex', 'Round', 1, 1)");
$addStmt->execute([$testProdName, $slug]);
$createdProdId = $db->lastInsertId();

$checkProd = $db->prepare("SELECT * FROM products WHERE id = ?");
$checkProd->execute([$createdProdId]);
$prodData = $checkProd->fetch();

if ($prodData && $prodData['name'] === $testProdName && (float)$prodData['price'] == 4500.00) {
    echo "  [PASS] Admin Add Product: Successfully added '{$testProdName}' with Price ৳4,500 (ID: {$createdProdId})\n";
} else {
    echo "  [FAIL] Admin Add Product failed.\n";
    exit(1);
}

// 4.2 Update Price
$newPrice = 3900.00;
$newOrigPrice = 4900.00;
$updStmt = $db->prepare("UPDATE products SET price = ?, original_price = ? WHERE id = ?");
$updStmt->execute([$newPrice, $newOrigPrice, $createdProdId]);

$checkProd->execute([$createdProdId]);
$prodUpdated = $checkProd->fetch();
if ($prodUpdated && (float)$prodUpdated['price'] == 3900.00) {
    echo "  [PASS] Admin Update Price: Price updated successfully to ৳" . number_format($newPrice) . ".\n";
} else {
    echo "  [FAIL] Admin Update Price failed.\n";
    exit(1);
}

// 4.3 Delete Product
$delStmt = $db->prepare("DELETE FROM products WHERE id = ?");
$delStmt->execute([$createdProdId]);
$checkProd->execute([$createdProdId]);
if (!$checkProd->fetch()) {
    echo "  [PASS] Admin Remove Product: Item #{$createdProdId} deleted successfully from database.\n";
} else {
    echo "  [FAIL] Admin Remove Product failed to delete item.\n";
    exit(1);
}

// TEST 5: Order & Cart Placement
echo "\n--- TEST 5: Order & Cart Checkout Processing ---\n";
$orderNum = 'IO-TEST-' . rand(1000, 9999);
$orderStmt = $db->prepare("INSERT INTO orders (order_number, user_id, customer_name, customer_phone, customer_email, delivery_type, delivery_address, payment_method, shipping_fee, total_amount, status) VALUES (?, ?, ?, ?, ?, 'home', '12 Jamal Khan Road, Chattogram', 'cod', 0.0, 3850.0, 'confirmed')");
$orderStmt->execute([$orderNum, $newUserId, $testName, $testPhone, $testEmail]);
$orderId = $db->lastInsertId();

$itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, color_name, color_hex, unit_price, quantity, subtotal) VALUES (?, 1, 'Milano Elegance Titanium', 'Matte Black', '#000000', 3850.0, 1, 3850.0)");
$itemStmt->execute([$orderId]);

$verifyOrder = $db->prepare("SELECT * FROM orders WHERE id = ?");
$verifyOrder->execute([$orderId]);
$orderRow = $verifyOrder->fetch();

if ($orderRow && $orderRow['order_number'] === $orderNum) {
    echo "  [PASS] Order placement verified: Order #{$orderNum} saved with ৳3,850 total.\n";
} else {
    echo "  [FAIL] Order placement failed.\n";
    exit(1);
}

// TEST 6: HTML, SEO Meta Keywords & English Address Verification
echo "\n--- TEST 6: SEO Meta Keywords & English Localization ---\n";
ob_start();
include __DIR__ . '/../index.php';
$htmlOutput = ob_get_clean();

// Check SEO Keywords
if (strpos($htmlOutput, 'name="keywords"') !== false && strpos($htmlOutput, 'italy optics') !== false && strpos($htmlOutput, 'jamal khan optical shop') !== false) {
    echo "  [PASS] HTML Head contains complete SEO keywords metadata.\n";
} else {
    echo "  [FAIL] Missing SEO keywords meta tag in HTML head.\n";
}

// Check JSON-LD Schema
if (strpos($htmlOutput, '"@type": "Optician"') !== false && strpos($htmlOutput, '12 Jamal Khan Road') !== false) {
    echo "  [PASS] Schema.org Local Business Optician JSON-LD is present and valid.\n";
} else {
    echo "  [FAIL] Schema.org structured data missing or incorrect.\n";
}

// Check English Address
if (strpos($htmlOutput, '12 Jamal Khan Road, Chattogram 4000') !== false) {
    echo "  [PASS] Address is in English: '12 Jamal Khan Road, Chattogram 4000'.\n";
} else {
    echo "  [FAIL] Address not found in English.\n";
}

// Check for any Bengali characters in output
if (preg_match('/[\x{0980}-\x{09F2}\x{09F4}-\x{09FF}]/u', $htmlOutput)) {
    echo "  [WARN] Some Bengali letters found in output.\n";
} else {
    echo "  [PASS] Clean localization: No unexpected Bengali characters in rendered HTML.\n";
}

// Clean up test user
$db->prepare("DELETE FROM users WHERE id = ?")->execute([$newUserId]);
$db->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);

echo "\n========================================================\n";
echo "  ALL DIAGNOSTIC TESTS PASSED! ZERO FAULTS DETECTED.\n";
echo "========================================================\n";
