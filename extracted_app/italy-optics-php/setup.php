<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/database/migrate.php';

// 1-Click Download SQL Dump (ready for phpMyAdmin / MySQL CLI)
if (isset($_GET['download_sql']) && $_GET['download_sql'] === '1') {
    $schemaPath = __DIR__ . '/database/schema.sql';
    $sql = file_exists($schemaPath) ? file_get_contents($schemaPath) : '';
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="italy_optics_auto_mysql.sql"');
    echo $sql;
    exit;
}

$hostInput = trim($_POST['db_host'] ?? DB_HOST);
$portInput = trim($_POST['db_port'] ?? DB_PORT);
$nameInput = trim($_POST['db_name'] ?? DB_NAME);
$userInput = trim($_POST['db_user'] ?? DB_USER);
$passInput = $_POST['db_pass'] ?? DB_PASS;

$migrationResult = null;
$runAuto = (isset($_GET['auto']) && $_GET['auto'] === '1') ||
           ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'run_migration');

if ($runAuto) {
    $override = [
        'host' => $hostInput ?: 'localhost',
        'port' => $portInput ?: '3306',
        'dbname' => $nameInput ?: 'italy_optics',
        'user' => $userInput ?: 'root',
        'pass' => $passInput
    ];

    $migrationResult = runMigration(false, $override);
    if (!empty($migrationResult['success'])) {
        @file_put_contents(__DIR__ . '/config/db_override.json', json_encode($override, JSON_PRETTY_PRINT));
    }
}

