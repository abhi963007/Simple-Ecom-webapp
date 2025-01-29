<?php
$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    redirect('/user/login.php', 'Please login to view your cart.', 'error');
}

$userId = $_SESSION['user_id'];

// Handle quantity updates and item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $cartId = (int)$_POST['cart_id'];
        $quantity = (int)$_POST['quantity'];
        
        if ($quantity <= 0) {
            // Remove item if quantity is 0 or negative
            $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $cartId, $userId);
        } else {
            // Update quantity
            $sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $quantity, $cartId, $userId);
        }
        $stmt->execute();
        
        redirect('/cart/cart.php', 'Cart updated successfully.', 'success');
    } elseif (isset($_POST['remove_item'])) {
        $cartId = (int)$_POST['cart_id'];
        
        $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
        
        redirect('/cart/cart.php', 'Item removed from cart.', 'success');
    }
}

// Get cart items with product details
$sql = "SELECT c.id as cart_id, c.quantity, p.* 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$subtotal = 0;
$itemCount = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $itemCount += $item['quantity'];
}

// Prepare WhatsApp message
$whatsappMessage = "New Order:%0A%0A";
foreach ($cartItems as $item) {
    $whatsappMessage .= sprintf(
        "- %s (Qty: %d) @ $%s each%0A",
        $item['name'],
        $item['quantity'],
        number_format($item['price'], 2)
    );
}
$whatsappMessage .= "%0ASubtotal: $" . number_format($subtotal, 2);
$whatsappPhone = "1234567890"; // Replace with actual business phone number
$whatsappUrl = "https://wa.me/$whatsappPhone?text=" . $whatsappMessage;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold mb-8">Shopping Cart</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-600 mb-4">Your cart is empty.</p>
            <a href="/products" 
               class="inline-block bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-8">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($cartItems as $item): ?>
                            <li class="p-6">
                                <div class="flex items-center">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0 w-24 h-24">
                                        <?php if ($item['image_url']): ?>
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                 class="w-full h-full object-cover rounded-lg">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gray-200 rounded-lg flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Product Details -->
                                    <div class="ml-6 flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h3 class="text-lg font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </h3>
                                                <p class="mt-1 text-sm text-gray-500">
                                                    Price: <?php echo formatPrice($item['price']); ?>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-medium text-gray-900">
                                                    <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4 flex items-center justify-between">
                                            <!-- Quantity Update Form -->
                                            <form method="POST" class="flex items-center space-x-2">
                                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                                <label for="quantity-<?php echo $item['cart_id']; ?>" class="sr-only">Quantity</label>
                                                <input type="number" id="quantity-<?php echo $item['cart_id']; ?>" 
                                                       name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                       min="0" max="<?php echo $item['stock']; ?>"
                                                       class="w-16 border rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
                                                <button type="submit" name="update_quantity"
                                                        class="text-sm text-blue-600 hover:text-blue-800">
                                                    Update
                                                </button>
                                            </form>
                                            
                                            <!-- Remove Item Form -->
                                            <form method="POST" class="flex items-center">
                                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                                <button type="submit" name="remove_item"
                                                        class="text-sm text-red-600 hover:text-red-800">
                                                    Remove
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>
                    
                    <div class="flow-root">
                        <dl class="-my-4 text-sm divide-y divide-gray-200">
                            <div class="py-4 flex items-center justify-between">
                                <dt class="text-gray-600">Subtotal</dt>
                                <dd class="font-medium text-gray-900"><?php echo formatPrice($subtotal); ?></dd>
                            </div>
                            <div class="py-4 flex items-center justify-between">
                                <dt class="text-gray-600">Total Items</dt>
                                <dd class="font-medium text-gray-900"><?php echo $itemCount; ?></dd>
                            </div>
                            <div class="py-4 flex items-center justify-between">
                                <dt class="text-base font-medium text-gray-900">Order Total</dt>
                                <dd class="text-base font-medium text-gray-900"><?php echo formatPrice($subtotal); ?></dd>
                            </div>
                        </dl>
                    </div>
                    
                    <div class="mt-6">
                        <a href="<?php echo htmlspecialchars($whatsappUrl); ?>" target="_blank"
                           class="w-full bg-green-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-600 transition duration-200 flex items-center justify-center">
                            <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.105 4.706a9.735 9.735 0 00-6.879-2.829c-5.344 0-9.7 4.356-9.7 9.7 0 1.71.444 3.379 1.287 4.854L3.92 21.63l5.2-.894a9.68 9.68 0 004.63 1.177h.004c5.344 0 9.7-4.356 9.7-9.7a9.735 9.735 0 00-2.829-6.879l-.52.521zm-6.879-1.783a8.69 8.69 0 016.129 2.525 8.69 8.69 0 012.525 6.129c0 4.77-3.884 8.654-8.654 8.654a8.62 8.62 0 01-4.12-1.046l-.235-.139-3.367.58.584-3.367-.139-.235a8.61 8.61 0 01-1.046-4.12c0-4.77 3.884-8.654 8.654-8.654z"/>
                            </svg>
                            Checkout via WhatsApp
                        </a>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <a href="/products" class="text-sm text-blue-600 hover:text-blue-800">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 