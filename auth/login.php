<?php
/**
 * User Login
 * Handles user login process
 */

require_once __DIR__ . '/../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/TastyBook/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($username)) {
            $errors[] = 'Username or email is required.';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required.';
        }
        
        if (empty($errors)) {
            try {
                $db = new Database();
                
                // Check if login is username or email
                $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
                $field = $isEmail ? 'email' : 'username';
                
                $stmt = $db->prepare("SELECT id, username, email, password_hash, first_name, last_name, is_active, role FROM users WHERE {$field} = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user && $user['is_active'] && verifyPassword($password, $user['password_hash'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['role'] = $user['role'] ?? 'user';
                    
                    // Set remember me cookie if requested
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true); // 30 days
                    }
                    
                    setFlashMessage('success', 'Welcome back, ' . $user['first_name'] . '!');
                    
                    // Redirect to intended page or dashboard
                    $redirect = $_GET['redirect'] ?? '/TastyBook/index.php';
                    // If redirect is relative, make it absolute
                    if (!str_starts_with($redirect, '/')) {
                        $redirect = '/TastyBook/' . $redirect;
                    }
                    redirect($redirect);
                    
                } else {
                    $errors[] = 'Invalid username/email or password.';
                }
                
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $errors[] = 'Login failed. Please try again.';
            }
        }
    }
}

$pageTitle = 'Login';
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-form">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-utensils"></i>
                    <span>TastyBook</span>
                </div>
                <h1>Welcome Back!</h1>
                <p>Sign in to continue your culinary journey</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="form">
                <?php echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">'; ?>
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="username" name="username" class="form-control" 
                               placeholder="Username or Email" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        <span class="checkmark"></span>
                        Remember me for 30 days
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Create one here</a></p>
                <p><a href="forgot-password.php">Forgot your password?</a></p>
            </div>
        </div>
        
        <div class="auth-image">
            <div class="image-content">
                <h2>Discover Amazing Recipes</h2>
                <p>Join thousands of food lovers sharing their favorite recipes</p>
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-book"></i>
                        <span>1000+ Recipes</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-users"></i>
                        <span>Active Community</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-star"></i>
                        <span>Top Rated</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.querySelector('.password-toggle i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Add form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.form');
    const inputs = form.querySelectorAll('.form-control');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
        
        input.addEventListener('input', function() {
            this.classList.remove('error');
        });
    });
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        inputs.forEach(input => {
            if (input.value.trim() === '') {
                input.classList.add('error');
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>