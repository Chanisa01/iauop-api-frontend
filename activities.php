<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    include 'db_connect.php';

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }

    $action = $_GET['action'] ?? '';

    if ($action === 'get_activities_card' || $action === 'get_all_activities') {
        $limit = $action === 'get_activities_card' ? "LIMIT 6" : "";

        $sql = "SELECT id, title, slug, cover, description, uploaded_at 
                FROM activities 
                WHERE is_active = 1 
                ORDER BY uploaded_at DESC 
                $limit";

        $result = $conn->query($sql);
        $activities = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $activities[] = [
                    'id' => $row['id'],
                    'slug' => $row['slug'],
                    'title' => $row['title'],
                    'cover' => $row['cover'],
                    'description' => $row['description'],
                    'uploaded_at' => $row['uploaded_at'],
                ];
            }
        }

        echo json_encode($activities);
        exit;
    }

    if ($_GET['action'] === 'get_activity_by_slug') {
        $slug = $_GET['slug'] ?? '';

        if (empty($slug)) {
            echo json_encode(['error' => 'Missing slug']);
            exit();
        }

        $stmt = $conn->prepare("SELECT id, title, slug, cover, description, uploaded_at FROM activities WHERE slug = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param("s", $slug);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            echo json_encode([
                'id' => $row['id'],
                'slug' => $row['slug'],
                'title' => $row['title'],
                'cover' => $row['cover'],
                'description' => $row['description'],
                'uploaded_at' => $row['uploaded_at'],
            ]);
        } else {
            echo json_encode(null);
        }

        exit();
    }


    $conn->close();
?>
