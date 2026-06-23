<?php $db = new PDO("sqlite:crm.db"); $id=2; $lead = $db->query("SELECT * FROM leads WHERE id = $id")->fetch(PDO::FETCH_ASSOC); echo json_encode($lead);
