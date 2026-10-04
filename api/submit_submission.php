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
    
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    
    // التحقق من البيانات
    if (empty($name) || empty($title) || empty($type)) {
        throw new Exception('جميع الحقول المطلوبة يجب ملؤها');
    }
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('يجب رفع ملف');
    }
    
    // تنظيف البيانات
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
    $type = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
    
    // التحقق من حجم الملف (10MB كحد أقصى)
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    if ($_FILES['file']['size'] > $maxFileSize) {
        throw new Exception('حجم الملف كبير جداً. الحد الأقصى 10MB');
    }
    
    // التحقق من نوع الملف
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg',
        'video/mp4', 'video/webm',
        'application/pdf',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    $fileType = $_FILES['file']['type'];
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception('نوع الملف غير مدعوم');
    }
    
    // إنشاء مجلد الرفع إذا لم يكن موجوداً
    $uploadDir = '../uploads/submissions/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // إنشاء اسم فريد للملف
    $fileExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    // رفع الملف
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        throw new Exception('فشل رفع الملف');
    }
    
    // حفظ المعلومات في قاعدة البيانات
    $conn = getDBConnection();
    
    if (!$conn) {
        // حذف الملف المرفوع إذا فشل الاتصال
        @unlink($filePath);
        throw new Exception('فشل الاتصال بقاعدة البيانات');
    }
    
    $fileUrl = 'uploads/submissions/' . $fileName;
    
    $stmt = $conn->prepare("INSERT INTO submissions (name, title, description, type, file_url, file_name) VALUES (?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        @unlink($filePath);
        throw new Exception('فشل في إعداد الاستعلام: ' . $conn->error);
    }
    
    $originalFileName = htmlspecialchars($_FILES['file']['name'], ENT_QUOTES, 'UTF-8');
    $stmt->bind_param("ssssss", $name, $title, $description, $type, $fileUrl, $originalFileName);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'تم رفع المشاركة بنجاح'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        @unlink($filePath);
        throw new Exception('حدث خطأ أثناء حفظ المشاركة: ' . $stmt->error);
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

