<?php
    include 'db_connect.php';
    require_once __DIR__ . '/vendor/autoload.php'; 
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    function sanitizeHtml($html) {
        $config = HTMLPurifier_Config::createDefault();

        // ✅ เปิดใช้งาน HTML tag/attribute ที่จำเป็น
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', implode(',', [
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'p', 'br', 'b', 'strong', 'i', 'em', 'u',
            'a[href|target|rel]',
            'ul', 'ol', 'li',
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
            'blockquote',
            'img[src|alt|width|height|style|class]',
            'figure[class]', 'figcaption',
            'iframe[src|width|height|frameborder|allowfullscreen|allow]',
            'oembed[url]'
        ]));

        // ✅ อนุญาต iframe เฉพาะ YouTube/Vimeo
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www.youtube.com/embed/|www.youtube.com/watch\?v=|player.vimeo.com/video/)%');

        // ✅ อนุญาต CSS style บางส่วนใน inline style
        $config->set('CSS.AllowedProperties', ['text-align', 'width', 'height', 'float', 'margin', 'padding', 'border', 'display']);

        // ✅ อนุญาต class ที่จำเป็น
        $config->set('Attr.AllowedClasses', ['table', 'media', 'align-left', 'align-right', 'center']);

        // ✅ เพิ่ม rel="noopener noreferrer" เพื่อความปลอดภัยเมื่อมี target="_blank"
        $config->set('HTML.TargetBlank', true);

        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }

    $action = $_GET['action'] ?? '';

    if ($action === 'get_activity') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM activities WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result && isset($result['description'])) {
                $result['description'] = sanitizeHtml($result['description']);
            }

            echo json_encode($result);
        }

    } elseif ($action === 'get_images') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM activities_images WHERE activities_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        }

    } elseif ($action === 'get_files') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM activities_files WHERE activities_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        }

    } else {
        http_response_code(400);
        echo json_encode(["error" => "Invalid action"]);
    }
?>