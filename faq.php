<?php
    include 'db_connect.php';
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }

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

    if ($_GET['action'] === 'get_active_faqs') {
        $sql = "SELECT id, title, description 
                FROM faqs 
                WHERE is_active = 1 
                ORDER BY id ASC
                Limit 3";
        $result = mysqli_query($conn, $sql);

        $faqs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $faq_id = $row['id'];

            // ดึงไฟล์แนบของ FAQ นี้
            $file_sql = "SELECT file_name, original_name 
                        FROM faq_files 
                        WHERE faq_id = $faq_id";
            $file_result = mysqli_query($conn, $file_sql);

            $files = [];
            while ($file_row = mysqli_fetch_assoc($file_result)) {
                $files[] = [
                    'file_name' => $file_row['file_name'],
                    'original_name' => $file_row['original_name'],
                    'folder_path' => 'information/faqs/'
                ];
            }

            // เพิ่ม key 'files' เข้าไปในแต่ละ FAQ
            $row['files'] = $files;

            $faqs[] = $row;
        }

        echo json_encode($faqs);
    }

    if ($_GET['action'] === 'get_active_all_faqs') {
        $sql = "SELECT f.id, f.title, f.description, f.uploaded_at, f.types_faq, 
                    g.group_name, g.display_order
                FROM faqs f
                LEFT JOIN faq_group g ON f.types_faq = g.id
                WHERE f.is_active = 1
                ORDER BY g.display_order ASC, f.id ASC";
        $result = mysqli_query($conn, $sql);

        $groups = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $faq_id = $row['id'];

            $file_sql = "SELECT file_name, original_name 
                        FROM faq_files 
                        WHERE faq_id = $faq_id";
            $file_result = mysqli_query($conn, $file_sql);

            $files = [];
            while ($file_row = mysqli_fetch_assoc($file_result)) {
                $files[] = [
                    'file_name' => $file_row['file_name'],
                    'original_name' => $file_row['original_name'],
                    'folder_path' => 'information/faqs/'
                ];
            }

            $row['files'] = $files;

            $groupName = $row['group_name'] ?? 'ไม่ระบุประเภท';
            $groupOrder = $row['display_order'] ?? 999;

            if (!isset($groups[$groupName])) {
                $groups[$groupName] = [
                    'display_order' => $groupOrder,
                    'faqs' => []
                ];
            }

            $groups[$groupName]['faqs'][] = $row;
        }

        // จัดกลุ่มให้เรียงตาม display_order
        uasort($groups, function ($a, $b) {
            return $a['display_order'] <=> $b['display_order'];
        });

        echo json_encode($groups);
    }


    // if ($_GET['action'] === 'get_active_all_faqs') {
    //     $sql = "SELECT id, title, description 
    //             FROM faqs 
    //             WHERE is_active = 1 
    //             ORDER BY id ASC";
    //     $result = mysqli_query($conn, $sql);

    //     $faqs = [];

    //     while ($row = mysqli_fetch_assoc($result)) {
    //         $faq_id = $row['id'];

    //         // ดึงไฟล์แนบของ FAQ นี้
    //         $file_sql = "SELECT file_name, original_name 
    //                     FROM faq_files 
    //                     WHERE faq_id = $faq_id";
    //         $file_result = mysqli_query($conn, $file_sql);

    //         $files = [];
    //         while ($file_row = mysqli_fetch_assoc($file_result)) {
    //             $files[] = [
    //                 'file_name' => $file_row['file_name'],
    //                 'original_name' => $file_row['original_name'],
    //                 'folder_path' => 'information/faqs/'
    //             ];
    //         }

    //         // เพิ่ม key 'files' เข้าไปในแต่ละ FAQ
    //         $row['files'] = $files;

    //         $faqs[] = $row;
    //     }

    //     echo json_encode($faqs);
    // }
?>