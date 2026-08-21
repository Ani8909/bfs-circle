<?php
$lines = file('api.php');
foreach($lines as $i => $line) {
    if (stripos($line, 'referral') !== false) {
        echo ($i+1) . ': ' . trim($line) . "\n";
    }
}
?>
