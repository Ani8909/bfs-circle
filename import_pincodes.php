<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '1024M');
require_once 'config.php';

echo "Reading JSON file...\n";
$json = file_get_contents('pincodes.json');
if (!$json) {
    die("Failed to read pincodes.json\n");
}

echo "JSON Size: " . strlen($json) . " bytes\n";
echo "Decoding JSON...\n";
$data = json_decode($json, true);
if (!$data) {
    die("Failed to decode JSON. Error: " . json_last_error_msg() . "\n");
}

echo "Data length: " . count($data) . "\n";

echo "Creating table...\n";
$db->exec("CREATE TABLE IF NOT EXISTS pincodes (
    pincode TEXT PRIMARY KEY,
    city TEXT,
    state TEXT
)");

echo "Starting import...\n";
$db->beginTransaction();

$stmt = $db->prepare("INSERT OR REPLACE INTO pincodes (pincode, city, state) VALUES (?, ?, ?)");

$count = 0;
foreach ($data as $row) {
    $pin = $row['pincode'] ?? '';
    $city = $row['districtName'] ?? '';
    $state = $row['stateName'] ?? '';
    
    $city = ucwords(strtolower($city));
    $state = ucwords(strtolower($state));

    if ($pin && $city && $state) {
        $stmt->execute([$pin, $city, $state]);
        $count++;
    }
}

$db->commit();
echo "Successfully processed $count records. Unique pincodes saved to DB!\n";

$c = $db->query("SELECT count(*) FROM pincodes")->fetchColumn();
echo "Total in DB: $c\n";

$r = $db->query("SELECT * FROM pincodes WHERE pincode='282001'")->fetch(PDO::FETCH_ASSOC);
print_r($r);
