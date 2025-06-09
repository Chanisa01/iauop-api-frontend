<?php
    include 'db_connect.php';
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }

    $sql = "SELECT dc.group_year_start, dc.group_year_end, dc.title, dc.file_name,
            ac.prename, ac.name, ac.surname, ac.image_committee_name, ac.position1, ac.position2,
            acg.group_name, acg.display_order
            FROM document_composition dc
            LEFT JOIN audit_committee ac ON ac.group_year_start = dc.group_year_start AND ac.group_year_end = dc.group_year_end
            LEFT JOIN audit_committee_group acg ON acg.id = ac.position1
            WHERE dc.is_active = 1 AND ac.is_active = 1
            ORDER BY dc.group_year_start DESC , acg.display_order ASC";

    $result = $conn->query($sql);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $groupKey = $row['group_year_start'] . '-' . $row['group_year_end'];
        if (!isset($data[$groupKey])) {
            $data[$groupKey] = [
                'documents' => [],
                'committees' => []
            ];
        }

        if (!empty($row['file_name'])) {
            $data[$groupKey]['documents'][] = [
                'title' => $row['title'],
                'file_name' => $row['file_name']
            ];
        }

        if (!empty($row['name'])) {
            $data[$groupKey]['committees'][] = [
                'prename' => $row['prename'],
                'name' => $row['name'],
                'surname' => $row['surname'],
                'image_committee_name' => $row['image_committee_name'],
                'position1' => $row['position1'],
                'position2' => $row['position2'],
                'group_name' => $row['group_name'],
                'display_order' => $row['display_order'],
            ];
        }
    }
    
    echo json_encode($data);
?>