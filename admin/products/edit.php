<?php
require_once __DIR__ . '/../../includes/header.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('/', 'Access denied.', 'error');
}

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $productId > 0;

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($isEdit) {
    // Get product details
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if (!$product) {
        redirect('/admin/products', 'Product not found.', 'error');
    }
    
    $pageTitle = 'Edit Product: ' . $product['name'];
} else {
    $pageTitle = 'Add New Product';
    $product = [
        'name' => '',
        'description' => '',
        'price' => '',
        'stock' => '',
        'category_id' => '',
        'image_url' => ''
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $imageUrl = sanitize($_POST['image_url']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Product name is required.";
    }
    
    if ($price <= 0) {
        $errors[] = "Price must be greater than zero.";
    }
    
    if ($stock < 0) {
        $errors[] = "Stock cannot be negative.";
    }
    
    if (empty($errors)) {
        if ($isEdit) {
            // Update existing product
            $sql = "UPDATE products SET 
                    name = ?, 
                    description = ?, 
                    price = ?, 
                    stock = ?, 
                    category_id = ?, 
                    image_url = ? 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $categoryId, $imageUrl, $productId);
        } else {
            // Create new product
            $sql = "INSERT INTO products (name, description, price, stock, category_id, image_url) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdiis", $name, $description, $price, $stock, $categoryId, $imageUrl);
        }
        
        if ($stmt->execute()) {
            redirect('/admin/products', 
                     $isEdit ? 'Product updated successfully.' : 'Product created successfully.', 
                     'success');
        } else {
            $errors[] = $isEdit ? "Failed to update product." : "Failed to create product.";
        }
    }
    
    if (!empty($errors)) {
        $error_message = implode("<br>", $errors);
        redirect('/admin/products/' . ($isEdit ? "edit.php?id=$productId" : 'create.php'), 
                 $error_message, 
                 'error');
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-1">
                <li>
                    <a href="/admin/products" class="text-gray-500 hover:text-gray-700">Products</a>
                </li>
                <li class="text-gray-500">/</li>
                <li class="text-gray-900 font-medium">
                    <?php echo $isEdit ? 'Edit Product' : 'Add New Product'; ?>
                </li>
            </ol>
        </nav>
    </div>
    
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">
            <?php echo $isEdit ? 'Edit Product' : 'Add New Product'; ?>
        </h1>
        
        <form method="POST" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                <input type="text" name="name" id="name" required
                       value="<?php echo htmlspecialchars($product['name']); ?>"
                       class="mt-1 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md">
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="mt-1 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="price" id="price" required step="0.01" min="0"
                               value="<?php echo $product['price']; ?>"
                               class="pl-7 block w-full focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>
                
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                    <input type="number" name="stock" id="stock" required min="0"
                           value="<?php echo $product['stock']; ?>"
                           class="mt-1 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" id="category_id"
                            class="mt-1 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div>
                <label for="image_url" class="block text-sm font-medium text-gray-700">Image URL</label>
                <input type="url" name="image_url" id="image_url"
                       value="<?php echo htmlspecialchars($product['image_url']); ?>"
                       class="mt-1 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md"
                       placeholder="https://example.com/image.jpg">
            </div>
            
            <div class="flex justify-end space-x-3">
                <a href="/admin/products" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <?php echo $isEdit ? 'Update Product' : 'Create Product'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 