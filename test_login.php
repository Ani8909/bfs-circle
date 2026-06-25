<?php
$ch = curl_init('http://localhost:8000/api.php?api=login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['username' => 'staff1', 'password' => 'staff123']);
$response = curl_exec($ch);
if(curl_errno($ch)){
    echo "Curl error: " . curl_error($ch) . "\n";
}
echo "HTTP Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo "RESPONSE RAW:\n" . $response . "\n";
?>
