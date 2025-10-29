<?php
require __DIR__ . '/../vendor/autoload.php';

// Load .env manually (giống Bootstrap)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }
    }
}

$config = require __DIR__ . '/../config/database.php';
$dsn = sprintf(
    "%s:host=%s;port=%s;dbname=%s;charset=%s",
    $config['driver'],
    $config['host'],
    $config['port'],
    $config['database'],
    $config['charset']
);

echo "DSN: $dsn\n";
try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connected OK\n";

    // Liệt kê các bảng trong database
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables (" . count($tables) . "):\n";
    foreach ($tables as $t) {
        echo " - $t\n";
    }

    // Kiểm tra bảng users
    $tableToCheck = 'users';
    if (in_array($tableToCheck, $tables)) {
        echo "Table '$tableToCheck' exists. Row count: ";
        $c = $pdo->query("SELECT COUNT(*) as c FROM `$tableToCheck`")->fetch(PDO::FETCH_ASSOC);
        echo ($c['c'] ?? '0') . "\n";
    } else {
        echo "Table '$tableToCheck' NOT found in database '{$config['database']}'.\n";
    }
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
