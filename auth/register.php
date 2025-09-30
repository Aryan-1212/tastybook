<?php
/**
 * User Registration
 * Handles user registration process
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
        $email = sanitizeInput($_POST['email'] ?? '');
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $agreeTerms = isset($_POST['agree_terms']);
        
        // Validation
        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($firstName)) {
            $errors[] = 'First name is required.';
        }
        
        if (empty($lastName)) {
            $errors[] = 'Last name is required.';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        
        if (!$agreeTerms) {
            $errors[] = 'You must agree to the Terms of Service and Privacy Policy.';
        }
        
        if (empty($errors)) {
            try {
                $db = new Database();
                
                // Check if username already exists
                $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $errors[] = 'Username already exists.';
                }
                
                // Check if email already exists
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $errors[] = 'Email already exists.';
                }
                
                if (empty($errors)) {
                    // Create user
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $db->prepare("
                        INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active, created_at) 
                        VALUES (?, ?, ?, ?, ?, 'user', 1, NOW())
                    ");
                    $stmt->execute([$username, $email, $passwordHash, $firstName, $lastName]);
                    
                    setFlashMessage('success', 'Account created successfully! Please log in.');
                    redirect('/TastyBook/auth/login.php');
                }
                
            } catch (Exception $e) {
                error_log("Registration error: " . $e->getMessage());
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}

$pageTitle = 'Sign Up';
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-form">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-utensils"></i>
                    <span>TastyBook</span>
                </div>
                <h1>Join TastyBook!</h1>
                <p>Create your account and start sharing delicious recipes</p>
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
                <div class="form-row">
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="first_name" name="first_name" class="form-control" 
                                   placeholder="First Name" 
                                   value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="last_name" name="last_name" class="form-control" 
                                   placeholder="Last Name" 
                                   value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-at input-icon"></i>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="Email Address" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="username" name="username" class="form-control" 
                               placeholder="Username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               placeholder="Confirm Password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="agree_terms" value="1" required>
                        <span class="checkmark"></span>
                        I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <span>Create Account</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
        </div>
        
        <div class="auth-image">
            <div class="image-content">
                <h2>Start Your Culinary Journey</h2>
                <p>Join our community and discover amazing recipes from around the world</p>
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-plus-circle"></i>
                        <span>Share Recipes</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-heart"></i>
                        <span>Save Favorites</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-comments"></i>
                        <span>Rate & Review</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = passwordInput.parentElement.querySelector('.password-toggle i');
    
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
    
    // Real-time password matching
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    confirmPassword.addEventListener('input', function() {
        if (this.value !== password.value) {
            this.classList.add('error');
        } else {
            this.classList.remove('error');
        }
    });
    
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
        
        if (password.value !== confirmPassword.value) {
            confirmPassword.classList.add('error');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>