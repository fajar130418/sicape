<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'cuti_db';
$port = 3308;

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT nip, name FROM users WHERE nip='199403182020121006'");
if ($row = $result->fetch_assoc()) {
    echo "FOUND: " . $row['nip'] . " - " . $row['name'] . "\n";
} else {
    echo "NOT FOUND\n";
    $res2 = $conn->query("SELECT nip FROM users LIMIT 1");
    if ($r2 = $res2->fetch_assoc())
        echo "Sample NIP: " . $r2['nip'] . "\n";
}
$conn->close();
