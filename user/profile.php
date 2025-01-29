<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    redirect('user/login.php', 'Please login to view your profile.', 'error');
}

// Get user data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get order history
$stmt = $conn->prepare("
    SELECT o.*, COUNT(oi.id) as total_items 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Profile Header -->
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 mb-8 transform hover:scale-[1.02] transition-transform duration-300">
        <div class="flex items-center space-x-6">
            <div class="h-24 w-24 bg-white rounded-xl shadow-inner flex items-center justify-center transform hover:rotate-3 transition-transform duration-300">
                <span class="text-4xl font-bold text-blue-600">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </span>
            </div>
            <div class="text-white">
                <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="text-blue-100 flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <?php echo htmlspecialchars($user['email']); ?>
                </p>
                <p class="text-blue-100 text-sm mt-2">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Account Details -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-[1.02] transition-transform duration-300">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="h-6 w-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Account Details
                </h2>
                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-4 transform hover:-translate-y-1 transition-transform duration-300">
                        <label class="text-sm text-gray-500">Username</label>
                        <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 transform hover:-translate-y-1 transition-transform duration-300">
                        <label class="text-sm text-gray-500">Email</label>
                        <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <button onclick="location.href='change_password.php'" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Change Password
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-[1.01] transition-transform duration-300">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="h-6 w-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    Recent Orders
                </h2>
                <?php if (empty($orders)): ?>
                    <div class="text-center py-8">
                        <div class="mx-auto h-24 w-24 text-gray-400 mb-4">
                            <svg class="h-full w-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <p class="text-gray-500">No orders yet</p>
                        <a href="<?php echo url('products/index.php'); ?>" class="mt-4 inline-flex items-center text-blue-500 hover:text-blue-600">
                            Start Shopping
                            <svg class="h-5 w-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($orders as $order): ?>
                            <div class="bg-gray-50 rounded-lg p-4 transform hover:-translate-y-1 transition-transform duration-300">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="text-sm text-gray-500">Order #<?php echo $order['id']; ?></span>
                                        <p class="font-medium text-gray-800">
                                            <?php echo $order['total_items']; ?> items - <?php echo formatPrice($order['total_amount']); ?>
                                        </p>
                                        <span class="text-sm text-gray-500">
                                            <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div>
                                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                            <?php echo match($order['status']) {
                                                'completed' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                default => 'bg-yellow-100 text-yellow-800'
                                            }; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <a href="orders.php" class="inline-flex items-center text-blue-500 hover:text-blue-600">
                            View All Orders
                            <svg class="h-5 w-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 