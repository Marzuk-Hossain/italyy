
<?php
require_once __DIR__ . '/config/db.php';

$db = getDB();

if (!$db) {
    die("Database connection failed!");
}

if (Database::getDriverName() !== 'mysql') {
    die("MySQL is not connected!");
}

try {
    $check = $db->prepare(
        "SELECT id FROM users WHERE email = ? AND role = 'admin'"
    );

    $check->execute(['admin@italyoptics.com']);

    if (!$check->fetch()) {
        die("Admin account not found in MySQL!");
    }

    $hash = password_hash('admin123', PASSWORD_DEFAULT);

    $update = $db->prepare(
        "UPDATE users SET password_hash = ? 
         WHERE email = ? AND role = 'admin'"
    );

    $update->execute([
        $hash,
        'admin@italyoptics.com'
    ]);

    echo "Admin password fixed successfully!";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
