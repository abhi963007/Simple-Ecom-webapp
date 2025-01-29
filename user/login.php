<?php
$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';

// Rate limiting
function checkRateLimit($ip) {
    global $conn;
    $timeWindow = 5 * 60; // 5 minutes instead of 15
    $maxAttempts = 10;    // 10 attempts instead of 5

    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempts 
        FROM security_log 
        WHERE ip_address = ? 
        AND action = 'LOGIN_ATTEMPT' 
        AND status = 'FAILED'
        AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
    ");
    $stmt->bind_param("si", $ip, $timeWindow);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['attempts'] >= $maxAttempts;
}

// Log login attempt
function logLoginAttempt($userId, $status, $details) {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $stmt = $conn->prepare("
        INSERT INTO security_log (user_id, action, ip_address, user_agent, status, details)
        VALUES (?, 'LOGIN_ATTEMPT', ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $userId, $ip, $userAgent, $status, $details);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = [];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    // Debug login attempt
    error_log("Login attempt started - Email: " . $email);
    
    // Check rate limiting
    if (checkRateLimit($ip)) {
        error_log("Rate limit exceeded for IP: " . $ip);
        $_SESSION['message'] = "Too many login attempts. Please try again later.";
        $_SESSION['message_type'] = 'error';
        redirect('user/login.php');
        exit();
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
        error_log("Login error: Email is empty");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
        error_log("Login error: Invalid email format - " . $email);
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
        error_log("Login error: Password is empty");
    }
    
    if (empty($errors)) {
        try {
            error_log("Attempting database query for email: " . $email);
            
            $stmt = $conn->prepare("
                SELECT id, password, role, username, login_attempts, account_locked, is_active 
                FROM users 
                WHERE email = ? 
                LIMIT 1
            ");
            if (!$stmt) {
                error_log("Database prepare error: " . $conn->error);
                throw new Exception("Database error: " . $conn->error);
            }
            
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                error_log("Database execute error: " . $stmt->error);
                throw new Exception("Database error: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                error_log("User found in database: " . print_r($user, true));
                
                // Check if account is locked or inactive
                if ($user['account_locked'] || !$user['is_active']) {
                    error_log("Account status check failed - Locked: " . ($user['account_locked'] ? 'yes' : 'no') . ", Active: " . ($user['is_active'] ? 'yes' : 'no'));
                    logLoginAttempt($user['id'], 'FAILED', 'Account locked or inactive');
                    throw new Exception("This account has been locked. Please contact support.");
                }

                error_log("Verifying password for user: " . $user['username']);
                error_log("Stored password hash: " . $user['password']);
                error_log("Password verification result: " . (password_verify($password, $user['password']) ? 'true' : 'false'));

                if (password_verify($password, $user['password'])) {
                    error_log("Password verified successfully");
                    
                    // Reset login attempts on successful login
                    $update_stmt = $conn->prepare("
                        UPDATE users 
                        SET login_attempts = 0, 
                            last_login_attempt = NOW(),
                            account_locked = FALSE
                        WHERE id = ?
                    ");
                    $update_stmt->bind_param("i", $user['id']);
                    $update_stmt->execute();

                    // Start a new session
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['is_admin'] = ($user['role'] === 'admin');
                    $_SESSION['login_time'] = time();
                    $_SESSION['ip_address'] = $ip;
                    
                    error_log("Session variables set: " . print_r($_SESSION, true));
                    
                    // Log successful login
                    logLoginAttempt($user['id'], 'SUCCESS', 'Login successful');

                    // Special handling for admin
                    if ($user['role'] === 'admin') {
                        error_log("Redirecting admin to dashboard");
                        redirect('admin/dashboard.php', 'Welcome back, Admin!', 'success');
                    } else {
                        error_log("Redirecting user to index");
                        redirect('index.php', 'Welcome back!', 'success');
                    }
                    exit();
                } else {
                    error_log("Password verification failed");
                    // Increment login attempts
                    $attempts = $user['login_attempts'] + 1;
                    $should_lock = $attempts >= 5;
                    
                    $update_stmt = $conn->prepare("
                        UPDATE users 
                        SET login_attempts = ?, 
                            last_login_attempt = NOW(),
                            account_locked = ?
                        WHERE id = ?
                    ");
                    $update_stmt->bind_param("iii", $attempts, $should_lock, $user['id']);
                    $update_stmt->execute();

                    logLoginAttempt($user['id'], 'FAILED', 'Invalid password');
                    
                    if ($should_lock) {
                        throw new Exception("Account has been locked due to too many failed attempts. Please contact support.");
                    }
                }
            } else {
                logLoginAttempt(null, 'FAILED', 'Invalid email');
            }
            
            // Generic error message for security
            $errors[] = "Invalid email or password.";
            
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = 'error';
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center">
            <!-- Login Icon -->
            <div class="mx-auto h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome Back!</h2>
            <p class="text-gray-600 mb-6">Please sign in to your account</p>
        </div>
        
        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input type="email" id="email" name="email" required
                        class="pl-10 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter your email">
                </div>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input type="password" id="password" name="password" required
                        class="pl-10 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter your password">
                </div>
            </div>
            
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Sign In
            </button>
        </form>
        
        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Don't have an account?</span>
                </div>
            </div>
            
            <div class="mt-6">
                <a href="<?php echo url('user/register.php'); ?>" 
                   class="w-full flex justify-center items-center px-4 py-2 border border-blue-300 shadow-sm text-sm font-medium rounded-lg text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Create New Account
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 