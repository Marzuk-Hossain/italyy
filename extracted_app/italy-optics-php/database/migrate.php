<?php
require_once __DIR__ . '/../config/config.php';

function runMigration($verbose = true, ?array $dbOverride = null) {
    $results = [
        'success' => false,
        'messages' => [],
        'errors' => []
    ];

    $host = $dbOverride['host'] ?? DB_HOST;
    $port = $dbOverride['port'] ?? DB_PORT;
    $dbName = $dbOverride['dbname'] ?? DB_NAME;
    $user = $dbOverride['user'] ?? DB_USER;
    $pass = $dbOverride['pass'] ?? DB_PASS;
    $charset = DB_CHARSET;

    try {
        // Step 1: Connect to MySQL server (without selecting DB first)
        $dsnServer = "mysql:host={$host};port={$port};charset={$charset}";
        $pdoServer = new PDO($dsnServer, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $results['messages'][] = "Connected to MySQL server at {$host}:{$port}.";

        // Step 2: Create database if not exists
        $safeDb = str_replace('`', '``', $dbName);
        $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `{$safeDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $results['messages'][] = "Database `{$dbName}` created/verified successfully.";

        // Step 3: Connect directly to the database
        $dsnDb = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";
        $pdo = new PDO($dsnDb, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Step 4: Load schema.sql
        $schemaPath = __DIR__ . '/schema.sql';
        if (!file_exists($schemaPath)) {
            throw new Exception("schema.sql not found at: {$schemaPath}");
        }

        $sqlContent = str_replace(["\r\n", "\r"], "\n", file_get_contents($schemaPath));

        // Remove line comments
        $lines = explode("\n", $sqlContent);
        $cleanSql = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }
            $cleanSql .= $line . "\n";
        }

        // Split statements by semicolon followed by newline
        $statements = array_filter(array_map('trim', explode(";\n", $cleanSql)));

        $executedCount = 0;
        foreach ($statements as $stmt) {
            $stmt = rtrim($stmt, "; \t\n\r");
            if (!empty($stmt)) {
                if (stripos($stmt, 'USE ') === 0 || stripos($stmt, 'CREATE DATABASE ') === 0) {
                    continue;
                }
                $pdo->exec($stmt);
                $executedCount++;
            }
        }

        // Ensure existing tables also support base64 local uploads in products.image
        try {
            $pdo->exec("ALTER TABLE `products` MODIFY `image` LONGTEXT NOT NULL");
        } catch (Throwable $ignore) {
        }

        $results['messages'][] = "Executed {$executedCount} SQL statements successfully.";

        // Generate fresh password hashes with PHP so documented credentials always work.
        $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
        $customerHash = password_hash('customer123', PASSWORD_DEFAULT);

        $admin = $pdo->prepare("UPDATE users SET password_hash = ?, role = 'admin' WHERE email = ?");
        $admin->execute([$adminHash, 'admin@italyoptics.com']);
        if ($admin->rowCount() === 0) {
            $insertAdmin = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, 'admin')");
            $insertAdmin->execute(['Store Manager (Admin)', 'admin@italyoptics.com', '01708-541674', $adminHash]);
        }

        $customer = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $customer->execute([$customerHash, 'customer@italyoptics.com']);
        if ($customer->rowCount() === 0) {
            $insertCustomer = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, 'customer')");
            $insertCustomer->execute(['Tanvir Chowdhury', 'customer@italyoptics.com', '01819-556677', $customerHash]);
        }

        $results['messages'][] = "Demo admin and customer passwords have been initialized.";

        // Count seeded records
        $catCount = $pdo->query("SELECT COUNT(*) FROM `categories`")->fetchColumn();
        $prodCount = $pdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
        $userCount = $pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
        $revCount = $pdo->query("SELECT COUNT(*) FROM `reviews`")->fetchColumn();

        $results['messages'][] = "Verification: {$catCount} Categories, {$prodCount} Eyewear Products, {$userCount} Users, {$revCount} Reviews active.";
        $results['success'] = true;

    } catch (PDOException $e) {
        $results['errors'][] = "Database PDO Error: " . $e->getMessage();
        $results['success'] = false;
    } catch (Exception $e) {
        $results['errors'][] = "General Error: " . $e->getMessage();
        $results['success'] = false;
    }

    return $results;
}

// If accessed directly from CLI or Browser
if (php_sapi_name() === 'cli') {
    echo "===========================================\n";
    echo "  ITALY OPTICS - MYSQL AUTO MIGRATION SETUP\n";
    echo "===========================================\n";
    $res = runMigration(true);
    foreach ($res['messages'] as $m) echo "[OK] " . $m . "\n";
    foreach ($res['errors'] as $e) echo "[ERR] " . $e . "\n";
    echo $res['success'] ? "\n>>> Database migration completed successfully!\n" : "\n>>> Migration failed.\n";
    exit($res['success'] ? 0 : 1);
}

// If accessed directly via browser as API or page
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'migrate.php') {
    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(runMigration(false));
        exit;
    }
    header('Location: ../setup.php');
    exit;
}
