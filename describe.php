<?php $db = new PDO('sqlite:crm.db'); $stmt = $db->query('PRAGMA table_info(bankers)'); print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
