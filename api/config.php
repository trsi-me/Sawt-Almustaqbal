<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sawt_almustaqbal');

// الاتصال بقاعدة البيانات
function getDBConnection() {
    try {
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            // محاولة إنشاء قاعدة البيانات إذا لم تكن موجودة
            $temp_conn = @new mysqli(DB_HOST, DB_USER, DB_PASS);
            if (!$temp_conn->connect_error) {
                $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                $temp_conn->query($sql);
                $temp_conn->close();
                
                // إعادة المحاولة
                $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            }
            
            if ($conn->connect_error) {
                return null;
            }
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        return null;
    }
}

// تهيئة قاعدة البيانات وإنشاء الجدول إذا لم يكن موجوداً
function initDatabase() {
    try {
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        if ($conn->connect_error) {
            return false;
        }
        
        // إنشاء قاعدة البيانات إذا لم تكن موجودة
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $conn->query($sql);
        
        $conn->close();
        
        // الاتصال بقاعدة البيانات وإنشاء الجدول
        $conn = getDBConnection();
        
        if (!$conn) {
            return false;
        }
        
        $sql = "CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_page (page),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($sql);
        
        // إنشاء جدول المشاركات
        $sql = "CREATE TABLE IF NOT EXISTS submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            type VARCHAR(50) NOT NULL,
            file_url VARCHAR(500) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_type (type),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($sql);
        $conn->close();
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// تهيئة قاعدة البيانات عند تحميل الملف (فقط إذا لم يكن هناك خطأ)
try {
    initDatabase();
} catch (Exception $e) {
    // تجاهل الخطأ في التهيئة التلقائية
}
?>

