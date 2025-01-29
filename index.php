<?php
$pageTitle = 'Welcome to Our Store';
require_once __DIR__ . '/includes/header.php';

// Get featured products (latest 8 products)
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.stock > 0 
        ORDER BY p.created_at DESC 
        LIMIT 8";
$featuredProducts = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Get categories with product count
$sql = "SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY product_count DESC 
        LIMIT 6";
$categories = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<!-- Hero Section -->
<div class="relative bg-gradient-to-br from-indigo-600 via-blue-700 to-blue-800 text-white overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20viewBox%3D%220%200%2020%2020%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20fill%3D%22%23fff%22%20fill-opacity%3D%220.05%22%20fill-rule%3D%22evenodd%22%3E%3Ccircle%20cx%3D%223%22%20cy%3D%223%22%20r%3D%223%22%2F%3E%3Ccircle%20cx%3D%2213%22%20cy%3D%2213%22%20r%3D%223%22%2F%3E%3C%2Fg%3E%3C%2Fsvg%3E')] bg-[length:16px_16px]"></div>
    </div>
    
    <!-- Content Container -->
    <div class="container mx-auto px-4 py-16 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Text Content -->
            <div class="relative z-10 text-center lg:text-left">
                <div class="relative transform hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute inset-0 bg-white/5 rounded-2xl backdrop-blur-sm -rotate-1"></div>
                    <div class="relative p-8">
                        <h1 class="text-4xl lg:text-6xl font-bold mb-6 leading-tight">
                            Discover Our <span class="text-blue-300">Amazing</span> Collection
                        </h1>
                        <p class="text-lg lg:text-xl mb-8 text-blue-100 max-w-lg mx-auto lg:mx-0">
                            Find the perfect products at great prices. Shop with confidence and enjoy our premium selection.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <a href="<?php echo url('products/index.php'); ?>" 
                               class="group inline-flex items-center justify-center bg-white text-blue-600 px-8 py-4 rounded-xl font-semibold 
                                      hover:bg-blue-50 transform hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                                Shop Now
                                <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                            <a href="<?php echo url('categories/index.php'); ?>" 
                               class="group inline-flex items-center justify-center bg-blue-700/30 text-white px-8 py-4 rounded-xl font-semibold 
                                      hover:bg-blue-700/40 backdrop-blur-sm transform hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                                Browse Categories
                                <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Image/Illustration -->
            <div class="relative z-10 hidden lg:block">
                <div class="relative transform hover:-translate-y-2 transition-transform duration-500">
                    <!-- Shopping Elements Animation -->
                    <div class="absolute -top-8 -left-8 w-16 h-16 bg-blue-400/20 rounded-full animate-float-slow"></div>
                    <div class="absolute -bottom-12 -right-8 w-24 h-24 bg-indigo-400/20 rounded-full animate-float-slower"></div>
                    
                    <!-- Main Image Container -->
                    <div class="relative bg-gradient-to-br from-white/10 to-white/5 rounded-2xl p-8 backdrop-blur-sm">
                        <div class="relative z-10 flex items-center justify-center">
                            <!-- Shopping Cart Illustration -->
                            <svg class="w-full h-auto text-white/90" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 3H5L5.4 5M7 13H17L21 5H5.4M7 13L5.4 5M7 13L4.707 15.293C4.077 15.923 4.523 17 5.414 17H17M17 17C15.8954 17 15 17.8954 15 19C15 20.1046 15.8954 21 17 21C18.1046 21 19 20.1046 19 19C19 17.8954 18.1046 17 17 17ZM9 19C9 20.1046 8.10457 21 7 21C5.89543 21 5 20.1046 5 19C5 17.8954 5.89543 17 7 17C8.10457 17 9 17.8954 9 19Z" 
                                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        
                        <!-- Decorative Elements -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-blue-400/20 to-transparent rounded-2xl"></div>
                        <div class="absolute inset-0 bg-grid-white/[0.05] bg-[size:2rem_2rem] rounded-2xl"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Wave -->
    <div class="absolute bottom-0 left-0 right-0 text-white/5">
        <svg class="w-full h-auto" viewBox="0 0 1440 120" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 0L48 8.875C96 17.75 192 35.5 288 48.875C384 62.25 480 71.25 576 66.5C672 62.25 768 44.5 864 26.75C960 8.875 1056 -8.875 1152 8.875C1248 26.75 1344 80 1392 106.625L1440 120V120H1392C1344 120 1248 120 1152 120C1056 120 960 120 864 120C768 120 672 120 576 120C480 120 384 120 288 120C192 120 96 120 48 120H0V0Z"/>
        </svg>
    </div>
