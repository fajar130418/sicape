<?php
$db = mysqli_connect('localhost', 'root', '', 'cuti_db');
if (!$db)
    die("Connection failed: " . mysqli_connect_error());

echo "Distinct rank values in users table:\n";
$res = mysqli_query($db, "SELECT DISTINCT rank FROM users WHERE rank IS NOT NULL");
while ($row = mysqli_fetch_assoc($res)) {
    echo "- " . $row['rank'] . "\n";
}
mysqli_close($db);
