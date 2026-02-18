<?php
$mysqli = new mysqli("localhost", "root", "", "", 3308);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
echo "Connected successfully to MySQL on port 3308 via TCP/IP";
$mysqli->close();
?>