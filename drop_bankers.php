<?php
require 'config.php';
$db->exec("DROP TABLE IF EXISTS bankers");
echo "Dropped successfully.";
