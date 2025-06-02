<?php
    include 'db_connect.php';
    require_once __DIR__ . '/vendor/autoload.php'; 
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }

    function sanitizeHtml($html) {
        $config = HTMLPurifier_Config::createDefault();

        // ✅ Doctype ที่รองรับ HTML ทั่วไป
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');

        // ✅ อนุญาตเฉพาะแท็กข้อความทั่วไป ไม่มี media ใดๆ
        $config->set('HTML.Allowed', implode(',', [
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'p', 'br', 'b', 'strong', 'i', 'em', 'u',
            'a[href|target|rel]',
            'ul', 'ol', 'li',
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
            'blockquote',
            'figure[class]', 'figcaption'
            // ❌ ไม่มี img, iframe, oembed
        ]));

        // ❌ ปิด SafeIframe
        $config->set('HTML.SafeIframe', false);

        // ✅ อนุญาต style บางอย่างสำหรับข้อความ
        $config->set('CSS.AllowedProperties', ['text-align', 'margin', 'padding']);

        // ✅ อนุญาตบาง class
        $config->set('Attr.AllowedClasses', ['table', 'align-left', 'align-right', 'center']);

        // ✅ เพิ่ม rel="noopener noreferrer" เพื่อความปลอดภัย
        $config->set('HTML.TargetBlank', true);

        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }

    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
    if ($category_id === 0) {
        echo json_encode(['error' => 'Invalid category_id']);
        exit;
    }

    $sql = "SELECT a.*, cat.folder_path
        FROM article a
        JOIN categories cat ON a.category_id = cat.id
        WHERE category_id = ?";

    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $articles = [];
    while ($row = $result->fetch_assoc()) {
        if (isset($row['description_th'])) {
            $row['description_th'] = sanitizeHtml($row['description_th']);
        }
        $articles[] = $row;
    }

    // แสดงผลลัพธ์ในรูปแบบ JSON
    echo json_encode($articles, JSON_UNESCAPED_UNICODE);

    // ปิดการเชื่อมต่อ
    $stmt->close();
    $conn->close();
?>