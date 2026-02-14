<?php
$db = mysqli_connect('localhost', 'root', '', 'cuti_db');
if (!$db)
    die("Connection failed: " . mysqli_connect_error());

echo "Users table columns:\n";
$res = mysqli_query($db, "SHOW COLUMNS FROM users");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " (" . $row['Type'] . ") - Default: " . $row['Default'] . "\n";
}

echo "\nLeave Requests table columns:\n";
$res = mysqli_query($db, "SHOW COLUMNS FROM leave_requests");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

mysqli_close($db);
