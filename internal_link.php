<?php
    header('Content-Type: application/json');
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET");

    include 'db_connect.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        exit;
    }

    try {
        $sql = "SELECT id_websites, name_website, url 
                FROM websites 
                WHERE show_footer = 1 AND is_active = 1 
                ORDER BY id_websites ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();
        $websites = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode($websites);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
    }
?>
