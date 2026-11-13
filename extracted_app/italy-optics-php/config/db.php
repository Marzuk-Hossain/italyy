<?php
require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;
    private static string $driver = 'mysql';
    private static ?string $lastError = null;

    public static function getConnection(): ?PDO {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            // Prefer connecting to an existing database. This also works on
            // shared hosting, where the database user cannot CREATE DATABASE.
            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 5
                ]);
            } catch (PDOException $firstError) {
                // For local XAMPP installs, create the database automatically.
                $serverDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
                $server = new PDO($serverDsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 5
                ]);
                $safeDbName = str_replace('`', '``', DB_NAME);
                $server->exec("CREATE DATABASE IF NOT EXISTS `{$safeDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 5
                ]);
            }

            // Create tables and seed the demo data on first run.
            $table = $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
            if (!$table) {
                require_once __DIR__ . '/../database/migrate.php';
                $migration = runMigration(false);
                if (empty($migration['success'])) {
                    throw new RuntimeException(implode('; ', $migration['errors'] ?? ['Database migration failed.']));
                }
            }

            // Repair only the known broken demo hash shipped in the old ZIP.
            // Custom passwords are never overwritten.
            $legacyHash = '$2y$10$w855.j70qT1E0Jsl.X8C1.FzF0Z7jZ8F.2o5uE598o9aXjQhHhW2e';
            $demoAccounts = [
                ['admin@italyoptics.com', 'admin123', 'admin'],
                ['customer@italyoptics.com', 'customer123', 'customer']
            ];
            $findDemo = $pdo->prepare("SELECT password_hash FROM users WHERE email = ?");
            $repairDemo = $pdo->prepare("UPDATE users SET password_hash = ?, role = ? WHERE email = ?");
            foreach ($demoAccounts as [$email, $plainPassword, $role]) {
                $findDemo->execute([$email]);
                $storedHash = $findDemo->fetchColumn();
                if ($storedHash === $legacyHash) {
                    $repairDemo->execute([password_hash($plainPassword, PASSWORD_DEFAULT), $role, $email]);
                }
            }

            self::$driver = 'mysql';
            self::$instance = $pdo;
            return self::$instance;
        } catch (Throwable $e) {
            self::$lastError = $e->getMessage();
            error_log('Italy Optics MySQL connection error: ' . self::$lastError);
            return null;
        }
    }

    public static function getDriverName(): string {
        return self::$instance ? self::$driver : 'unavailable';
    }

    public static function getLastError(): ?string {
        return self::$lastError;
    }

    public static function isConnected(): bool {
        return self::getConnection() !== null;
    }
}

function getDB(): ?PDO {
    return Database::getConnection();
}
