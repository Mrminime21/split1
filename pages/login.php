<?php
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/layout.php';

$auth = new Auth();
$error = '';
$success = '';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: /dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    
    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($auth->login($email, $password)) {
            header('Location: /dashboard');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    } elseif ($action === 'register') {
        try {
            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'referral_code' => $_POST['referral_code'] ?? ''
            ];
            
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                throw new Exception('All fields are required');
            }
            
            if (strlen($data['password']) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }
            
            $userId = $auth->register($data);
            $success = 'Account created successfully! You can now login.';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$title = 'Login';

ob_start();
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10 backdrop-blur-sm">
            <div class="text-center mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
                    <i data-lucide="satellite" class="h-8 w-8 text-white"></i>
                </div>
                <h2 class="text-3xl font-bold text-white" id="form-title">Welcome Back</h2>
                <p class="text-gray-300 mt-2" id="form-subtitle">Sign in to your account</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg mb-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg mb-6">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form id="login-form" method="POST" class="space-y-6">
                <input type="hidden" name="action" value="login">
                
                <div>
                    <label class="block text-gray-300 mb-2">Email Address</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"></i>
                        <input type="email" name="email" required
                               class="w-full bg-slate-700 text-white pl-10 pr-4 py-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="Enter your email">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-300 mb-2">Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"></i>
                        <input type="password" name="password" required
                               class="w-full bg-slate-700 text-white pl-10 pr-4 py-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="Enter your password">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-cyan-400 text-white py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-cyan-500 transition-all transform hover:scale-105">
                    Sign In
                </button>
            </form>

            <!-- Register Form -->
            <form id="register-form" method="POST" class="space-y-6 hidden">
                <input type="hidden" name="action" value="register">
                
                <div>
                    <label class="block text-gray-300 mb-2">Username</label>
                    <div class="relative">
                        <i data-lucide="user" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"></i>
                        <input type="text" name="username" required
                               class="w-full bg-slate-700 text-white pl-10 pr-4 py-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="Enter your username">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-300 mb-2">Email Address</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"></i>
                        <input type="email" name="email" required
                               class="w-full bg-slate-700 text-white pl-10 pr-4 py-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="Enter your email">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-300 mb-2">Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"></i>
                        <input type="password" name="password" required
                               class="w-full bg-slate-700 text-white pl-10 pr-4 py-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="Enter your password">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-300 mb-2">Referral Code (Optional)</label>
                    <input type="text" name="referral_code"
                           class="w-full bg-slate-700 text-white px-4 py-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                           placeholder="Enter referral code">
                    <p class="text-gray-400 text-sm mt-1">Enter a referral code to earn bonus rewards</p>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-cyan-400 text-white py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-cyan-500 transition-all transform hover:scale-105">
                    Create Account
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-300">
                    <span id="toggle-text">Don't have an account?</span>
                    <button id="toggle-form" class="text-cyan-400 hover:text-cyan-300 ml-2 font-medium">
                        Sign up
                    </button>
                </p>
            </div>

            <div id="welcome-bonus" class="mt-6 p-4 bg-blue-500/10 border border-blue-500/20 rounded-lg hidden">
                <h4 class="text-white font-medium mb-2">🎁 Welcome Bonus</h4>
                <p class="text-gray-300 text-sm">
                    Sign up now and get a $10 welcome bonus plus access to our 3-level referral system!
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJS = "
    let isLogin = true;
    
    document.getElementById('toggle-form').addEventListener('click', function() {
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const title = document.getElementById('form-title');
        const subtitle = document.getElementById('form-subtitle');
        const toggleText = document.getElementById('toggle-text');
        const toggleBtn = document.getElementById('toggle-form');
        const welcomeBonus = document.getElementById('welcome-bonus');
        
        if (isLogin) {
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
            title.textContent = 'Join Starlink Rent';
            subtitle.textContent = 'Create your account and start earning';
            toggleText.textContent = 'Already have an account?';
            toggleBtn.textContent = 'Sign in';
            welcomeBonus.classList.remove('hidden');
            isLogin = false;
        } else {
            registerForm.classList.add('hidden');
            loginForm.classList.remove('hidden');
            title.textContent = 'Welcome Back';
            subtitle.textContent = 'Sign in to your account';
            toggleText.textContent = \"Don't have an account?\";
            toggleBtn.textContent = 'Sign up';
            welcomeBonus.classList.add('hidden');
            isLogin = true;
        }
    });
";

echo renderLayout($title, $content, '', $additionalJS);
?>