<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require 'vendor/autoload.php';
require 'app/Config/Boot/Development.php';
require 'system/Boot.php';

$db = \Config\Database::connect();
$query = $db->table('users')->select('nip, name')->limit(5)->get();
print_r($query->getResultArray());
