<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dbfile'])) {
    if ($_POST['secret'] !== 'bfs-secret-123') die('Unauthorized');
    if (move_uploaded_file($_FILES['dbfile']['tmp_name'], __DIR__ . '/crm.db')) {
        echo "UPLOAD_SUCCESS";
    } else {
        echo "UPLOAD_FAILED";
    }
}
