<?php
$root_footer = file_get_contents(__DIR__ . '/../footer.php');
// Correct relative folder for client file attachments download
$root_footer = str_replace('href="uploads/', 'href="../uploads/', $root_footer);
echo $root_footer;
?>
