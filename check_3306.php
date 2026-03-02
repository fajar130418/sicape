<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$port = 3306;

$conn = new mysqli($host, $user, $pass, null, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

$result = $conn->query("SHOW DATABASES");
while ($row = $result->fetch_assoc()) {
    echo $row['Database'] . "\n";
}
$conn->close();
