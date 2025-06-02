<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *'); // อนุญาต React frontend เข้าถึง API

    include 'db_connect.php';

    $is_active = isset($_GET['is_active']) ? $_GET['is_active'] : null;

    $sql = "SELECT id_banner, image_name, url, is_active, display_order FROM banner";

    if ($is_active !== null) {
        $sql .= " WHERE is_active = ?";
        $stmt = $conn->prepare($sql . " ORDER BY display_order ASC");
        $stmt->bind_param("i", $is_active);
    } else {
        $stmt = $conn->prepare($sql . " ORDER BY display_order ASC");
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $bannerData = [];
    while ($row = $result->fetch_assoc()) {
        $bannerData[] = $row;
    }

    echo json_encode($bannerData);

    $stmt->close();
    $conn->close();
?>
