<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';

// Start output buffering to prevent headers already sent error
ob_start();

// Helper function to generate URLs
function url($path) {
    // Remove any leading slashes and 'index.php' from the path
    $path = ltrim($path, '/');
    $path = str_replace('index.php', '', $path);
    
    // Build the clean URL
    $url = rtrim(BASE_URL . '/' . $path, '/');
    
    // Add trailing slash for directories
    if (empty(pathinfo($path, PATHINFO_EXTENSION))) {
        $url .= '/';
    }
    
    return $url;
}

// Helper function to check if current page matches the given path
function isCurrentPage($path) {
    $current_page = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $current_page = strtolower(trim($current_page, '/'));
    $path = strtolower(trim($path, '/'));
    
    // Handle root path
    if ($path === 'index.php' && ($current_page === '' || $current_page === 'index.php')) {
        return true;
    }
    
    // Handle section paths
    if ($path === 'products' && strpos($current_page, 'products') === 0) {
        return true;
    }
    
    if ($path === 'categories' && strpos($current_page, 'categories') === 0) {
        return true;
    }
    
    return false;
}

// Process any redirects or session handling here, before any HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? "$pageTitle - " : ''; ?>Our eCommerce Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add Favicon -->
    <link rel="icon" type="image/png" href="<?php echo url('assets/images/favicon.png'); ?>">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left side - Logo and main navigation -->
                <div class="flex">
                    <!-- Logo -->
                    <a href="<?php echo url('index.php'); ?>" class="flex-shrink-0 flex items-center">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span class="ml-2 text-xl font-bold text-blue-600">Store</span>
                    </a>

                    <!-- Desktop Navigation -->
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="<?php echo url('index.php'); ?>" 
                           class="<?php echo isCurrentPage('index.php') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Home
                        </a>
                        <a href="<?php echo url('products/index.php'); ?>" 
                           class="<?php echo isCurrentPage('products') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            Products
                        </a>
                        <a href="<?php echo url('categories/index.php'); ?>" 
                           class="<?php echo isCurrentPage('categories') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            Categories
                        </a>
                    </div>
                </div>

                <!-- Right side - User menu -->
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo url('admin/dashboard.php'); ?>" 
                               class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium inline-flex items-center">
                                <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Admin
                            </a>
                        <?php endif; ?>
                        
                        <!-- Cart -->
                        <a href="<?php echo url('cart/index.php'); ?>" 
                           class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium inline-flex items-center relative">
                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Cart
                            <?php $cartCount = getCartCount(); ?>
                            <?php if ($cartCount > 0): ?>
                                <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                                    <?php echo $cartCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- User Menu -->
                        <div class="ml-3 relative group">
                            <button class="bg-white rounded-full flex text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-100">
                                    <span class="text-sm font-medium leading-none text-blue-700">
                                        <?php echo substr(htmlspecialchars($_SESSION['username'] ?? ''), 0, 1); ?>
                                    </span>
                                </span>
                            </button>
                            <div class="hidden group-hover:block absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5">
                                <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-200">
                                    Signed in as<br>
                                    <strong><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong>
                                </div>
                                <a href="<?php echo url('user/profile.php'); ?>" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    My Profile
                                </a>
                                <a href="<?php echo url('user/logout.php'); ?>" 
                                   class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50 flex items-center">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo url('user/login.php'); ?>" 
                           class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium inline-flex items-center">
                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Login
                        </a>
                        <a href="<?php echo url('user/register.php'); ?>" 
                           class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-md text-sm font-medium inline-flex items-center transition-colors">
                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            Register
                        </a>
                    <?php endif; ?>

                    <!-- Mobile menu button -->
                    <button type="button" data-mobile-menu class="sm:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="sm:hidden hidden" data-mobile-menu-items>
                <div class="pt-2 pb-3 space-y-1">
                    <a href="<?php echo url('index.php'); ?>" 
                       class="<?php echo isCurrentPage('index.php') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Home
                    </a>
                    <a href="<?php echo url('products/index.php'); ?>" 
                       class="<?php echo isCurrentPage('products') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Products
                    </a>
                    <a href="<?php echo url('categories/index.php'); ?>" 
                       class="<?php echo isCurrentPage('categories') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Categories
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="rounded-md p-4 <?php echo match($_SESSION['message_type']) {
                'success' => 'bg-green-50 border border-green-200 text-green-800',
                'error' => 'bg-red-50 border border-red-200 text-red-800',
                default => 'bg-blue-50 border border-blue-200 text-blue-800'
            }; ?>">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <?php if ($_SESSION['message_type'] === 'success'): ?>
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        <?php elseif ($_SESSION['message_type'] === 'error'): ?>
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        <?php else: ?>
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm"><?php echo $_SESSION['message']; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="flex-grow py-8"><?php // Main content will be here ?> 