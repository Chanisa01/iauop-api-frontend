<?php
    include 'db_connect.php';
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }

    $sql = "SELECT id_websites, name_website, url, image_name 
        FROM websites 
        WHERE is_active = 1 AND show_footer = 0";
    
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $websites = [];

        while ($row = $result->fetch_assoc()) {
            $websites[] = $row;
        }

        echo json_encode($websites);
    } else {
        echo json_encode([]);
    }

    $conn->close();
?>