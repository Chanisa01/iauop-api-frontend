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

    $sql = "SELECT per.*, cat.folder_path, grp.group_name, grp.display_order
            FROM personal per
            JOIN categories cat ON per.category_id = cat.id
            JOIN personal_group grp ON per.department = grp.id
            WHERE is_active = 1 AND category_id = ?
            ORDER BY grp.display_order ASC, per.id_personal ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $personnal = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $personnal[] = [
                'folder_path' => $row['folder_path'],
                'id_personal' => $row['id_personal'],
                'prename' => $row['prename'],
                'name' => $row['name'],
                'surname' => $row['surname'],
                'department' => $row['department'],
                'position' => $row['position'],
                'certificate' => $row['certificate'],
                'types_cert' => $row['types_cert'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'extension' => $row['extension'],
                'image_personal_name' => $row['image_personal_name'],
                'group_name' => $row['group_name'],
                'display_order' => $row['display_order'],
            ];
        }
    }

    echo json_encode($personnal);
?>
