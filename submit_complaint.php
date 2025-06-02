<?php
    header('Content-Type: application/json');
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type");

    include 'db_connect.php';

    $data = json_decode(file_get_contents("php://input"), true);

    $fullName       = htmlspecialchars(trim($data['fullName'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email          = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phoneNumber    = preg_replace('/\D/', '', $data['phone'] ?? '');
    $complaintType  = htmlspecialchars(trim($data['complaintType'] ?? ''), ENT_QUOTES, 'UTF-8');
    $description    = htmlspecialchars(trim($data['detail'] ?? ''), ENT_QUOTES, 'UTF-8');
    $acknowledgement = isset($data['consent']) && $data['consent'] === true ? 1 : 0;

    if (empty($complaintType) || empty($description) || !$acknowledgement) {
        echo json_encode(['success' => false, 'error' => 'ข้อมูลจำเป็นไม่ครบถ้วน']);
        exit;
    }

    $status     = 'received';

    $stmt = $conn->prepare("
        INSERT INTO complaints (
            full_name, email, phone_number, complaint_type, description,
            acknowledgement, status, submitted_at
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->bind_param( "sssssis", $fullName, $email, $phoneNumber, $complaintType, $description, $acknowledgement, $status );

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
?>