<?php
require_once 'config.php';
try {
    $db->exec('ALTER TABLE applicant_bank_assignments ADD COLUMN assigned_by TEXT');
    echo 'Added assigned_by\n';
} catch (Exception $e) {
    echo $e->getMessage() . '\n';
}

try {
    $db->exec('ALTER TABLE applicant_bank_assignments ADD COLUMN rejection_reason TEXT');
    echo 'Added rejection_reason\n';
} catch (Exception $e) {
    echo $e->getMessage() . '\n';
}
?>
