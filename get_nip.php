<?php
require 'app/Config/Database.php';
$db = \Config\Database::connect();
$query = $db->query("SELECT nip FROM users LIMIT 1");
$row = $query->getRow();
echo $row->nip;
