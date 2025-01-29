<?php
$pageTitle = 'Categories';
require_once __DIR__ . '/../includes/header.php';

// Get category ID from URL
$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($categoryId) {
    // Get category details
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();
    
    if (!$category) {
        redirect('/', 'Category not found.', 'error');
    }
    
    // Get products in this category
    $sql = "SELECT * FROM products WHERE category_id = ? AND stock > 0 ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $pageTitle = $category['name'];
} else {
    // Get all categories with product count
    $sql = "SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id 
            ORDER BY c.name";
    $categories = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}
?>

<!-- Breadcrumb -->
<div class="container mx-auto px-4">
    <nav class="flex py-4 text-gray-600 text-sm">
        <a href="<?php echo url(''); ?>" class="hover:text-blue-600 transition-colors">Home</a>
        <span class="mx-2">›</span>
        <a href="<?php echo url('categories/'); ?>" class="hover:text-blue-600 transition-colors">Categories</a>
        <?php if (isset($category)): ?>
            <span class="mx-2">›</span>
            <span class="text-gray-900"><?php echo htmlspecialchars($category['name']); ?></span>
        <?php endif; ?>
    </nav>
</div>

<!-- Category Grid -->
<div class="container mx-auto px-4 py-8">
    <?php if (isset($category)): ?>
        <h1 class="text-3xl font-bold mb-8 text-gray-900">
            <?php echo htmlspecialchars($category['name']); ?>
            <div class="mt-2 h-1 w-20 bg-blue-600 rounded-full"></div>
        </h1>
        
        <?php if (!empty($category['description'])): ?>
            <p class="text-gray-600 mb-8 max-w-3xl">
                <?php echo htmlspecialchars($category['description']); ?>
            </p>
        <?php endif; ?>
        
        <?php if (empty($products)): ?>
            <div class="text-center py-12">
                <div class="inline-block p-6 rounded-full bg-blue-50 mb-4">
                    <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <p class="text-xl text-gray-600">No products found in this category.</p>
                <p class="mt-2 text-gray-500">Please check back later for new products.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <?php foreach ($products as $product): ?>
                    <a href="<?php echo url('products/' . $product['id']); ?>" 
                       class="group bg-white rounded-xl shadow-md overflow-hidden transform hover:-translate-y-2 transition-all duration-300">
                        <?php if ($product['image_url']): ?>
                            <div class="relative overflow-hidden aspect-w-1 aspect-h-1">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="w-full h-48 object-cover transform group-hover:scale-110 transition-transform duration-300">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </div>
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-2 text-gray-800 group-hover:text-blue-600 transition-colors">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                <?php echo htmlspecialchars($product['description']); ?>
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="text-xl font-bold text-blue-600">
                                    <?php echo formatPrice($product['price']); ?>
                                </span>
                                <a href="<?php echo url('products/' . $product['id']); ?>" 
                                   class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 
                                          transform hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                                    View Details
                                    <svg class="ml-2 w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <h1 class="text-3xl font-bold mb-12 text-center text-gray-900">
            Browse Categories
            <div class="mt-2 h-1 w-20 bg-blue-600 mx-auto rounded-full"></div>
        </h1>
        
        <?php if (empty($categories)): ?>
            <p class="text-gray-600 text-center">No categories available.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                <?php foreach ($categories as $cat): ?>
                    <a href="<?php echo url('categories/' . $cat['id']); ?>" 
                       class="group bg-white rounded-xl shadow-md p-8 hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300
                              relative overflow-hidden">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </h3>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-50 text-blue-600">
                                <?php echo $cat['product_count']; ?> Products
                            </span>
                        </div>
                        <?php if ($cat['description']): ?>
                            <p class="text-gray-600 mb-4 line-clamp-2">
                                <?php echo htmlspecialchars($cat['description']); ?>
                            </p>
                        <?php endif; ?>
                        <div class="flex items-center text-blue-600 group-hover:translate-x-2 transition-transform">
                            <span class="text-sm font-medium">Browse Category</span>
                            <svg class="ml-2 w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 