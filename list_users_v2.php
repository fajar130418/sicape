<?php
require 'vendor/autoload.php';
$env = parse_ini_file('.env');
$host = $env['database.default.hostname'] ?? 'localhost';
$user = $env['database.default.username'] ?? 'root';
$pass = $env['database.default.password'] ?? '';
$db = $env['database.default.database'] ?? 'cuti_db';
$port = $env['database.default.port'] ?? 3306;

try {
    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $result = $conn->query("SELECT nip, name FROM users LIMIT 5");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "NIP: " . $row["nip"] . " - Name: " . $row["name"] . "\n";
        }
    } else {
        echo "0 results";
    }
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
