<?php
/**
 * Starlink Router Rent - Installation Script
 * Version: 2.0.0
 * 
 * This script handles the complete installation of the Starlink Router Rent system
 * including database setup, configuration, and initial admin user creation.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

class StarlinkRouterRentInstaller {
    private $config = [];
    private $pdo = null;
    private $errors = [];
    private $success = [];

    public function __construct() {
        session_start();
    }

    /**
     * Main installation process
     */
    public function install() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processInstallation();
        }
        $this->showInstallationForm();
    }

    /**
     * Process the installation form submission
     */
    private function processInstallation() {
        try {
            // Validate input
            $this->validateInput();
            
            // Test database connection
            $this->testDatabaseConnection();
            
            // Create configuration file
            $this->createConfigFile();
            
            // Import database schema
            $this->importDatabase();
            
            // Create admin user
            $this->createAdminUser();
            
            // Set up directories
            $this->setupDirectories();
            
            // Generate security keys
            $this->generateSecurityKeys();
            
            $this->success[] = "Installation completed successfully!";
            $this->success[] = "You can now access the admin panel at: /admin";
            $this->success[] = "Default admin credentials - Username: " . $_POST['admin_username'] . ", Password: " . $_POST['admin_password'];
            
        } catch (Exception $e) {
            $this->errors[] = "Installation failed: " . $e->getMessage();
        }
    }

    /**
     * Validate form input
     */
    private function validateInput() {
        $required = ['db_host', 'db_name', 'db_username', 'admin_username', 'admin_email', 'admin_password'];
        
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field '{$field}' is required");
            }
        }

        if (!filter_var($_POST['admin_email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid admin email address");
        }

        if (strlen($_POST['admin_password']) < 6) {
            throw new Exception("Admin password must be at least 6 characters long");
        }
    }

    /**
     * Test database connection
     */
    private function testDatabaseConnection() {
        try {
            $dsn = "mysql:host=" . $_POST['db_host'] . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, $_POST['db_username'], $_POST['db_password'] ?? '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $this->pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $_POST['db_name'] . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->pdo->exec("USE `" . $_POST['db_name'] . "`");
            
            $this->success[] = "Database connection successful";
            
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Create configuration file
     */
    private function createConfigFile() {
        $config = [
            'database' => [
                'host' => $_POST['db_host'],
                'name' => $_POST['db_name'],
                'username' => $_POST['db_username'],
                'password' => $_POST['db_password'] ?? '',
                'charset' => 'utf8mb4'
            ],
            'app' => [
                'name' => 'Starlink Router Rent',
                'url' => $_POST['site_url'] ?? 'http://localhost',
                'debug' => false,
                'timezone' => $_POST['timezone'] ?? 'UTC'
            ],
            'security' => [
                'jwt_secret' => $this->generateRandomString(64),
                'encryption_key' => $this->generateRandomString(32),
                'password_salt' => $this->generateRandomString(16)
            ],
            'payments' => [
                'plisio_api_key' => $_POST['plisio_api_key'] ?? '',
                'binance_api_key' => $_POST['binance_api_key'] ?? '',
                'binance_secret' => $_POST['binance_secret'] ?? ''
            ],
            'telegram' => [
                'bot_token' => $_POST['telegram_bot_token'] ?? '',
                'webhook_url' => ($_POST['site_url'] ?? 'http://localhost') . '/api/telegram/webhook'
            ]
        ];

        $configContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        
        if (!file_put_contents('config.php', $configContent)) {
            throw new Exception("Failed to create configuration file");
        }

        $this->config = $config;
        $this->success[] = "Configuration file created";
    }

    /**
     * Import database schema
     */
    private function importDatabase() {
        $sqlFile = 'database.sql';
        
        if (!file_exists($sqlFile)) {
            throw new Exception("Database schema file not found: {$sqlFile}");
        }

        $sql = file_get_contents($sqlFile);
        
        // Remove comments and split into statements
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolon but ignore those inside quotes or delimiters
        $statements = $this->splitSqlStatements($sql);

        $successCount = 0;
        $errorCount = 0;

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || strtoupper(substr($statement, 0, 3)) === 'SET' || strtoupper(substr($statement, 0, 6)) === 'SELECT') {
                continue;
            }

            try {
                $this->pdo->exec($statement);
                $successCount++;
            } catch (PDOException $e) {
                $errorCount++;
                // Log error but continue (some statements might fail on re-installation)
                error_log("SQL Error: " . $e->getMessage() . " in statement: " . substr($statement, 0, 100));
                
                // Only throw exception for critical errors
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate') === false) {
                    throw new Exception("Database import failed: " . $e->getMessage());
                }
            }
        }

        $this->success[] = "Starlink Router Rent database schema imported successfully ({$successCount} statements executed)";
        if ($errorCount > 0) {
            $this->success[] = "Note: {$errorCount} statements were skipped (likely already exist)";
        }
    }

    /**
     * Split SQL statements properly handling delimiters
     */
    private function splitSqlStatements($sql) {
        $statements = [];
        $current = '';
        $delimiter = ';';
        
        $lines = explode("\n", $sql);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and comments
            if (empty($line) || substr($line, 0, 2) === '--' || substr($line, 0, 2) === '/*') {
                continue;
            }
            
            $current .= $line . "\n";
            
            // Check if statement ends with current delimiter
            if (substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
                $statements[] = substr($current, 0, -strlen($delimiter) - 1);
                $current = '';
            }
        }
        
        // Add any remaining statement
        if (!empty(trim($current))) {
            $statements[] = $current;
        }
        
        return $statements;
    }

    /**
     * Create admin user
     */
    private function createAdminUser() {
        $username = $_POST['admin_username'];
        $email = $_POST['admin_email'];
        $password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);

        // Check if admin already exists
        $existing = $this->pdo->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
        $existing->execute([$username, $email]);
        
        if ($existing->fetch()) {
            // Update existing admin
            $stmt = $this->pdo->prepare("
                UPDATE admin_users 
                SET email = ?, password_hash = ?, updated_at = NOW() 
                WHERE username = ?
            ");
            $stmt->execute([$email, $password, $username]);
            $this->success[] = "Admin user updated successfully";
        } else {
            // Create new admin
            $stmt = $this->pdo->prepare("
                INSERT INTO admin_users (username, email, password_hash, role, status, created_at) 
                VALUES (?, ?, ?, 'super_admin', 'active', NOW())
            ");
            $stmt->execute([$username, $email, $password]);
            $this->success[] = "Admin user created successfully";
        }
    }

    /**
     * Set up required directories
     */
    private function setupDirectories() {
        $directories = [
            'uploads',
            'logs',
            'cache',
            'temp',
            'templates',
            'templates/email',
            'api',
            'api/plisio',
            'pages/payment'
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new Exception("Failed to create directory: {$dir}");
                }
            }
            
            // Create .htaccess for security (except for api and pages)
            if (!in_array($dir, ['api', 'api/plisio', 'pages', 'pages/payment'])) {
                $htaccess = $dir . '/.htaccess';
                if (!file_exists($htaccess)) {
                    file_put_contents($htaccess, "deny from all\n");
                }
            }
        }

        $this->success[] = "Required directories created";
    }

    /**
     * Generate security keys and tokens
     */
    private function generateSecurityKeys() {
        // Create .env file for environment variables
        $envContent = "# Starlink Router Rent Environment Configuration\n";
        $envContent .= "APP_ENV=production\n";
        $envContent .= "APP_DEBUG=false\n";
        $envContent .= "JWT_SECRET=" . $this->generateRandomString(64) . "\n";
        $envContent .= "ENCRYPTION_KEY=" . $this->generateRandomString(32) . "\n";
        
        file_put_contents('.env', $envContent);
        
        $this->success[] = "Security keys generated";
    }

    /**
     * Generate random string
     */
    private function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Show installation form
     */
    private function showInstallationForm() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Starlink Router Rent - Installation</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                    color: #e2e8f0;
                    min-height: 100vh;
                    padding: 20px;
                }
                .container { 
                    max-width: 800px; 
                    margin: 0 auto; 
                    background: rgba(30, 41, 59, 0.8);
                    border-radius: 16px;
                    padding: 40px;
                    border: 1px solid rgba(59, 130, 246, 0.2);
                }
                .header {
                    text-align: center;
                    margin-bottom: 40px;
                }
                .logo {
                    background: linear-gradient(135deg, #3b82f6, #06b6d4);
                    width: 80px;
                    height: 80px;
                    border-radius: 16px;
                    margin: 0 auto 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 32px;
                    color: white;
                }
                h1 { 
                    color: #f1f5f9; 
                    margin-bottom: 10px;
                    font-size: 2.5rem;
                }
                .subtitle {
                    color: #94a3b8;
                    font-size: 1.1rem;
                }
                .form-group { 
                    margin-bottom: 25px; 
                }
                .form-row {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                }
                label { 
                    display: block; 
                    margin-bottom: 8px; 
                    color: #cbd5e1;
                    font-weight: 500;
                }
                input, select, textarea { 
                    width: 100%; 
                    padding: 12px 16px; 
                    border: 1px solid #475569;
                    border-radius: 8px; 
                    background: #1e293b;
                    color: #e2e8f0;
                    font-size: 16px;
                    transition: border-color 0.3s;
                }
                input:focus, select:focus, textarea:focus {
                    outline: none;
                    border-color: #3b82f6;
                    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                }
                .section {
                    background: rgba(15, 23, 42, 0.5);
                    padding: 30px;
                    border-radius: 12px;
                    margin-bottom: 30px;
                    border: 1px solid rgba(71, 85, 105, 0.3);
                }
                .section h3 {
                    color: #f1f5f9;
                    margin-bottom: 20px;
                    font-size: 1.3rem;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .section-icon {
                    width: 24px;
                    height: 24px;
                    background: linear-gradient(135deg, #3b82f6, #06b6d4);
                    border-radius: 6px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 14px;
                }
                .btn { 
                    background: linear-gradient(135deg, #3b82f6, #06b6d4);
                    color: white; 
                    padding: 16px 32px; 
                    border: none; 
                    border-radius: 8px; 
                    cursor: pointer; 
                    font-size: 16px;
                    font-weight: 600;
                    width: 100%;
                    transition: transform 0.2s;
                }
                .btn:hover {
                    transform: translateY(-2px);
                }
                .alert { 
                    padding: 16px; 
                    border-radius: 8px; 
                    margin-bottom: 20px; 
                }
                .alert-success { 
                    background: rgba(34, 197, 94, 0.1); 
                    border: 1px solid rgba(34, 197, 94, 0.3);
                    color: #4ade80; 
                }
                .alert-error { 
                    background: rgba(239, 68, 68, 0.1); 
                    border: 1px solid rgba(239, 68, 68, 0.3);
                    color: #f87171; 
                }
                .help-text {
                    font-size: 14px;
                    color: #94a3b8;
                    margin-top: 5px;
                }
                .requirements {
                    background: rgba(59, 130, 246, 0.1);
                    border: 1px solid rgba(59, 130, 246, 0.3);
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 30px;
                }
                .requirements h4 {
                    color: #60a5fa;
                    margin-bottom: 15px;
                }
                .requirements ul {
                    list-style: none;
                    padding-left: 0;
                }
                .requirements li {
                    padding: 5px 0;
                    color: #cbd5e1;
                }
                .requirements li:before {
                    content: "✓";
                    color: #4ade80;
                    margin-right: 10px;
                    font-weight: bold;
                }
                .crypto-highlight {
                    background: rgba(249, 115, 22, 0.1);
                    border: 1px solid rgba(249, 115, 22, 0.3);
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                }
                .crypto-highlight h4 {
                    color: #fb923c;
                    margin-bottom: 10px;
                }
                .telegram-highlight {
                    background: rgba(34, 197, 94, 0.1);
                    border: 1px solid rgba(34, 197, 94, 0.3);
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                }
                .telegram-highlight h4 {
                    color: #4ade80;
                    margin-bottom: 10px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">🛰️</div>
                    <h1>Starlink Router Rent</h1>
                    <p class="subtitle">Installation & Configuration</p>
                </div>

                <?php if (!empty($this->errors)): ?>
                    <div class="alert alert-error">
                        <strong>Installation Errors:</strong><br>
                        <?php foreach ($this->errors as $error): ?>
                            • <?php echo htmlspecialchars($error); ?><br>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($this->success)): ?>
                    <div class="alert alert-success">
                        <strong>Installation Success:</strong><br>
                        <?php foreach ($this->success as $message): ?>
                            • <?php echo htmlspecialchars($message); ?><br>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($this->success)): ?>
                    <div class="requirements">
                        <h4>System Requirements</h4>
                        <ul>
                            <li>PHP 7.4 or higher</li>
                            <li>MySQL 5.7 or higher</li>
                            <li>PDO MySQL extension</li>
                            <li>OpenSSL extension</li>
                            <li>cURL extension</li>
                            <li>Write permissions for web directory</li>
                        </ul>
                    </div>

                    <div class="telegram-highlight">
                        <h4>🛰️ Starlink Router Rental Platform</h4>
                        <p>Premium router rental platform with daily profits, referral system, and cryptocurrency payments!</p>
                    </div>

                    <div class="crypto-highlight">
                        <h4>🚀 Plisio.net Cryptocurrency Integration</h4>
                        <p>Full cryptocurrency payment support via Plisio.net with 15+ supported coins including Bitcoin, Ethereum, USDT, and more!</p>
                    </div>

                    <form method="POST">
                        <div class="section">
                            <h3><span class="section-icon">🗄️</span>Database Configuration</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="db_host">Database Host</label>
                                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                                    <div class="help-text">Usually 'localhost' for local installations</div>
                                </div>
                                <div class="form-group">
                                    <label for="db_name">Database Name</label>
                                    <input type="text" id="db_name" name="db_name" value="gainsmax_testtelegram" required>
                                    <div class="help-text">Will be created if it doesn't exist</div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="db_username">Database Username</label>
                                    <input type="text" id="db_username" name="db_username" required>
                                </div>
                                <div class="form-group">
                                    <label for="db_password">Database Password</label>
                                    <input type="password" id="db_password" name="db_password">
                                </div>
                            </div>
                        </div>

                        <div class="section">
                            <h3><span class="section-icon">👤</span>Admin Account</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="admin_username">Admin Username</label>
                                    <input type="text" id="admin_username" name="admin_username" value="admin" required>
                                </div>
                                <div class="form-group">
                                    <label for="admin_email">Admin Email</label>
                                    <input type="email" id="admin_email" name="admin_email" value="admin@starlinkrouterrent.com" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="admin_password">Admin Password</label>
                                <input type="password" id="admin_password" name="admin_password" required>
                                <div class="help-text">Minimum 6 characters</div>
                            </div>
                        </div>

                        <div class="section">
                            <h3><span class="section-icon">🌐</span>Site Configuration</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="site_url">Site URL</label>
                                    <input type="url" id="site_url" name="site_url" value="<?php echo 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']); ?>">
                                    <div class="help-text">Full URL where your site will be accessible</div>
                                </div>
                                <div class="form-group">
                                    <label for="timezone">Timezone</label>
                                    <select id="timezone" name="timezone">
                                        <option value="UTC">UTC</option>
                                        <option value="America/New_York">Eastern Time</option>
                                        <option value="America/Chicago">Central Time</option>
                                        <option value="America/Denver">Mountain Time</option>
                                        <option value="America/Los_Angeles">Pacific Time</option>
                                        <option value="Europe/London">London</option>
                                        <option value="Europe/Paris">Paris</option>
                                        <option value="Asia/Tokyo">Tokyo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="section">
                            <h3><span class="section-icon">💳</span>Plisio.net Cryptocurrency Gateway</h3>
                            <div class="form-group">
                                <label for="plisio_api_key">Plisio API Key (Optional)</label>
                                <input type="text" id="plisio_api_key" name="plisio_api_key">
                                <div class="help-text">Get your API key from <a href="https://plisio.net" target="_blank" style="color: #fb923c;">plisio.net</a> - Supports Bitcoin, Ethereum, USDT, and 15+ cryptocurrencies</div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="binance_api_key">Binance API Key (Optional)</label>
                                    <input type="text" id="binance_api_key" name="binance_api_key">
                                </div>
                                <div class="form-group">
                                    <label for="binance_secret">Binance API Secret (Optional)</label>
                                    <input type="password" id="binance_secret" name="binance_secret">
                                </div>
                            </div>
                            <div class="help-text">Payment gateways can be configured later in the admin panel</div>
                        </div>

                        <div class="section">
                            <h3><span class="section-icon">📱</span>Telegram Integration</h3>
                            <div class="form-group">
                                <label for="telegram_bot_token">Telegram Bot Token (Optional)</label>
                                <input type="text" id="telegram_bot_token" name="telegram_bot_token">
                                <div class="help-text">Create a bot with <a href="https://t.me/BotFather" target="_blank" style="color: #60a5fa;">@BotFather</a> to get the token</div>
                            </div>
                        </div>

                        <button type="submit" class="btn">🚀 Install Starlink Router Rent with Crypto Support</button>
                    </form>
                <?php else: ?>
                    <div style="text-align: center; margin-top: 30px;">
                        <a href="/" class="btn" style="display: inline-block; text-decoration: none; max-width: 300px;">
                            🏠 Go to Homepage
                        </a>
                        <br><br>
                        <a href="/admin" class="btn" style="display: inline-block; text-decoration: none; max-width: 300px; background: linear-gradient(135deg, #059669, #10b981);">
                            ⚙️ Access Admin Panel
                        </a>
                        <br><br>
                        <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); padding: 20px; border-radius: 8px; margin-top: 20px;">
                            <h4 style="color: #4ade80; margin-bottom: 10px;">🎉 Installation Complete!</h4>
                            <p>Your Starlink Router Rent platform is now ready with:</p>
                            <ul style="text-align: left; margin: 10px 0; padding-left: 20px;">
                                <li>✅ Premium router rental system</li>
                                <li>✅ Plisio.net cryptocurrency payment integration</li>
                                <li>✅ 3-level referral system with automatic commissions</li>
                                <li>✅ Investment plans with guaranteed returns</li>
                                <li>✅ Professional email notification system</li>
                                <li>✅ Complete admin panel for management</li>
                            </ul>
                            <p style="margin-top: 15px;"><strong>Next steps:</strong> Configure your Telegram bot and Plisio.net API key in the admin panel!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
    }
}

// Run the installer
$installer = new StarlinkRouterRentInstaller();
$installer->install();
?>