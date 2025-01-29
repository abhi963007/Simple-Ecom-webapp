<?php
require_once __DIR__ . '/../../includes/header.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('/', 'Access denied.', 'error');
}

// Get category ID from URL
$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $categoryId > 0;

if ($isEdit) {
    // Get category details
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();
    
    if (!$category) {
        redirect('/admin/categories', 'Category not found.', 'error');
    }
    
    $pageTitle = 'Edit Category: ' . $category['name'];
} else {
    $pageTitle = 'Add New Category';
    $category = [
        'name' => '',
        'description' => ''
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Category name is required.";
    }
    
    // Check if category name already exists
    $sql = "SELECT id FROM categories WHERE name = ? AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $name, $categoryId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "A category with this name already exists.";
    }
    
    if (empty($errors)) {
        if ($isEdit) {
            // Update existing category
            $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $name, $description, $categoryId);
        } else {
            // Create new category
            $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $name, $description);
        }
        
        if ($stmt->execute()) {
            redirect('/admin/categories', 
                     $isEdit ? 'Category updated successfully.' : 'Category created successfully.', 
                     'success');
        } else {
            $errors[] = $isEdit ? "Failed to update category." : "Failed to create category.";
        }
    }
    
    if (!empty($errors)) {
        $error_message = implode("<br>", $errors);
        redirect('/admin/categories/' . ($isEdit ? "edit.php?id=$categoryId" : 'edit.php'), 
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
                    <a href="/admin/categories" class="text-gray-500 hover:text-gray-700">Categories</a>
                </li>
                <li class="text-gray-500">/</li>
                <li class="text-gray-900 font-medium">
                    <?php echo $isEdit ? 'Edit Category' : 'Add New Category'; ?>
                </li>
            </ol>
        </nav>
    </div>
    
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">
            <?php echo $isEdit ? 'Edit Category' : 'Add New Category'; ?>
        </h1>
        
        <form method="POST" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
                <input type="text" name="name" id="name" required
                       value="<?php echo htmlspecialchars($category['name']); ?>"
                       class="mt-1 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md">
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="mt-1 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($category['description']); ?></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <a href="/admin/categories" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <?php echo $isEdit ? 'Update Category' : 'Create Category'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 