<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    include 'db_connect.php';


    // ตรวจสอบว่ามีการส่งค่า is_active หรือไม่
    $is_active = isset($_GET['is_active']) ? intval($_GET['is_active']) : null;

    try {
        if ($is_active !== null) {
            $stmt = $conn->prepare("SELECT * FROM event_banner WHERE is_active = ?");
            $stmt->bind_param("i", $is_active);
        } else {
            $stmt = $conn->prepare("SELECT * FROM event_banner");
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $banners = [];
        while ($row = $result->fetch_assoc()) {
            $banners[] = $row;
        }

        echo json_encode($banners);
    } catch (Exception $e) {
        echo json_encode([
            "error" => true,
            "message" => "เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage()
        ]);
    }

    $conn->close();
?>
