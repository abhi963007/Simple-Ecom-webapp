function redirect($path, $message = '', $type = 'success') {
    error_log("Redirect called - Path: " . $path . ", Message: " . $message . ", Type: " . $type);
    
    if (!empty($message)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
        error_log("Session message set: " . $message);
    }
    
    $url = url($path);
    error_log("Redirecting to URL: " . $url);
    
    header("Location: " . $url);
    exit();
} 

/**
 * Handle image upload for products
 * @param array $file The uploaded file from $_FILES
 * @return string|false The relative path to the uploaded image or false on failure
 */
function handleImageUpload($file) {
    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        error_log("No file uploaded or invalid upload");
        return false;
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        error_log("Invalid file type: " . $mime_type);
        return false;
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        error_log("File too large: " . $file['size']);
        return false;
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = __DIR__ . '/../uploads/products';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('product_') . '.' . $extension;
    $filepath = $upload_dir . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/products/' . $filename;
    }
    
    error_log("Failed to move uploaded file");
    return false;
}

/**
 * Delete a product image
 * @param string $image_path The relative path to the image
 * @return bool True on success, false on failure
 */
function deleteProductImage($image_path) {
    if (empty($image_path)) {
        return true;
    }
    
    $full_path = __DIR__ . '/../' . $image_path;
    if (file_exists($full_path)) {
        return unlink($full_path);
    }
    
    return true;
}

/**
 * Validate image file before upload
 * @param array $file The uploaded file from $_FILES
 * @return array Array of error messages, empty if no errors
 */
function validateImage($file) {
    $errors = [];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = "No file uploaded or invalid upload";
        return $errors;
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        $errors[] = "Invalid file type. Allowed types: JPG, PNG, GIF, WebP";
    }
    
    if ($file['size'] > $max_size) {
        $errors[] = "File too large. Maximum size: 5MB";
    }
    
    return $errors;
} 