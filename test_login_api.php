<?php
$ch = curl_init('http://localhost:8081/api/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['nip' => '199403182020121006', 'password' => '123456']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "Status Code: " . $info['http_code'] . "\n";
echo "Response body: " . $response . "\n";
