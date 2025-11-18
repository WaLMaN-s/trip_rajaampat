<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rajaampat_trip');

// Site Configuration
define('SITE_NAME', 'Raja Ampat Trip');
define('SITE_URL', 'http://localhost/trip_rajaampat/public');
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL', SITE_URL . '/public/uploads/');

// Payment Configuration
define('QRIS_IMAGE', 'qris-rajaampat.png');
define('BANK_NAME', 'Bank BCA');
define('BANK_ACCOUNT', '1234567890');
define('BANK_HOLDER', 'PT Raja Ampat Trip');

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper Functions
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function alert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function show_alert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        $class = $alert['type'] === 'success' ? 'alert-success' : 'alert-danger';
        echo "<div class='alert {$class}'>{$alert['message']}</div>";
        unset($_SESSION['alert']);
    }
}