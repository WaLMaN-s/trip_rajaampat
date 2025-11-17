<?php
/**
 * Helper Functions for Raja Ampat Trip Website
 */

// Format currency to Rupiah
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Sanitize input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate random string
function generate_random_string($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length);
}

// Check file type
function is_valid_image($file) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    $file_type = mime_content_type($file);
    return in_array($file_type, $allowed_types);
}

// Resize image
function resize_image($source, $destination, $max_width = 800, $max_height = 600) {
    list($width, $height, $type) = getimagesize($source);
    
    if ($width <= $max_width && $height <= $max_height) {
        copy($source, $destination);
        return true;
    }
    
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);
    
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            break;
        default:
            return false;
    }
    
    imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($new_image, $destination, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($new_image, $destination, 9);
            break;
    }
    
    imagedestroy($image);
    imagedestroy($new_image);
    
    return true;
}

// Format tanggal Indonesia
function format_tanggal($date) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $split = explode('-', date('Y-n-j', strtotime($date)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Time ago
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return $difference . ' detik yang lalu';
    } elseif ($difference < 3600) {
        return floor($difference / 60) . ' menit yang lalu';
    } elseif ($difference < 86400) {
        return floor($difference / 3600) . ' jam yang lalu';
    } elseif ($difference < 604800) {
        return floor($difference / 86400) . ' hari yang lalu';
    } else {
        return date('d/m/Y H:i', $timestamp);
    }
}

// Send email notification (placeholder)
function send_email($to, $subject, $message) {
    // TODO: Implement email sending
    // You can use PHPMailer or similar library
    return true;
}

// Log activity
function log_activity($user_id, $activity, $description = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, activity, description, ip_address, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([
            $user_id,
            $activity,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        return false;
    }
}

// Get payment status badge
function get_status_badge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge badge-pending">⏳ Pending</span>';
        case 'valid':
            return '<span class="badge badge-valid">✓ Valid</span>';
        case 'invalid':
            return '<span class="badge badge-invalid">✗ Ditolak</span>';
        case 'paid':
            return '<span class="badge badge-valid">✓ Dibayar</span>';
        case 'confirmed':
            return '<span class="badge badge-valid">✓ Dikonfirmasi</span>';
        case 'cancelled':
            return '<span class="badge badge-invalid">✗ Dibatalkan</span>';
        default:
            return '<span class="badge badge-pending">?</span>';
    }
}

// Truncate text
function truncate_text($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

// Check if date is valid
function is_valid_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}