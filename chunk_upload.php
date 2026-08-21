<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['secret'] ?? '') !== 'super-secret-upload-123') die('No');
    $action = $_POST['action'] ?? '';
    
    if ($action === 'upload') {
        $chunk = $_POST['chunk'] ?? '';
        $data = base64_decode($chunk);
        file_put_contents(__DIR__ . '/crm.db.part', $data, FILE_APPEND);
        echo "OK";
    }
    elseif ($action === 'finish') {
        rename(__DIR__ . '/crm.db.part', __DIR__ . '/crm.db');
        echo "DONE";
    }
}
