<?php
$db = mysqli_connect('localhost', 'root', '', 'cuti_db');
if (!$db)
    die("Connection failed: " . mysqli_connect_error());

// Set first user to PNS
mysqli_query($db, "UPDATE users SET user_type = 'PNS' WHERE id = 1");
// Set second user to PPPK
mysqli_query($db, "UPDATE users SET user_type = 'PPPK' WHERE id = 2");

echo "Users updated successfully.\n";
mysqli_close($db);
