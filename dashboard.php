<?php
/**
 * User Dashboard
 * Displays user's recipes and favorites
 */

require_once __DIR__ . '/includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to access the dashboard.');
    redirect('/TastyBook/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$db = new Database();
$userId = getCurrentUserId();

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
    LIMIT 10
");
$stmt->execute([$userId]);
$userRecipes = $stmt->fetchAll();

// Get user's favorite recipes
$stmt = $db->prepare("
    SELECT r.*, c.name as category_name, u.first_name, u.last_name,
           AVG(rev.rating) as avg_rating, COUNT(rev.id) as review_count
    FROM favorites f
    JOIN recipes r ON f.recipe_id = r.id
    JOIN categories c ON r.category_id = c.id
    JOIN users u ON r.user_id = u.id
    LEFT JOIN reviews rev ON r.id = rev.recipe_id
    WHERE f.user_id = ? AND r.is_published = 1
    GROUP BY r.id
    ORDER BY f.created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$favoriteRecipes = $stmt->fetchAll();

// Get user stats
$stmt = $db->prepare("SELECT COUNT(*) as recipe_count FROM recipes WHERE user_id = ?");
$stmt->execute([$userId]);
$recipeCount = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) as favorite_count FROM favorites WHERE user_id = ?");
$stmt->execute([$userId]);
$favoriteCount = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) as review_count FROM reviews WHERE user_id = ?");
$stmt->execute([$userId]);
$reviewCount = $stmt->fetchColumn();

$pageTitle = 'Dashboard';
?>

<section class="dashboard">
    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars(getCurrentUser()['first_name']); ?>!</h1>
            <p>Manage your recipes and discover new favorites</p>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $recipeCount; ?></h3>
                    <p>My Recipes</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $favoriteCount; ?></h3>
                    <p>Favorites</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $reviewCount; ?></h3>
                    <p>Reviews Given</p>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>My Recent Recipes</h2>
                    <a href="/TastyBook/recipes/add-recipe.php" class="btn btn-primary">
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
                                    
                                    <div class="recipe-actions">
                                        <a href="recipes/recipe-details.php?id=<?php echo $recipe['id']; ?>" class="btn btn-secondary">View</a>
                                        <a href="recipes/edit-recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-outline">Edit</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center">
                        <a href="my-recipes.php" class="btn btn-outline">View All My Recipes</a>
                    </div>
                <?php else: ?>
                    <div class="no-content">
                        <i class="fas fa-book"></i>
                        <h3>No recipes yet</h3>
                        <p>Start sharing your delicious recipes with the community!</p>
                        <a href="/TastyBook/recipes/add-recipe.php" class="btn btn-primary">Add Your First Recipe</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>My Favorites</h2>
                    <a href="favorites.php" class="btn btn-outline">View All Favorites</a>
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
                                    
                                    <div class="recipe-author">
                                        <span>By <?php echo htmlspecialchars($recipe['first_name'] . ' ' . $recipe['last_name']); ?></span>
                                    </div>
                                    
                                    <a href="recipes/recipe-details.php?id=<?php echo $recipe['id']; ?>" class="btn btn-secondary">View Recipe</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-content">
                        <i class="fas fa-heart"></i>
                        <h3>No favorites yet</h3>
                        <p>Start exploring recipes and add them to your favorites!</p>
                        <a href="recipes/recipes.php" class="btn btn-primary">Browse Recipes</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