</div>

<!-- Add these styles to your CSS -->
<style>
@keyframes float-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}
@keyframes float-slower {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}
.animate-float-slow {
    animation: float-slow 6s ease-in-out infinite;
}
.animate-float-slower {
    animation: float-slower 8s ease-in-out infinite;
}
</style>

<!-- Featured Products -->
<div class="container mx-auto px-4 py-16">
    <h2 class="text-3xl font-bold mb-12 text-center text-gray-800">
        Featured Products
        <div class="mt-2 h-1 w-20 bg-blue-600 mx-auto rounded-full"></div>
    </h2>
    
    <?php if (empty($featuredProducts)): ?>
        <div class="text-center py-12">
            <div class="inline-block p-6 rounded-full bg-blue-50 mb-4">
                <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </div>
            <p class="text-xl text-gray-600">No products available at the moment.</p>
            <p class="mt-2 text-gray-500">Please check back later for new products.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="group bg-white rounded-xl shadow-md overflow-hidden transform hover:-translate-y-2 transition-all duration-300">
                    <div class="relative aspect-w-4 aspect-h-3">
                        <?php if (!empty($product['image_url'])): ?>
                            <img src="<?php echo url($product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 class="w-full h-64 object-cover transform group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <?php else: ?>
                            <div class="w-full h-64 bg-gray-100 flex items-center justify-center group-hover:bg-gray-200 transition-colors">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Quick view button -->
                        <div class="absolute bottom-4 left-0 right-0 flex justify-center opacity-0 group-hover:opacity-100 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                            <a href="<?php echo url('products/details.php?id=' . $product['id']); ?>" 
                               class="bg-white/90 backdrop-blur-sm text-gray-900 px-4 py-2 rounded-full text-sm font-medium hover:bg-white
                                      flex items-center space-x-2 shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>Quick View</span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="inline-block px-3 py-1 bg-blue-50 text-blue-600 text-xs rounded-full mb-2">
                                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h3>
                            </div>
                            <div class="text-xl font-bold text-blue-600">
                                <?php echo formatPrice($product['price']); ?>
                            </div>
                        </div>
                        
                        <p class="mt-2 text-gray-600 text-sm line-clamp-2">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                        
                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="text-green-600">
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        In Stock
                                    </span>
                                <?php else: ?>
                                    <span class="text-red-600">
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Out of Stock
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($product['stock'] > 0): ?>
                                <a href="<?php echo url('cart/add.php?id=' . $product['id']); ?>" 
                                   class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 
                                          transform hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Add to Cart
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-12 text-center">
            <a href="<?php echo url('products/index.php'); ?>" 
               class="inline-flex items-center bg-gray-800 text-white px-6 py-3 rounded-lg hover:bg-gray-700 
                      transform hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                <span>View All Products</span>
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Categories Section -->
<div class="bg-gradient-to-b from-gray-50 to-white">
    <div class="container mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold mb-12 text-center text-gray-800">
            Shop by Category
            <div class="mt-2 h-1 w-20 bg-blue-600 mx-auto rounded-full"></div>
        </h2>
        
        <?php if (empty($categories)): ?>
            <p class="text-gray-600 text-center">No categories available.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                <?php foreach ($categories as $category): ?>
                    <a href="<?php echo BASE_URL; ?>/categories/index.php?id=<?php echo $category['id']; ?>" 
                       class="group bg-white rounded-xl shadow-md p-8 hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300
                              relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full -mr-16 -mt-16 group-hover:bg-blue-500/20 transition-colors"></div>
                        <div class="relative">
                            <h3 class="text-xl font-semibold mb-3 text-gray-800 group-hover:text-blue-600 transition-colors">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h3>
                            <p class="text-gray-600 mb-4">
                                <?php echo htmlspecialchars($category['description']); ?>
                            </p>
                            <div class="flex items-center text-blue-600">
                                <span class="font-semibold"><?php echo $category['product_count']; ?> Products</span>
                                <svg class="ml-2 w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 