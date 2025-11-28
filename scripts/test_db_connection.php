<?php
$config = require __DIR__ . '/../config/database.php';

echo "Using DB config:\n";
echo "driver: " . ($config['driver'] ?? '') . "\n";
echo "host: " . ($config['host'] ?? '') . "\n";
echo "port: " . ($config['port'] ?? '') . "\n";
echo "database: " . ($config['database'] ?? '') . "\n";
echo "username: " . ($config['username'] ?? '') . "\n";

$dsn = sprintf("%s:host=%s;port=%d;dbname=%s;charset=%s",
    $config['driver'] ?? 'mysql',
    $config['host'] ?? '127.0.0.1',
    $config['port'] ?? 3306,
    $config['database'] ?? '',
    $config['charset'] ?? 'utf8mb4'
);

try {
    $pdo = new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database successfully.\n";

    // Try a simple query to inspect sales_details columns
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM sales_details");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "sales_details columns:\n";
        foreach ($cols as $c) {
            echo " - " . ($c['Field'] ?? '') . "\n";
        }
    } catch (Throwable $e) {
        echo "Could not read sales_details definition: " . $e->getMessage() . "\n";
    }

} catch (Throwable $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}


?>