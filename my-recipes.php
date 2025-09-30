<?php
/**
 * My Recipes
 * Displays user's own recipes
 */

require_once __DIR__ . '/includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to view your recipes.');
    redirect('auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$db = new Database();
$userId = getCurrentUserId();

// Get pagination parameters
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get total count
$stmt = $db->prepare("SELECT COUNT(*) FROM recipes WHERE user_id = ?");
$stmt->execute([$userId]);
$totalRecipes = $stmt->fetchColumn();
$totalPages = ceil($totalRecipes / $perPage);

// Get user's recipes
$stmt = $db->prepare("
    SELECT r.*, c.name as category_name,
           AVG(rev.rating) as avg_rating, COUNT(rev.id) as review_count
    FROM recipes r 
    JOIN categories c ON r.category_id = c.id 
    LEFT JOIN reviews rev ON r.id = rev.recipe_id
    WHERE r.user_id = ?
    GROUP BY r.id
    ORDER BY r.created_at DESC
    LIMIT {$perPage} OFFSET {$offset}
");
$stmt->execute([$userId]);
$userRecipes = $stmt->fetchAll();

$pageTitle = 'My Recipes';
?>

<section class="my-recipes-section">
    <div class="container">
        <div class="section-header">
            <h1>My Recipes</h1>
            <p>Manage your shared recipes</p>
            <a href="recipes/add-recipe.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Recipe
            </a>
        </div>

        <?php if (!empty($userRecipes)): ?>
            <div class="recipes-grid">
                <?php foreach ($userRecipes as $recipe): ?>
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
                            
                            <div class="recipe-status">
                                <?php if ($recipe['approval_status'] === 'pending'): ?>
                                    <span class="status-badge draft">Pending Approval</span>
                                <?php elseif ($recipe['approval_status'] === 'rejected'): ?>
                                    <span class="status-badge draft">Rejected</span>
                                <?php else: ?>
                                    <span class="status-badge published">Published</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="recipe-actions">
                                <a href="recipes/recipe-details.php?id=<?php echo $recipe['id']; ?>" class="btn btn-secondary">View</a>
                                <a href="recipes/edit-recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-outline">Edit</a>
                                <button class="btn btn-danger delete-recipe" data-recipe-id="<?php echo $recipe['id']; ?>" data-recipe-title="<?php echo htmlspecialchars($recipe['title']); ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination-container">
                    <?php echo generatePagination($page, $totalPages, 'my-recipes.php'); ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-content">
                <i class="fas fa-book"></i>
                <h3>No recipes yet</h3>
                <p>Start sharing your delicious recipes with the community!</p>
                <a href="recipes/add-recipe.php" class="btn btn-primary">Add Your First Recipe</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Delete recipe functionality
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-recipe');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const recipeId = this.dataset.recipeId;
            const recipeTitle = this.dataset.recipeTitle;
            
            if (confirm(`Are you sure you want to delete "${recipeTitle}"? This action cannot be undone.`)) {
                fetch('recipes/delete-recipe.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `recipe_id=${recipeId}&csrf_token=${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closest('.recipe-card').remove();
                        
                        // Show message
                        const message = document.createElement('div');
                        message.className = 'alert alert-success';
                        message.innerHTML = data.message;
                        document.body.insertBefore(message, document.body.firstChild);
                        
                        setTimeout(() => message.remove(), 3000);
                        
                        // Check if no more recipes
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
