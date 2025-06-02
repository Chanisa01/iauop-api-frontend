<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
include 'db_connect.php';

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$this_month = date('Y-m');

$sql_today = "SELECT COUNT(*) FROM visitor_logs WHERE DATE(visited_at) = ?";
$sql_yesterday = "SELECT COUNT(*) FROM visitor_logs WHERE DATE(visited_at) = ?";
$sql_month = "SELECT COUNT(*) FROM visitor_logs WHERE DATE_FORMAT(visited_at, '%Y-%m') = ?";
$sql_total = "SELECT COUNT(*) FROM visitor_logs";

$today_count = $conn->prepare($sql_today);
$today_count->bind_param("s", $today);
$today_count->execute();
$today_result = $today_count->get_result()->fetch_row()[0];

$yesterday_count = $conn->prepare($sql_yesterday);
$yesterday_count->bind_param("s", $yesterday);
$yesterday_count->execute();
$yesterday_result = $yesterday_count->get_result()->fetch_row()[0];

$month_count = $conn->prepare($sql_month);
$month_count->bind_param("s", $this_month);
$month_count->execute();
$month_result = $month_count->get_result()->fetch_row()[0];

$total_result = $conn->query($sql_total)->fetch_row()[0];

echo json_encode([
    'today' => $today_result,
    'yesterday' => $yesterday_result,
    'month' => $month_result,
    'total' => $total_result
]);
?>
