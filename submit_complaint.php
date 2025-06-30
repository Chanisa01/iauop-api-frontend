<?php
    header('Content-Type: application/json');
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Security-Policy: frame-ancestors 'self' https://challenges.cloudflare.com;");

    include 'db_connect.php';

    // Cloudflare Turnstile verification function
    function verifyTurnstile($token, $secretKey) {
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            return false;
        }
        
        $response = json_decode($result);
        return $response->success ?? false;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    // Extract form data
    $fullName       = htmlspecialchars(trim($data['fullName'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email          = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phoneNumber    = preg_replace('/\D/', '', $data['phone'] ?? '');
    $complaintType  = htmlspecialchars(trim($data['complaintType'] ?? ''), ENT_QUOTES, 'UTF-8');
    $description    = htmlspecialchars(trim($data['detail'] ?? ''), ENT_QUOTES, 'UTF-8');
    $acknowledgement = isset($data['consent']) && $data['consent'] === true ? 1 : 0;
    $turnstileToken = $data['turnstileToken'] ?? '';

    // Validate required fields
    if (empty($complaintType) || empty($description) || !$acknowledgement) {
        echo json_encode(['success' => false, 'error' => 'ข้อมูลจำเป็นไม่ครบถ้วน']);
        exit;
    }

    // Validate Cloudflare Turnstile
    if (empty($turnstileToken)) {
        echo json_encode(['success' => false, 'error' => 'กรุณายืนยันว่าคุณไม่ใช่โปรแกรมอัตโนมัติ']);
        exit;
    }

    // Your Cloudflare Turnstile secret key
    $turnstileSecretKey = '0x4AAAAAABikgK85JTaS1n5gAFiy9bk0qGQ';
    
    if (!verifyTurnstile($turnstileToken, $turnstileSecretKey)) {
        echo json_encode(['success' => false, 'error' => 'การยืนยัน Turnstile ไม่สำเร็จ กรุณาลองใหม่อีกครั้ง']);
        exit;
    }

    $status = 'received';

    $stmt = $conn->prepare("
        INSERT INTO complaints (
            full_name, email, phone_number, complaint_type, description,
            acknowledgement, status, submitted_at
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->bind_param("sssssis", $fullName, $email, $phoneNumber, $complaintType, $description, $acknowledgement, $status);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'ส่งเรื่องร้องเรียนสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'error' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
?>