// Check current status
$currentStatus = false;
$tableStats = [];
try {
    $dsn = "mysql:host={$hostInput};port={$portInput};dbname={$nameInput};charset=utf8mb4";
    $testPdo = new PDO($dsn, $userInput, $passInput, [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);
    if ($testPdo) {
        $stmt = $testPdo->query("SHOW TABLES");
        if ($stmt) {
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $currentStatus = in_array('products', $tables);
            if ($currentStatus) {
                $tableStats['products'] = $testPdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
                $tableStats['categories'] = $testPdo->query("SELECT COUNT(*) FROM `categories`")->fetchColumn();
                $tableStats['users'] = $testPdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
                $tableStats['reviews'] = $testPdo->query("SELECT COUNT(*) FROM `reviews`")->fetchColumn();
            }
        }
    }
} catch (Exception $e) {
    $currentStatus = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>1-Click Auto MySQL Setup | Italy Optics</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { background-color: #000000; color: #FFFFFF; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .setup-container { max-width: 700px; width: 100%; background: #111111; border: 1px solid #333333; border-radius: 16px; padding: 36px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.8); }
    .badge-status { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 4px 10px; border-radius: 9999px; }
    .badge-ok { background: #FFFFFF; color: #000000; }
    .badge-warn { background: #222222; color: #CCCCCC; border: 1px solid #444444; }
    .btn-action { display: inline-block; width: 100%; text-align: center; padding: 14px 24px; background: #FFFFFF; color: #000000; border: none; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; border-radius: 8px; cursor: pointer; transition: all 0.2s; text-decoration: none; }
    .btn-action:hover { background: #E5E5E5; transform: translateY(-1px); }
    .btn-outline { background: transparent; color: #FFFFFF; border: 1px solid #333333; margin-top: 10px; }
    .btn-outline:hover { background: #222222; border-color: #555555; }
    .log-box { background: #000000; border: 1px solid #222222; border-radius: 8px; padding: 16px; font-family: monospace; font-size: 12px; max-height: 220px; overflow-y: auto; margin: 16px 0; }
    .log-ok { color: #FFFFFF; margin: 4px 0; }
    .log-err { color: #FF4444; margin: 4px 0; }
    .grid-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin: 16px 0; }
    @media (min-width: 480px) { .grid-stats { grid-template-columns: repeat(4, 1fr); } }
    .stat-card { background: #181818; border: 1px solid #282828; padding: 12px; border-radius: 8px; text-align: center; }
    .stat-val { font-size: 20px; font-weight: 700; }
    .stat-lbl { font-size: 10px; color: #888888; text-transform: uppercase; }
    .db-input { width: 100%; padding: 8px 10px; background: #000; border: 1px solid #333; color: #FFF; border-radius: 6px; font-size: 12px; font-family: monospace; }
    .db-lbl { display: block; font-size: 10px; text-transform: uppercase; color: #888; margin-bottom: 4px; font-weight: 700; }
  </style>
</head>
<body>

<div class="setup-container">

  <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #222222; padding-bottom: 20px; margin-bottom: 24px; gap: 12px; flex-wrap: wrap;">
    <div>
      <div style="font-size: 11px; color: #888888; letter-spacing: 0.15em; text-transform: uppercase; font-weight: 700;">
        Italy Optics · Chittagong
      </div>
      <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; margin: 4px 0 0; letter-spacing: -0.02em;">
        1-Click Auto MySQL Database Setup
      </h1>
    </div>
    <div>
      <?php if ($currentStatus): ?>
        <span class="badge-status badge-ok">● MySQL Ready</span>
      <?php else: ?>
        <span class="badge-status badge-warn">○ Needs Setup</span>
      <?php endif; ?>
    </div>
  </div>

  <p style="font-size: 13px; color: #AAAAAA; line-height: 1.6; margin-bottom: 20px;">
    Click once to automatically create the <strong><?= htmlspecialchars($nameInput) ?></strong> MySQL database, create all 8 tables (<code style="color:#FFF;">users</code>, <code style="color:#FFF;">categories</code>, <code style="color:#FFF;">products</code>, <code style="color:#FFF;">product_colors</code>, <code style="color:#FFF;">orders</code>, <code style="color:#FFF;">order_items</code>, <code style="color:#FFF;">reviews</code>, <code style="color:#FFF;">contact_messages</code>), and populate all Italian eyewear items &amp; accounts.
  </p>

  <?php if ($currentStatus && !empty($tableStats)): ?>
    <div class="grid-stats">
      <div class="stat-card">
        <div class="stat-val"><?= $tableStats['products'] ?></div>
        <div class="stat-lbl">Products</div>
      </div>
      <div class="stat-card">
        <div class="stat-val"><?= $tableStats['categories'] ?></div>
        <div class="stat-lbl">Categories</div>
      </div>
      <div class="stat-card">
        <div class="stat-val"><?= $tableStats['users'] ?></div>
        <div class="stat-lbl">Users</div>
      </div>
      <div class="stat-card">
        <div class="stat-val"><?= $tableStats['reviews'] ?></div>
        <div class="stat-lbl">Reviews</div>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($migrationResult): ?>
    <div class="log-box">
      <?php foreach ($migrationResult['messages'] as $msg): ?>
        <div class="log-ok">✓ <?= htmlspecialchars($msg) ?></div>
      <?php endforeach; ?>
      <?php foreach ($migrationResult['errors'] as $err): ?>
        <div class="log-err">✕ <?= htmlspecialchars($err) ?></div>
      <?php endforeach; ?>
    </div>

    <?php if ($migrationResult['success']): ?>
      <div style="padding: 12px; background: #000; border: 1px solid #FFF; border-radius: 8px; margin-bottom: 20px; font-size: 13px;">
        <strong>✓ MySQL Database Created &amp; Seeded!</strong> Your website is ready to run.
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- 1-Click Auto Create Form -->
  <form method="POST">
    <input type="hidden" name="action" value="run_migration">
    <div style="background: #181818; border: 1px solid #282828; border-radius: 8px; padding: 16px; margin-bottom: 18px;">
      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px; margin-bottom: 10px;">
        <div>
          <label class="db-lbl">MySQL Host</label>
          <input type="text" name="db_host" value="<?= htmlspecialchars($hostInput) ?>" class="db-input" required>
        </div>
        <div>
          <label class="db-lbl">Port</label>
          <input type="text" name="db_port" value="<?= htmlspecialchars($portInput) ?>" class="db-input" required>
        </div>
      </div>
      <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
        <div>
          <label class="db-lbl">Database Name</label>
          <input type="text" name="db_name" value="<?= htmlspecialchars($nameInput) ?>" class="db-input" required>
        </div>
        <div>
          <label class="db-lbl">MySQL User</label>
          <input type="text" name="db_user" value="<?= htmlspecialchars($userInput) ?>" class="db-input" required>
        </div>
        <div>
          <label class="db-lbl">MySQL Password</label>
          <input type="password" name="db_pass" value="<?= htmlspecialchars($passInput) ?>" placeholder="(blank for XAMPP)" class="db-input">
        </div>
      </div>
    </div>

    <button type="submit" class="btn-action">
      <?= $currentStatus ? '↻ 1-Click Re-Sync & Repair MySQL Database' : '⚡ 1-Click Auto Create MySQL Database' ?>
    </button>
  </form>

  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
    <a href="setup.php?download_sql=1" class="btn-action btn-outline">
      ⬇ Download .SQL File
    </a>
    <a href="index.php" class="btn-action btn-outline">
      Open Showroom →
    </a>
  </div>

  <!-- Demo Accounts Notice -->
  <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #222222; font-size: 11px; color: #888888;">
    <strong style="color: #FFFFFF;">Default Credentials:</strong><br>
    • <strong>Admin:</strong> <code style="color:#FFF;">admin@italyoptics.com</code> / password: <code style="color:#FFF;">admin123</code><br>
    • <strong>Customer:</strong> <code style="color:#FFF;">customer@italyoptics.com</code> / password: <code style="color:#FFF;">customer123</code>
  </div>

</div>

</body>
</html>
