<?php
/**
 * QrServe Secure Image Handler
 * Handles upload, compression, and format conversion
 */

function handleImageUpload($file, $destination_dir, $max_size = 2097152) {
    // Validate
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error code: ' . $file['error']];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File too large. Max 2MB.'];
    }
    
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        return ['success' => false, 'error' => 'Invalid file type. Use JPG, PNG, GIF, or WebP.'];
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '.' . $ext;
    $filepath = rtrim($destination_dir, '/') . '/' . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'error' => 'Failed to save file.'];
    }
    
    // Try to convert to WebP with fallback
    $webp_path = preg_replace('/\.[^.]+$/', '.webp', $filepath);
    
    $image_info = getimagesize($filepath);
    if (!$image_info) {
        return ['success' => true, 'path' => $filename, 'webp' => false];
    }
    
    $mime = $image_info['mime'];
    $image = null;
    
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($filepath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($filepath);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($filepath);
            break;
        case 'image/webp':
            // Already WebP, just return
            return ['success' => true, 'path' => $filename, 'webp' => true];
    }
    
    if ($image) {
        // Convert with quality 80
        $result = imagewebp($image, $webp_path, 80);
        imagedestroy($image);
        
        if ($result && file_exists($webp_path)) {
            // Delete original to save space
            @unlink($filepath);
            $final_name = basename($webp_path);
            return ['success' => true, 'path' => $final_name, 'webp' => true];
        }
    }
    
    // Fallback: Keep original if WebP conversion fails
    return ['success' => true, 'path' => $filename, 'webp' => false];
}

function generateThumbnail($source_path, $width = 300, $height = 300) {
    $info = getimagesize($source_path);
    if (!$info) return false;
    
    $mime = $info['mime'];
    $image = null;
    
    switch ($mime) {
        case 'image/jpeg': $image = imagecreatefromjpeg($source_path); break;
        case 'image/png': $image = imagecreatefrompng($source_path); break;
        case 'image/gif': $image = imagecreatefromgif($source_path); break;
        case 'image/webp': $image = imagecreatefromwebp($source_path); break;
        default: return false;
    }
    
    if (!$image) return false;
    
    // Calculate dimensions
    $orig_width = imagesx($image);
    $orig_height = imagesy($image);
    $ratio = min($width/$orig_width, $height/$orig_height);
    $new_width = $orig_width * $ratio;
    $new_height = $orig_height * $ratio;
    
    // Create thumbnail
    $thumb = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG/GIF
    if ($mime == 'image/png' || $mime == 'image/gif') {
        imagealphablending($thumb, true);
        imagesavealpha($thumb, true);
    }
    
    imagecopyresampled($thumb, $image, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
    
    $thumb_path = dirname($source_path) . '/thumb_' . basename($source_path);
    imagewebp($thumb, $thumb_path, 85);
    
    imagedestroy($image);
    imagedestroy($thumb);
    
    return $thumb_path;
}
?>
