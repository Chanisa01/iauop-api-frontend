<?php
    include 'db_connect.php';
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }

    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

    if ($category_id === 0) {
        echo json_encode(['error' => 'Invalid category_id']);
        exit;
    }

    $sql = "SELECT doc.id, doc.title, doc.file_name, doc.uploaded_at, cat.folder_path
            FROM document AS doc
            JOIN categories AS cat ON doc.category_id = cat.id
            WHERE is_active = 1 AND category_id = ?
            ORDER BY uploaded_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $documents = [];

    while ($row = $result->fetch_assoc()) {
        $documents[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'file_name' => $row['file_name'],
            'uploaded_at' => $row['uploaded_at'],
            'folder_path' => $row['folder_path']
        ];
    }

    echo json_encode($documents);
?>