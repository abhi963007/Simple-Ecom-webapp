<?php
$pageTitle = isset($_GET['id']) ? 'Edit Product' : 'Add Product';
require_once __DIR__ . '/../../includes/admin_header.php';

// Get categories for dropdown
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Initialize variables
$product = [
    'id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'stock' => '',
    'category_id' => '',
    'image_url' => ''
];

// If editing, get product details
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($product_data = $result->fetch_assoc()) {
        $product = $product_data;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    $errors = [];
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if ($price <= 0) {
        $errors[] = "Price must be greater than 0";
    }
    if ($stock < 0) {
        $errors[] = "Stock cannot be negative";
    }
    
    // Handle image upload
    $image_path = $product['image_url']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $image_errors = validateImage($_FILES['image']);
        if (empty($image_errors)) {
            $new_image_path = handleImageUpload($_FILES['image']);
            if ($new_image_path) {
                // Delete old image if exists
                if (!empty($image_path)) {
                    deleteProductImage($image_path);
                }
                $image_path = $new_image_path;
            } else {
                $errors[] = "Failed to upload image";
            }
        } else {
            $errors = array_merge($errors, $image_errors);
        }
    }
    
    if (empty($errors)) {
        if (isset($_GET['id'])) {
            // Update existing product
            $stmt = $conn->prepare("
                UPDATE products 
                SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image_url = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $category_id, $image_path, $_GET['id']);
        } else {
            // Insert new product
            $stmt = $conn->prepare("
                INSERT INTO products (name, description, price, stock, category_id, image_url)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssdiis", $name, $description, $price, $stock, $category_id, $image_path);
        }
        
        if ($stmt->execute()) {
            redirect('admin/products/index.php', 'Product saved successfully!');
        } else {
            $errors[] = "Error saving product: " . $conn->error;
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = 'error';
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6"><?php echo $pageTitle; ?></h1>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                <input type="text" id="name" name="name" required
                    value="<?php echo htmlspecialchars($product['name']); ?>"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                ><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" step="0.01" min="0" id="price" name="price" required
                            value="<?php echo htmlspecialchars($product['price']); ?>"
                            class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                    <input type="number" min="0" id="stock" name="stock" required
                        value="<?php echo htmlspecialchars($product['stock']); ?>"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
            
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                <select id="category_id" name="category_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                            <?php echo ($category['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700">Product Image</label>
                <?php if (!empty($product['image_url'])): ?>
                    <div class="mt-2 mb-4">
                        <img src="<?php echo url($product['image_url']); ?>" 
                             alt="Current product image"
                             class="w-32 h-32 object-cover rounded-lg border">
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*"
                    class="mt-1 block w-full text-sm text-gray-500
                           file:mr-4 file:py-2 file:px-4
                           file:rounded-md file:border-0
                           file:text-sm file:font-semibold
                           file:bg-blue-50 file:text-blue-700
                           hover:file:bg-blue-100">
                <p class="mt-1 text-sm text-gray-500">
                    Allowed formats: JPG, PNG, GIF, WebP. Maximum size: 5MB
                </p>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="<?php echo url('admin/products/index.php'); ?>"
                   class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 
                          bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white 
                           bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Product
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?> 