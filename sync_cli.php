<?php
set_time_limit(0);
ini_set('memory_limit', '1024M');
$db = new PDO('sqlite:crm.db');
$db->exec("CREATE TABLE IF NOT EXISTS ifsc_master (ifsc TEXT PRIMARY KEY, bank TEXT, branch TEXT, address TEXT, city TEXT, state TEXT)");

$csvFile = 'IFSC.csv';
echo "Importing to database...\n";

$handle = fopen($csvFile, "r");
if ($handle !== FALSE) {
    fgetcsv($handle); // Skip header
    
    $db->beginTransaction();
    $stmt = $db->prepare("INSERT OR REPLACE INTO ifsc_master (ifsc, bank, branch, address, city, state) VALUES (?, ?, ?, ?, ?, ?)");
    
    $count = 0;
    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
        $bank = $data[0] ?? '';
        $ifsc = $data[1] ?? '';
        $branch = $data[2] ?? '';
        $state = $data[5] ?? '';
        $address = $data[6] ?? '';
        $city = $data[10] ?? '';
        
        if ($ifsc) {
            $stmt->execute([$ifsc, $bank, $branch, $address, $city, $state]);
            $count++;
        }
    }
    $db->commit();
    fclose($handle);
    echo "Success! $count bank branches saved.\n";
} else {
    echo "Failed to read CSV.\n";
}
