<?php
$pageTitle = 'Products';
require_once __DIR__ . '/../includes/header.php';

// Get filters from query parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : PHP_FLOAT_MAX;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Build the base query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";
$params = [];
$types = "";

// Add search filter
if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

// Add category filter
if ($category) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

// Add price range filter
if ($min_price > 0) {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}
if ($max_price < PHP_FLOAT_MAX) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

// Add sorting
$sql .= match($sort) {
    'price_low' => " ORDER BY p.price ASC",
    'price_high' => " ORDER BY p.price DESC",
    'name_asc' => " ORDER BY p.name ASC",
    'name_desc' => " ORDER BY p.name DESC",
    default => " ORDER BY p.created_at DESC"
};

// Get categories for filter
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Get price range
$priceRange = $conn->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products")->fetch_assoc();

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Title -->
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900">Our Products</h1>
        <div class="mt-2 h-1 w-20 bg-blue-600 mx-auto rounded-full"></div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-12 transform hover:shadow-xl transition-all duration-300
                backdrop-blur-sm bg-white/50 border border-gray-100">
        <form method="GET" class="space-y-6">
            <!-- Search -->
            <div class="relative">
                <label for="search" class="block text-sm font-medium text-gray-700">Search Products</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="pl-10 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg"
                           placeholder="Search by name or description">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Category Filter -->
                <div class="relative">
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category" id="category"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg appearance-none bg-white">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none mt-6">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Price Range -->
                <div>
                    <label for="min_price" class="block text-sm font-medium text-gray-700">Price Range</label>
                    <div class="mt-1 grid grid-cols-2 gap-3">
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="min_price" id="min_price" 
                                   value="<?php echo $min_price ?: ''; ?>"
                                   min="<?php echo floor($priceRange['min_price']); ?>" 
                                   step="0.01"
                                   class="pl-7 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg"
                                   placeholder="Min">
                        </div>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="max_price" id="max_price" 
                                   value="<?php echo $max_price < PHP_FLOAT_MAX ? $max_price : ''; ?>"
                                   max="<?php echo ceil($priceRange['max_price']); ?>" 
                                   step="0.01"
                                   class="pl-7 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-lg"
                                   placeholder="Max">
                        </div>
                    </div>
                </div>
                
                <!-- Sort -->
                <div class="relative">
                    <label for="sort" class="block text-sm font-medium text-gray-700">Sort By</label>
                    <select name="sort" id="sort"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg appearance-none bg-white">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                        <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none mt-6">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-between items-center pt-4">
                <button type="submit"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform hover:-translate-y-0.5 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Apply Filters
                </button>
                
                <a href="<?php echo url('products/index.php'); ?>" 
                   class="inline-flex items-center px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Clear Filters
                </a>
            </div>
        </form>
    </div>
    
    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div class="text-center py-12 bg-white rounded-xl shadow-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No products found</h3>
            <p class="mt-2 text-gray-500">Try adjusting your search or filter criteria</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="group bg-white rounded-xl shadow-md overflow-hidden transform hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                    <?php if ($product['image_url']): ?>
                        <div class="relative aspect-[4/3] overflow-hidden">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
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
                                <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                            </span>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-1">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 line-clamp-2 h-10">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-lg font-bold text-blue-600">
                                <?php echo formatPrice($product['price']); ?>
                            </span>
                            <a href="<?php echo url('products/details.php?id=' . $product['id']); ?>" 
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
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 