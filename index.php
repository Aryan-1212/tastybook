<?php
/**
 * Homepage
 * Displays project information and featured recipes
 */

require_once __DIR__ . '/includes/header.php';

$db = new Database();

// Get featured recipes (most recent 6) - only if database exists
$featuredRecipes = [];
try {
    $stmt = $db->prepare("
        SELECT r.*, c.name as category_name, u.first_name, u.last_name,
               AVG(rev.rating) as avg_rating, COUNT(rev.id) as review_count,
               r.is_featured, r.is_good, r.is_best
        FROM recipes r 
        JOIN categories c ON r.category_id = c.id 
        JOIN users u ON r.user_id = u.id 
        LEFT JOIN reviews rev ON r.id = rev.recipe_id
            WHERE r.approval_status = 'approved' AND r.is_featured = 1
        GROUP BY r.id
            ORDER BY r.is_best DESC, r.is_good DESC, r.created_at DESC
        LIMIT 6
    ");
    
    // Add CSS for recipe cards and ribbons
    echo '
    <style>
    .recipe-card {
        position: relative;
        overflow: hidden;
    }
    
        .status-badges {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            flex-direction: column;
            gap: 5px;
            z-index: 2;
        }
    
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    
        .status-badge i {
            font-size: 14px;
        }
    
        .status-badge.best {
            background: #4299e1;
            color: white;
        }
    
        .status-badge.good {
            background: #48bb78;
            color: white;
        }
    
        .status-badge.featured {
            background: #ecc94b;
            color: black;
        }
    
        .ribbon {
        position: absolute;
        top: 15px;
        right: -30px;
        transform: rotate(45deg);
        background: #ffd700;
        padding: 5px 40px;
        color: #000;
        font-weight: bold;
        z-index: 2;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .recipe-card:hover .status-badges {
        opacity: 1;
    }
    
    .status-badges {
        position: absolute;
        top: 10px;
        left: 10px;
        display: flex;
        flex-direction: column;
        gap: 5px;
        z-index: 2;
    }
    
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .status-badge.best {
        background: #4299e1;
        color: white;
    }
    
    .status-badge.good {
        background: #48bb78;
        color: white;
    }
    
    .status-badge.featured {
        background: #ecc94b;
        color: black;
    }
    </style>
    ';
    $stmt->execute();
    $featuredRecipes = $stmt->fetchAll();
} catch (Exception $e) {
    // Database not set up yet
    $featuredRecipes = [];
}

$pageTitle = 'Home';
?>

<section class="hero">
    <div class="hero-container">
        <div class="hero-content">
            <h1 class="hero-title">TastyBook</h1>
            <h2 class="hero-subtitle">Your Online Recipe Library</h2>
            <p class="hero-description">
                Discover, share, and create delicious recipes from around the world. 
                Join our community of food lovers and explore thousands of mouthwatering dishes.
            </p>
            <div class="hero-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="recipes/recipes.php" class="btn btn-primary">Browse Recipes</a>
                    <a href="dashboard.php" class="btn btn-outline">My Dashboard</a>
                <?php else: ?>
                    <a href="auth/register.php" class="btn btn-primary">Get Started</a>
                    <a href="auth/login.php" class="btn btn-outline">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Delicious Food">
        </div>
    </div>
</section>

<!-- Project Information Section -->
<section class="project-info">
    <div class="container">
        <div class="info-grid">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3>Recipe Management</h3>
                <p>Create, edit, and manage your favorite recipes with detailed ingredients and step-by-step instructions.</p>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Smart Search</h3>
                <p>Find recipes by name, ingredients, or category with our advanced search and filtering system.</p>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Favorites & Reviews</h3>
                <p>Save your favorite recipes and rate others to help the community discover the best dishes.</p>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-user"></i>
                </div>
                <h3>Community</h3>
                <p>Join a vibrant community of food lovers sharing their culinary creations and experiences.</p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <h2 class="section-title">Why Choose TastyBook?</h2>
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-content">
                    <h3>Easy Recipe Sharing</h3>
                    <p>Share your culinary creations with the world. Upload photos, write detailed instructions, and help others recreate your dishes.</p>
                    <ul>
                        <li>Step-by-step instructions</li>
                        <li>Ingredient lists with measurements</li>
                        <li>Cooking tips and techniques</li>
                        <li>Difficulty levels and timing</li>
                    </ul>
                </div>
                <div class="feature-image">
                    <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Recipe Sharing">
                </div>
            </div>
            
            <div class="feature-item">
                <div class="feature-image">
                    <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?q=80&w=781&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Search & Discover">
                </div>
                <div class="feature-content">
                    <h3>Discover New Flavors</h3>
                    <p>Explore thousands of recipes from around the world. Use our smart search to find exactly what you're looking for.</p>
                    <ul>
                        <li>Search by ingredients or cuisine</li>
                        <li>Filter by difficulty and time</li>
                        <li>Browse by categories</li>
                        <li>Sort by ratings and popularity</li>
                    </ul>
                </div>
            </div>
            
            <div class="feature-item">
                <div class="feature-content">
                    <h3>Personal Recipe Collection</h3>
                    <p>Build your personal recipe library. Save favorites, create collections, and never lose a great recipe again.</p>
                    <ul>
                        <li>Save recipes to favorites</li>
                        <li>Rate and review dishes</li>
                        <li>Personal dashboard</li>
                        <li>Recipe statistics</li>
                    </ul>
                </div>
                <div class="feature-image">
                    <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Personal Collection">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="featured-recipes">
    <div class="container">
        <h2 class="section-title">Featured Recipes</h2>
        <div class="recipes-grid">
            <?php if (!empty($featuredRecipes)): ?>
                <?php foreach ($featuredRecipes as $recipe): ?>
                    <div class="recipe-card">
                        <div class="recipe-image">
                                <div class="status-badges">
                                <?php if ($recipe['is_best']): ?>
                                <div class="status-badge best">
                                    <i class="fas fa-crown"></i> Best Recipe
                                </div>
                                <?php endif; ?>
                                <?php if ($recipe['is_good']): ?>
                                <div class="status-badge good">
                                    <i class="fas fa-thumbs-up"></i> Good Recipe
                                </div>
                                <?php endif; ?>
                                <?php if ($recipe['is_featured']): ?>
                                <div class="status-badge featured">
                                    <i class="fas fa-star"></i> Featured
                                </div>
                            <?php endif; ?>
                                </div>
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
                                    <span class="rating-text"><?php echo number_format($recipe['avg_rating'], 1); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($recipe['quality']) && $recipe['quality'] === 'good'): ?>
                                <div class="status-badge" style="background:#e8f5e8;color:#2e7d32;display:inline-block;margin:.5rem 0;">Good</div>
                            <?php endif; ?>
                            
                            <a href="recipes/recipe-details.php?id=<?php echo $recipe['id']; ?>" class="btn btn-secondary">View Recipe</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-recipes">
                    <div class="no-recipes-content">
                        <i class="fas fa-book"></i>
                        <h3>No recipes yet</h3>
                        <p>Be the first to share a delicious recipe with the community!</p>
                        <?php if (isLoggedIn()): ?>
                            <a href="recipes/add-recipe.php" class="btn btn-primary">Add Your First Recipe</a>
                        <?php else: ?>
                            <a href="auth/register.php" class="btn btn-primary">Join Now to Add Recipes</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center">
            <a href="recipes/recipes.php" class="btn btn-outline">View All Recipes</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
