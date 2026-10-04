<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    require_once 'config.php';
    
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('فشل الاتصال بقاعدة البيانات');
    }
    
    // جلب جميع المشاركات مرتبة حسب التاريخ (الأحدث أولاً)
    $stmt = $conn->prepare("SELECT id, name, title, description, type, file_url, file_name, created_at FROM submissions ORDER BY created_at DESC");
    
    if (!$stmt) {
        throw new Exception('فشل في إعداد الاستعلام: ' . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        $submissions[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'title' => $row['title'],
            'description' => $row['description'],
            'type' => $row['type'],
            'file_url' => $row['file_url'],
            'file_name' => $row['file_name'],
            'created_at' => $row['created_at']
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'submissions' => $submissions,
        'count' => count($submissions)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ: ' . $e->getMessage(),
        'submissions' => [],
        'count' => 0
    ], JSON_UNESCAPED_UNICODE);
}
?>

