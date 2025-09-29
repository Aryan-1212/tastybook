<?php
/**
 * 404 Error Page
 */

require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Page Not Found';
?>

<section class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1>Page Not Found</h1>
            <p>The page you're looking for doesn't exist or has been moved.</p>
            <div class="error-actions">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Go Home
                </a>
                <a href="recipes/recipes.php" class="btn btn-outline">
                    <i class="fas fa-book"></i> Browse Recipes
                </a>
            </div>
        </div>
    </div>
</section>

<style>
.error-page {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.error-content {
    text-align: center;
    max-width: 600px;
    padding: 2rem;
}

.error-code {
    font-size: 8rem;
    font-weight: bold;
    color: rgba(255, 255, 255, 0.3);
    margin-bottom: 1rem;
    line-height: 1;
}

.error-content h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: white;
}

.error-content p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.error-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.error-actions .btn {
    min-width: 150px;
}

@media (max-width: 768px) {
    .error-code {
        font-size: 6rem;
    }
    
    .error-content h1 {
        font-size: 2rem;
    }
    
    .error-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .error-actions .btn {
        width: 100%;
        max-width: 200px;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
