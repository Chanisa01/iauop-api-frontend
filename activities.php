<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    include 'db_connect.php';

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }

    if ($_GET['action'] === 'get_activities_card') {
        $sql = "SELECT id, title, cover, description, uploaded_at 
        FROM activities 
        WHERE is_active = 1 
        ORDER BY uploaded_at DESC 
        LIMIT 6";

        $result = $conn->query($sql);

        $activities = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $activities[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'cover' => $row['cover'],
                    'description' => $row['description'],
                    'uploaded_at' => $row['uploaded_at'],
                ];
            }
        }

        // ส่งออก JSON
        echo json_encode($activities);
    }

    if ($_GET['action'] === 'get_all_activities') {
        $sql = "SELECT id, title, cover, description, uploaded_at 
        FROM activities 
        WHERE is_active = 1 
        ORDER BY uploaded_at DESC 
        ";

        $result = $conn->query($sql);

        $activities = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $activities[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'cover' => $row['cover'],
                    'description' => $row['description'],
                    'uploaded_at' => $row['uploaded_at'],
                ];
            }
        }

        // ส่งออก JSON
        echo json_encode($activities);
    }

    $conn->close();

?>