<?php
$db=new PDO('sqlite:crm.db');
$s=$db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='reminders'");
echo $s->fetchColumn();
