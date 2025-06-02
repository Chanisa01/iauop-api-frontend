<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
include 'db_connect.php';

$ip = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare("INSERT INTO visitor_logs (ip_address) VALUES (?)");
$stmt->bind_param("s", $ip);
$stmt->execute();
?>
