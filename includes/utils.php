<?php
require_once __DIR__ . '/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to sanitize input data
function sanitize($input) {
    global $conn;
    return $conn->real_escape_string(trim($input));
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && 
           $_SESSION['user_role'] === 'admin' && 
           isset($_SESSION['is_admin']) && 
           $_SESSION['is_admin'] === true && 
           isset($_SESSION['user_id']) && 
           isset($_SESSION['logged_in']);
}

// Function to redirect with message
function redirect($path, $message = '', $type = 'info') {
    if ($message) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    
    // Clean any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Ensure path starts with BASE_URL
    if (!str_starts_with($path, 'http') && !str_starts_with($path, '/')) {
        $path = '/' . $path;
    }
    if (!str_starts_with($path, 'http')) {
        $path = BASE_URL . $path;
    }
    
    header("Location: $path");
    exit();
}

// Function to display message
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        $bgColor = match($type) {
            'success' => 'bg-green-100 text-green-800',
            'error' => 'bg-red-100 text-red-800',
            'warning' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-blue-100 text-blue-800'
        };
        
        return "<div class='p-4 mb-4 rounded-lg $bgColor'>$message</div>";
    }
    return '';
}

// Function to generate WhatsApp checkout link
function generateWhatsAppLink($cartItems) {
    $phone = "1234567890"; // Replace with actual business phone number
    $message = "New Order:%0A";
    
    foreach ($cartItems as $item) {
        $message .= sprintf(
            "- %s (Qty: %d) @ $%s each%0A",
            $item['name'],
            $item['quantity'],
            number_format($item['price'], 2)
        );
    }
    
    $message .= "%0ATotal: $" . number_format($cartItems['total'], 2);
    return "https://wa.me/$phone?text=" . $message;
}

// Function to format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Function to get cart count
function getCartCount() {
    if (!isLoggedIn()) return 0;
    
    global $conn;
    $userId = (int)$_SESSION['user_id'];
    
    $sql = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['count'] ?? 0;
}
?> 