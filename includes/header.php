<?php
/**
 * Header Template
 * Common header for all pages
 */

require_once __DIR__ . '/../db/database.php';
require_once __DIR__ . '/functions.php';

// Start session
startSecureSession();

// Get current user
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link rel="stylesheet" href="/TastyBook/public/css/style.css">
    <link rel="stylesheet" href="/TastyBook/public/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v7.0.1/css/all.css" crossorigin="anonymous">
    <link rel="preconnect" href="https://use.fontawesome.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-utensils"></i>
                <span>TastyBook</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="/TastyBook/index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a href="/TastyBook/recipes/recipes.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'recipes.php' || basename(dirname($_SERVER['PHP_SELF'])) == 'recipes') ? 'active' : ''; ?>">Recipes</a>
                </li>
                <?php if (isLoggedIn()): ?>
                <li class="nav-item">
                    <a href="/TastyBook/recipes/add-recipe.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'add-recipe.php') ? 'active' : ''; ?>">Add Recipe</a>
                </li>
                <li class="nav-item">
                    <a href="/TastyBook/dashboard.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="/TastyBook/about.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : ''; ?>">About</a>
                </li>
                <li class="nav-item">
                    <a href="/TastyBook/contact.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>">Contact</a>
                </li>
            </ul>
            
            <div class="nav-auth">
                <?php if (isLoggedIn()): ?>
                    <div class="user-menu">
                        <div class="user-info">
                             <img src="<?php echo $currentUser['profile_image'] ? '/TastyBook/public/uploads/' . $currentUser['profile_image'] : 'https://via.placeholder.com/40x40?text=' . substr($currentUser['first_name'], 0, 1); ?>"
                                 alt="<?php echo htmlspecialchars($currentUser['first_name']); ?>" class="user-avatar">
                            <span class="user-name"><?php echo htmlspecialchars($currentUser['first_name']); ?></span>
                        </div>
                        <div class="user-dropdown">
                            <a href="/TastyBook/profile.php"><i class="fas fa-user"></i> Profile</a>
                            <a href="/TastyBook/my-recipes.php"><i class="fas fa-book"></i> My Recipes</a>
                            <a href="/TastyBook/favorites.php"><i class="fas fa-heart"></i> Favorites</a>
                            <?php if ($currentUser['username'] === 'admin'): ?>
                            <a href="/TastyBook/admin.php"><i class="fas fa-cog"></i> Admin Panel</a>
                            <?php endif; ?>
                            <a href="/TastyBook/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="/TastyBook/auth/login.php" class="btn btn-outline">Login</a>
                        <a href="/TastyBook/auth/register.php" class="btn btn-primary">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <?php
    // Display flash messages
    $successMessage = getFlashMessage('success');
    $errorMessage = getFlashMessage('error');
    $warningMessage = getFlashMessage('warning');
    $infoMessage = getFlashMessage('info');
    
    if ($successMessage): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($successMessage); ?>
            <button type="button" class="alert-close">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($errorMessage); ?>
            <button type="button" class="alert-close">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if ($warningMessage): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($warningMessage); ?>
            <button type="button" class="alert-close">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if ($infoMessage): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <?php echo htmlspecialchars($infoMessage); ?>
            <button type="button" class="alert-close">&times;</button>
        </div>
    <?php endif; ?>
