<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$port = 3308;

$conn = new mysqli($host, $user, $pass, null, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SHOW DATABASES");
while ($row = $result->fetch_assoc()) {
    echo $row['Database'] . "\n";
}
$conn->close();
