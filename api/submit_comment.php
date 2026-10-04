<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('طريقة الطلب غير صحيحة');
    }
    
    require_once 'config.php';
    
    $page = isset($_POST['page']) ? trim($_POST['page']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    
    // التحقق من البيانات
    if (empty($page) || empty($name) || empty($comment)) {
        throw new Exception('جميع الحقول مطلوبة');
    }
    
    // تنظيف البيانات من HTML
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
    $page = htmlspecialchars($page, ENT_QUOTES, 'UTF-8');
    
    // التحقق من طول البيانات
    if (strlen($name) > 255) {
        throw new Exception('الاسم طويل جداً');
    }
    
    if (strlen($comment) > 2000) {
        throw new Exception('التعليق طويل جداً');
    }
    
    // إدراج التعليق في قاعدة البيانات
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('فشل الاتصال بقاعدة البيانات');
    }
    
    $stmt = $conn->prepare("INSERT INTO comments (page, name, comment) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception('فشل في إعداد الاستعلام: ' . $conn->error);
    }
    
    $stmt->bind_param("sss", $page, $name, $comment);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'تم إرسال التعليق بنجاح'], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('حدث خطأ أثناء حفظ التعليق: ' . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

