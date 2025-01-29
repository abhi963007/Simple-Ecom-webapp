<?php
$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/../includes/header.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    redirect('user/login.php', 'Please login to view your cart.', 'error');
}

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $userId = (int)$_SESSION['user_id'];

    try {
        if ($_POST['action'] === 'update' && $quantity > 0) {
            // Check product stock
            $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if ($quantity <= $product['stock']) {
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("iii", $quantity, $userId, $productId);
                $stmt->execute();
            } else {
                $_SESSION['message'] = "Not enough stock available.";
                $_SESSION['message_type'] = 'error';
            }
        } elseif ($_POST['action'] === 'remove') {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $userId, $productId);
            $stmt->execute();
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Error updating cart.";
        $_SESSION['message_type'] = 'error';
    }

    // Redirect to prevent form resubmission
    header("Location: " . url('cart/index.php'));
    exit();
}

// Get cart items with product details
$userId = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT c.*, p.name, p.price, p.stock, p.image_url 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-8 flex items-center">
                <svg class="h-8 w-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Shopping Cart
            </h1>

            <?php if (empty($cartItems)): ?>
                <div class="text-center py-12">
                    <div class="mx-auto h-24 w-24 text-gray-400 mb-4">
                        <svg class="h-full w-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
                    <p class="text-gray-500 mb-6">Looks like you haven't added any items to your cart yet.</p>
                    <a href="<?php echo url('products/index.php'); ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Continue Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="flex items-center p-6 bg-gray-50 rounded-xl transform hover:scale-[1.01] transition-transform duration-300">
                            <!-- Product Image -->
                            <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg">
                                <?php if ($item['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         class="h-full w-full object-cover object-center">
                                <?php else: ?>
                                    <div class="h-full w-full bg-gray-200 flex items-center justify-center">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Product Details -->
                            <div class="ml-6 flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </h3>
                                    <p class="text-lg font-medium text-gray-900">
                                        <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                    </p>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Price: <?php echo formatPrice($item['price']); ?> each
                                </p>

                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <!-- Quantity Update Form -->
                                        <form method="POST" class="flex items-center space-x-2">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <label for="quantity-<?php echo $item['product_id']; ?>" class="text-sm text-gray-600">
                                                Quantity:
                                            </label>
                                            <select name="quantity" id="quantity-<?php echo $item['product_id']; ?>"
                                                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                    onchange="this.form.submit()">
                                                <?php for ($i = 1; $i <= min(10, $item['stock']); $i++): ?>
                                                    <option value="<?php echo $i; ?>" <?php echo $i === (int)$item['quantity'] ? 'selected' : ''; ?>>
                                                        <?php echo $i; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </form>

                                        <!-- Remove Item Form -->
                                        <form method="POST" class="flex items-center">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-500 flex items-center">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span class="ml-1 text-sm">Remove</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Cart Summary -->
                    <div class="mt-8 border-t border-gray-200 pt-8">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-gray-900">Total</span>
                            <span class="text-2xl font-bold text-gray-900"><?php echo formatPrice($total); ?></span>
                        </div>
                        
                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="<?php echo url('products/index.php'); ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Continue Shopping
                            </a>
                            <a href="<?php echo url('checkout/index.php'); ?>" 
                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 