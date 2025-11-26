<?php
// Transaction history page was removed from Reports module to avoid duplication.
// Redirect users to the Inventory module transaction history or the Reports 'inventory-over-time' summary.
header('Location: /admin/reports/inventory-over-time');
exit;
?>
