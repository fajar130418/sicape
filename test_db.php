<?php
// Standalone script to test database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'cuti_db';

try {
    $mysqli = new mysqli($host, $user, $pass, $db);

    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: " . $mysqli->connect_error;
        exit();
    }

    echo "Connected successfully to database '$db'.\n";

    $result = $mysqli->query("SELECT count(*) as count FROM users");
    $row = $result->fetch_assoc();
    echo "User count: " . $row['count'] . "\n";

    $result = $mysqli->query("SELECT * FROM users WHERE role='admin' LIMIT 1");
    if ($row = $result->fetch_assoc()) {
        echo "Admin found: " . $row['name'] . "\n";
    } else {
        echo "No admin found.\n";
    }

} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
