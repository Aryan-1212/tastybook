<?php
/**
 * Reset Password - Verify token and set new password
 */

require_once __DIR__ . '/../includes/header.php';

if (isLoggedIn()) {
    redirect('/TastyBook/index.php');
}

$db = new Database();
$errors = [];
$success = false;

$token = sanitizeInput($_GET['token'] ?? ($_POST['token'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!validatePassword($password)) {
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }
        if (empty($token)) {
            $errors[] = 'Invalid or missing token.';
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare('SELECT pr.id, pr.user_id FROM password_resets pr WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used_at IS NULL');
                $stmt->execute([$token]);
                $row = $stmt->fetch();
                if (!$row) {
                    $errors[] = 'This reset link is invalid or has expired.';
                } else {
                    $hash = hashPassword($password);
                    $db->beginTransaction();
                    $stmt = $db->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
                    $stmt->execute([$hash, $row['user_id']]);
                    $stmt = $db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?');
                    $stmt->execute([$row['id']]);
                    $db->commit();
                    $success = true;
                    setFlashMessage('success', 'Password has been reset. Please sign in.');
                    redirect('/TastyBook/auth/login.php');
                }
            } catch (Exception $e) {
                if (method_exists($db, 'rollback')) { $db->rollback(); }
                error_log('Reset password error: ' . $e->getMessage());
                $errors[] = 'Could not reset password. Please try again later.';
            }
        }
    }
} else {
    // Validate token on GET
    if (!empty($token)) {
        try {
            $stmt = $db->prepare('SELECT 1 FROM password_resets WHERE token = ? AND expires_at > NOW() AND used_at IS NULL');
            $stmt->execute([$token]);
            if (!$stmt->fetchColumn()) {
                $errors[] = 'This reset link is invalid or has expired.';
            }
        } catch (Exception $e) {
            $errors[] = 'Token validation failed.';
        }
    } else {
        $errors[] = 'Missing token.';
    }
}

$pageTitle = 'Reset Password';
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-form">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-utensils"></i>
                    <span>TastyBook</span>
                </div>
                <h1>Set a new password</h1>
                <p>Choose a strong password to secure your account</p>
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

            <form method="POST" class="form">
                <?php echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">'; ?>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" placeholder="New password" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>

            <div class="auth-footer">
                <p><a href="login.php">Back to Sign In</a></p>
            </div>
        </div>
        <div class="auth-image">
            <div class="image-content">
                <h2>Security First</h2>
                <p>Your password is encrypted and stored securely.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


