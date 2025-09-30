<?php
/**
 * User Favorites
 * Displays user's favorite recipes
 */

require_once __DIR__ . '/includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to view favorites.');
    redirect('auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$db = new Database();
$userId = getCurrentUserId();

// Get pagination parameters
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get total count
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM favorites f
    JOIN recipes r ON f.recipe_id = r.id
    WHERE f.user_id = ? AND (r.approval_status = 'approved' OR (r.approval_status IS NULL AND r.is_published = 1))
");
$stmt->execute([$userId]);
$totalFavorites = $stmt->fetchColumn();
$totalPages = ceil($totalFavorites / $perPage);

// Get favorite recipes
$stmt = $db->prepare("
    SELECT r.*, c.name as category_name, u.first_name, u.last_name,
           AVG(rev.rating) as avg_rating, COUNT(rev.id) as review_count
    FROM favorites f
    JOIN recipes r ON f.recipe_id = r.id
    JOIN categories c ON r.category_id = c.id
    JOIN users u ON r.user_id = u.id
    LEFT JOIN reviews rev ON r.id = rev.recipe_id
    WHERE f.user_id = ? AND (r.approval_status = 'approved' OR (r.approval_status IS NULL AND r.is_published = 1))
    GROUP BY r.id
    ORDER BY f.created_at DESC
    LIMIT {$perPage} OFFSET {$offset}
");
$stmt->execute([$userId]);
$favoriteRecipes = $stmt->fetchAll();

$pageTitle = 'My Favorites';
?>

<section class="favorites-section">
    <div class="container">
        <div class="section-header">
            <h1>My Favorite Recipes</h1>
            <p>Recipes you've saved to your personal collection</p>
        </div>

        <?php if (!empty($favoriteRecipes)): ?>
            <div class="recipes-grid">
                <?php foreach ($favoriteRecipes as $recipe): ?>
                    <div class="recipe-card">
                        <div class="recipe-image">
                            <?php if ($recipe['image_url']): ?>
                                <img src="/TastyBook/recipes/public/uploads/<?php echo htmlspecialchars($recipe['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" 
                                     alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="recipe-content">
                            <h3 class="recipe-title"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                            <p class="recipe-description">
                                <?php echo htmlspecialchars(substr($recipe['description'] ?: $recipe['instructions'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="recipe-meta">
                                <span class="recipe-category"><?php echo htmlspecialchars($recipe['category_name']); ?></span>
                                <span class="recipe-time">
                                    <i class="fas fa-clock"></i> 
                                    <?php 
                                    $totalTime = $recipe['prep_time'] + $recipe['cook_time'];
                                    echo $totalTime > 0 ? $totalTime . ' min' : 'Time not specified';
                                    ?>
                                </span>
                            </div>
                            
                            <?php if ($recipe['avg_rating']): ?>
                                <div class="recipe-rating">
                                    <div class="stars">
                                        <?php 
                                        $rating = round($recipe['avg_rating']);
                                        for ($i = 1; $i <= 5; $i++): 
                                        ?>
                                            <i class="fas fa-star<?php echo $i <= $rating ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-text"><?php echo number_format($recipe['avg_rating'], 1); ?> (<?php echo $recipe['review_count']; ?>)</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="recipe-author">
                                <span>By <?php echo htmlspecialchars($recipe['first_name'] . ' ' . $recipe['last_name']); ?></span>
                            </div>
                            
                            <div class="recipe-actions">
                                <a href="recipes/recipe-details.php?id=<?php echo $recipe['id']; ?>" class="btn btn-secondary">View Recipe</a>
                                <button class="btn btn-outline remove-favorite" data-recipe-id="<?php echo $recipe['id']; ?>">
                                    <i class="fas fa-heart-broken"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination-container">
                    <?php echo generatePagination($page, $totalPages, 'favorites.php'); ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-content">
                <i class="fas fa-heart"></i>
                <h3>No favorites yet</h3>
                <p>Start exploring recipes and add them to your favorites by clicking the heart icon!</p>
                <a href="recipes/recipes.php" class="btn btn-primary">Browse Recipes</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Remove from favorites functionality
document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.remove-favorite');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const recipeId = this.dataset.recipeId;
            const recipeCard = this.closest('.recipe-card');
            
            if (confirm('Are you sure you want to remove this recipe from your favorites?')) {
                fetch('recipes/recipe-details.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=toggle_favorite&recipe_id=${recipeId}&csrf_token=${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        recipeCard.remove();
                        
                        // Show message
                        const message = document.createElement('div');
                        message.className = 'alert alert-success';
                        message.innerHTML = data.message;
                        document.body.insertBefore(message, document.body.firstChild);
                        
                        setTimeout(() => message.remove(), 3000);
                        
                        // Check if no more favorites
                        const remainingCards = document.querySelectorAll('.recipe-card');
                        if (remainingCards.length === 0) {
                            location.reload();
                        }
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
