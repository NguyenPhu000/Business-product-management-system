<?php
// Quick test for supplier phone validation
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/core/Bootstrap.php';

use Modules\Category\Services\SupplierService;

$svc = new SupplierService();
$tests = [
    '+84901234567', // valid
    '0901234567', // valid
    '09-0123-4567', // invalid
    'abc123456', // invalid
    '+123', // invalid (too short)
    '+1234567890123456', // invalid (too long)
];

foreach ($tests as $t) {
    try {
        $svc->createSupplier(['name' => 'test-' . uniqid(), 'phone' => $t]);
        echo "[OK] Accepted: {$t}\n";
    } catch (Exception $e) {
        echo "[ERR] Rejected: {$t} => " . $e->getMessage() . "\n";
    }
}
