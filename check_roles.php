<?php
$db = mysqli_connect('localhost', 'root', '', 'cuti_db');
if (!$db)
    die("Connection failed: " . mysqli_connect_error());

echo "Distinct role values in users table:\n";
$res = mysqli_query($db, "SELECT DISTINCT role FROM users WHERE role IS NOT NULL");
while ($row = mysqli_fetch_assoc($res)) {
    echo "- " . $row['role'] . "\n";
}
mysqli_close($db);
