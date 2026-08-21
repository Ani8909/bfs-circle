<?php
/**
 * One-time Sync Script to populate Local IFSC Database
 */
require 'config.php';
set_time_limit(0); // Allow script to run indefinitely
ini_set('memory_limit', '1024M'); // Allow large memory for downloading

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized Access.");
}

$db->exec("CREATE TABLE IF NOT EXISTS ifsc_master (ifsc TEXT PRIMARY KEY, bank TEXT, branch TEXT, address TEXT, city TEXT, state TEXT)");

echo "<div style='font-family:sans-serif; padding:20px;'>";
echo "<h2>IFSC Master Downloader</h2>";

if (isset($_GET['action']) && $_GET['action'] == 'full_sync') {
    echo "Downloading latest IFSC data from official repository... Please wait...<br>";
    flush(); ob_flush();

    $url = "https://github.com/razorpay/ifsc/releases/latest/download/IFSC.csv";
    $csvFile = 'temp_ifsc.csv';
    
    // Download file
    $fileContent = file_get_contents($url);
    if ($fileContent === false) {
        die("<span style='color:red;'>Failed to download the IFSC CSV. Please check your internet connection.</span>");
    }
    
    file_put_contents($csvFile, $fileContent);
    echo "Download complete. Importing to database (This will take a minute)...<br>";
    flush(); ob_flush();

    // Import CSV
    $handle = fopen($csvFile, "r");
    if ($handle !== FALSE) {
        $header = fgetcsv($handle); // Skip header
        // Header mapping: BANK, IFSC, BRANCH, CENTRE, DISTRICT, STATE, ADDRESS, MICR, UPI, RTGS, CITY, NEFT, IMPS
        
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
        unlink($csvFile); // Clean up
        
        echo "<h3 style='color:green;'>Success! $count bank branches have been saved to your local database!</h3>";
        echo "<a href='add_banker.php' style='padding:10px 15px; background:#3b82f6; color:white; text-decoration:none; border-radius:5px;'>Return to Add Banker</a>";
    } else {
        echo "<span style='color:red;'>Failed to read the downloaded CSV.</span>";
    }
    echo "</div>";
    exit;
}
?>
<p>This script will download the official RBI IFSC database (~1.6 Lakh Branches) and permanently save it in your offline CRM Database.</p>
<p>Note: This process may take 1-2 minutes. Please do not close the window while it is loading.</p>
<form method="get">
    <input type="hidden" name="action" value="full_sync">
    <button type="submit" style="padding:10px 20px; background:#10b981; color:white; border:none; cursor:pointer; font-weight:bold; border-radius:5px; font-size:16px;">Download All IFSC Data Now</button>
</form>
</div>
