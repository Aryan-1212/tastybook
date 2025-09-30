<?php
/**
 * Forgot Password - Request Reset
 */

require_once __DIR__ . '/../includes/header.php';

if (isLoggedIn()) {
    redirect('/TastyBook/index.php');
}

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        if (!validateEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (empty($errors)) {
            try {
                $db = new Database();
                $stmt = $db->prepare('SELECT id, first_name FROM users WHERE email = ? AND is_active = 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    // expire in 1 hour
                    $stmt = $db->prepare('INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())');
                    $stmt->execute([$user['id'], $token]);
                    $resetLink = APP_URL . '/auth/reset-password.php?token=' . urlencode($token);
                    // In lieu of email, show an on-screen notice for local dev
                    $successMessage = 'If an account exists, a reset link has been generated. For development: ' . htmlspecialchars($resetLink);
                } else {
                    $successMessage = 'If an account exists, a reset link has been sent.';
                }
            } catch (Exception $e) {
                error_log('Forgot password error: ' . $e->getMessage());
                $errors[] = 'Failed to process request. Please try again later.';
            }
        }
    }
}

$pageTitle = 'Forgot Password';
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-form">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-utensils"></i>
                    <span>TastyBook</span>
                </div>
                <h1>Forgot your password?</h1>
                <p>Enter your email and we'll send you a reset link</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form">
                <?php echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">'; ?>
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-at input-icon"></i>
                        <input type="email" name="email" class="form-control" placeholder="Your email address" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send reset link</button>
            </form>

            <div class="auth-footer">
                <p><a href="login.php">Back to Sign In</a></p>
            </div>
        </div>
        <div class="auth-image">
            <div class="image-content">
                <h2>Reset Access</h2>
                <p>We keep accounts secure with time-limited reset links.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


