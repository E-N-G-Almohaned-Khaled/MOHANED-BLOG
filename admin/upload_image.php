<?php
session_start();
require_once '../config.php';

// Yetki Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

if ($_FILES['file']['name']) {
    if (!$_FILES['file']['error']) {
        $name = md5(rand(100, 200));
        $ext = explode('.', $_FILES['file']['name']);
        $filename = $name . '.' . end($ext);
        $destination = '../uploads/' . $filename; // Admin klasöründen yukarı çık
        $location = $_FILES["file"]["tmp_name"];
        
        // Klasör kontrolü
        if (!file_exists('../uploads/')) {
            mkdir('../uploads/', 0777, true);
        }

        move_uploaded_file($location, $destination);
        
        // Return URL for Summernote (absolute or relative to site root)
        // Since admin is /admin/, we need to return ../uploads/ in a way browser understands or absolute path.
        // Easiest is to give relative path from site root if <base> is not set, or absolute path.
        // Let's assume site runs at root.
        echo '../uploads/' . $filename;
    }
    else {
      echo  $message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['file']['error'];
    }
}
?>
