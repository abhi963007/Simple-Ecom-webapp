<?php
require_once __DIR__ . '/../includes/header.php';

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$productId) {
    redirect('/', 'Invalid product.', 'error');
}

// Get product details
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    redirect('/', 'Product not found.', 'error');
}

$pageTitle = $product['name'];

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        redirect('/user/login.php', 'Please login to add items to cart.', 'error');
    }
    
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity <= 0 || $quantity > $product['stock']) {
        redirect("/products/details.php?id=$productId", 'Invalid quantity.', 'error');
    }
    
    $userId = $_SESSION['user_id'];
    
    // Check if product already in cart
    $sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $existingItem = $stmt->get_result()->fetch_assoc();
    
    if ($existingItem) {
        // Update quantity
        $newQuantity = $existingItem['quantity'] + $quantity;
        if ($newQuantity > $product['stock']) {
            redirect("/products/details.php?id=$productId", 'Not enough stock available.', 'error');
        }
        
        $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $newQuantity, $existingItem['id']);
    } else {
        // Add new cart item
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $userId, $productId, $quantity);
    }
    
    if ($stmt->execute()) {
        redirect("/products/details.php?id=$productId", 'Product added to cart.', 'success');
    } else {
        redirect("/products/details.php?id=$productId", 'Failed to add product to cart.', 'error');
    }
}

// Get related products
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.category_id = ? AND p.id != ? 
        LIMIT 4";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product['category_id'], $productId);
$stmt->execute();
$relatedProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb with hover effects -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li>
                <a href="/" class="text-gray-500 hover:text-blue-600 transition-colors">Home</a>
            </li>
            <li>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </li>
            <li>
                <a href="/categories/index.php?id=<?php echo $product['category_id']; ?>" 
                   class="text-gray-500 hover:text-blue-600 transition-colors">
                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                </a>
            </li>
            <li>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </li>
            <li class="text-blue-600 font-medium">
                <?php echo htmlspecialchars($product['name']); ?>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
        <!-- Product Image with 3D effect -->
        <div class="group">
            <?php if ($product['image_url']): ?>
                <div class="relative aspect-[4/3] rounded-xl overflow-hidden bg-white shadow-lg transform hover:shadow-xl transition-all duration-300">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         class="w-full h-full object-cover object-center transform group-hover:scale-105 transition-transform duration-500"
                         loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-black/0 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
            <?php else: ?>
                <div class="aspect-[4/3] bg-gray-50 rounded-xl shadow-lg flex items-center justify-center transform hover:shadow-xl transition-all duration-300">
                    <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Product Details with 3D effects -->
        <div class="bg-white rounded-xl shadow-lg p-8 transform hover:shadow-xl transition-all duration-300
                    backdrop-blur-sm bg-white/50 border border-gray-100">
            <div class="mb-6">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-50 text-blue-600">
                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                </span>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors">
                <?php echo htmlspecialchars($product['name']); ?>
            </h1>
            
            <div class="prose prose-blue mb-6 text-gray-600 text-lg leading-relaxed">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>
            
            <div class="text-3xl font-bold text-blue-600 mb-8">
                <?php echo formatPrice($product['price']); ?>
            </div>
            
            <div class="mb-8 p-4 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-sm font-medium text-gray-700 mb-2">Stock Status:</div>
                <?php if ($product['stock'] > 0): ?>
                    <div class="flex items-center text-green-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        In Stock (<?php echo $product['stock']; ?> available)
                    </div>
                <?php else: ?>
                    <div class="flex items-center text-red-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Out of Stock
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($product['stock'] > 0): ?>
                <form method="POST" class="mb-8">
                    <div class="flex items-center space-x-4">
                        <div class="w-32">
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                            <div class="relative rounded-lg shadow-sm">
                                <input type="number" id="quantity" name="quantity" value="1" min="1" 
                                       max="<?php echo $product['stock']; ?>"
                                       class="block w-full pl-3 pr-10 py-2.5 text-gray-900 border border-gray-300 rounded-lg 
                                              focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="add_to_cart" 
                                class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-transparent 
                                       text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 
                                       transform hover:-translate-y-0.5 hover:shadow-lg transition-all duration-300">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Add to Cart
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <!-- Share Buttons with 3D effects -->
            <div class="border-t pt-6">
                <div class="text-sm font-medium text-gray-700 mb-4">Share this product:</div>
                <div class="flex space-x-4">
                    <a href="#" class="transform hover:-translate-y-1 transition-transform">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 hover:bg-blue-100 transition-colors">
                            <span class="sr-only">Share on Facebook</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </a>
                    <a href="#" class="transform hover:-translate-y-1 transition-transform">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-400 hover:bg-blue-100 transition-colors">
                            <span class="sr-only">Share on Twitter</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>
                            </svg>
                        </div>
                    </a>
                    <a href="#" class="transform hover:-translate-y-1 transition-transform">
                        <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600 hover:bg-green-100 transition-colors">
                            <span class="sr-only">Share on WhatsApp</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M20.105 4.706a9.735 9.735 0 00-6.879-2.829c-5.344 0-9.7 4.356-9.7 9.7 0 1.71.444 3.379 1.287 4.854L3.92 21.63l5.2-.894a9.68 9.68 0 004.63 1.177h.004c5.344 0 9.7-4.356 9.7-9.7a9.735 9.735 0 00-2.829-6.879l-.52.521z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products with 3D effects -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Related Products</h2>
            <div class="h-1 w-20 bg-blue-600 rounded-full mb-8"></div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="group bg-white rounded-xl shadow-md overflow-hidden transform hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                        <?php if ($relatedProduct['image_url']): ?>
                            <div class="relative aspect-[4/3] overflow-hidden">
                                <img src="<?php echo htmlspecialchars($relatedProduct['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>"
                                     class="w-full h-full object-cover object-center transform group-hover:scale-105 transition-transform duration-300"
                                     loading="lazy">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                        <?php else: ?>
                            <div class="aspect-[4/3] bg-gray-50 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-4">
                            <div class="mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-600">
                                    <?php echo htmlspecialchars($relatedProduct['category_name'] ?? 'Uncategorized'); ?>
                                </span>
                            </div>
                            <h3 class="text-base font-semibold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-1">
                                <?php echo htmlspecialchars($relatedProduct['name']); ?>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 line-clamp-2 h-10">
                                <?php echo htmlspecialchars($relatedProduct['description']); ?>
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-lg font-bold text-blue-600">
                                    <?php echo formatPrice($relatedProduct['price']); ?>
                                </span>
                                <a href="<?php echo url('products/details.php?id=' . $relatedProduct['id']); ?>" 
                                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 
                                          transform hover:-translate-y-0.5 hover:shadow-md transition-all duration-300">
                                    View
                                    <svg class="ml-1.5 w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 