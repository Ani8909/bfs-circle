<?php require "config.php"; $res = $db->query("SELECT id, username, role FROM users")->fetchAll(PDO::FETCH_ASSOC); print_r($res); ?>
