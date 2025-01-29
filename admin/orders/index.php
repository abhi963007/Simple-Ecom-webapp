<?php
$pageTitle = 'Manage Orders';
require_once __DIR__ . '/../../includes/header.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('/', 'Access denied.', 'error');
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']);
    
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $orderId);
    
    if ($stmt->execute()) {
        redirect('/admin/orders', 'Order status updated successfully.', 'success');
    } else {
        redirect('/admin/orders', 'Failed to update order status.', 'error');
    }
}

// Get filter values
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$dateFrom = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';

// Build query
$sql = "SELECT o.*, u.username, u.email,
        COUNT(oi.id) as item_count,
        GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')') SEPARATOR ', ') as items
        FROM orders o 
        JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE 1=1";
$params = [];
$types = "";

if ($status) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($dateFrom) {
    $sql .= " AND DATE(o.created_at) >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}

if ($dateTo) {
    $sql .= " AND DATE(o.created_at) <= ?";
    $params[] = $dateTo;
    $types .= "s";
}

$sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total revenue
$totalRevenue = array_reduce($orders, function($carry, $order) {
    return $carry + ($order['status'] === 'completed' ? $order['total_amount'] : 0);
}, 0);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Manage Orders</h1>
        
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-500">Total Orders</h3>
                <p class="mt-2 text-3xl font-semibold text-gray-900"><?php echo count($orders); ?></p>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-500">Total Revenue</h3>
                <p class="mt-2 text-3xl font-semibold text-gray-900"><?php echo formatPrice($totalRevenue); ?></p>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-500">Pending Orders</h3>
                <p class="mt-2 text-3xl font-semibold text-gray-900">
                    <?php echo count(array_filter($orders, fn($o) => $o['status'] === 'pending')); ?>
                </p>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-500">Completed Orders</h3>
                <p class="mt-2 text-3xl font-semibold text-gray-900">
                    <?php echo count(array_filter($orders, fn($o) => $o['status'] === 'completed')); ?>
                </p>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                        <input type="date" name="date_from" id="date_from"
                               value="<?php echo $dateFrom; ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                        <input type="date" name="date_to" id="date_to"
                               value="<?php echo $dateTo; ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="/admin/orders" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Clear Filters
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Orders Table -->
        <?php if (empty($orders)): ?>
            <div class="text-center py-12">
                <h3 class="text-lg font-medium text-gray-900 mb-2">No orders found</h3>
                <p class="text-gray-500">Try adjusting your filters</p>
            </div>
        <?php else: ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Items
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        #<?php echo $order['id']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($order['username']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($order['email']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?php echo $order['item_count']; ?> items
                                    </div>
                                    <div class="text-sm text-gray-500 truncate max-w-xs" title="<?php echo htmlspecialchars($order['items']); ?>">
                                        <?php echo htmlspecialchars($order['items']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo formatPrice($order['total_amount']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('g:i A', strtotime($order['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form method="POST" class="inline-flex">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" 
                                                onchange="this.form.submit()"
                                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md 
                                                    <?php echo match($order['status']) {
                                                        'completed' => 'text-green-800 bg-green-100',
                                                        'cancelled' => 'text-red-800 bg-red-100',
                                                        default => 'text-yellow-800 bg-yellow-100'
                                                    }; ?>">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>
                                                Pending
                                            </option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>
                                                Completed
                                            </option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>
                                                Cancelled
                                            </option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-blue-600 hover:text-blue-900" 
                                       onclick="alert('Order Details:\n\n<?php echo htmlspecialchars($order['items']); ?>')">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 