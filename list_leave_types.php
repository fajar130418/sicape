<?php
$db = mysqli_connect('localhost', 'root', '', 'cuti_db');
if (!$db)
    die("Connection failed: " . mysqli_connect_error());

$res = mysqli_query($db, "SELECT * FROM leave_types");
while ($row = mysqli_fetch_assoc($res)) {
    echo "ID: " . $row['id'] . " | Name: " . $row['name'] . " | Max: " . $row['max_duration'] . " | File: " . $row['requires_file'] . "\n";
}

mysqli_close($db);
