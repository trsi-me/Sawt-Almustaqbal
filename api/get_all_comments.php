<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    require_once 'config.php';
    
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('فشل الاتصال بقاعدة البيانات');
    }
    
    // جلب جميع التعليقات من جميع الصفحات مرتبة حسب التاريخ (الأحدث أولاً)
    $stmt = $conn->prepare("SELECT id, page, name, comment, created_at FROM comments ORDER BY created_at DESC");
    
    if (!$stmt) {
        throw new Exception('فشل في إعداد الاستعلام: ' . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'id' => $row['id'],
            'page' => $row['page'],
            'name' => $row['name'],
            'comment' => $row['comment'],
            'created_at' => $row['created_at']
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'comments' => $comments,
        'count' => count($comments)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ: ' . $e->getMessage(),
        'comments' => [],
        'count' => 0
    ], JSON_UNESCAPED_UNICODE);
}
?>

