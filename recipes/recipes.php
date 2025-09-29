<?php
/**
 * Recipes Listing
 * Displays all recipes with search and filter functionality
 */

require_once __DIR__ . '/../includes/header.php';

$db = new Database();

// Get search and filter parameters
$search = sanitizeInput($_GET['search'] ?? '');
$category = sanitizeInput($_GET['category'] ?? '');
$difficulty = sanitizeInput($_GET['difficulty'] ?? '');
$sort = sanitizeInput($_GET['sort'] ?? 'newest');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

// Build query
$whereConditions = ['r.is_published = 1'];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(r.title LIKE ? OR r.description LIKE ? OR r.ingredients LIKE ? OR r.instructions LIKE ? OR r.tags LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($category)) {
    $whereConditions[] = "c.slug = ?";
    $params[] = $category;
}

if (!empty($difficulty)) {
    $whereConditions[] = "r.difficulty = ?";
    $params[] = $difficulty;
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count for pagination
$countQuery = "SELECT COUNT(*) FROM recipes r JOIN categories c ON r.category_id = c.id WHERE {$whereClause}";
$stmt = $db->prepare($countQuery);
$stmt->execute($params);
$totalRecipes = $stmt->fetchColumn();
$totalPages = ceil($totalRecipes / $perPage);

// Get recipes
$offset = ($page - 1) * $perPage;

$orderBy = match($sort) {
    'oldest' => 'r.created_at ASC',
    'title' => 'r.title ASC',
    'rating' => 'avg_rating DESC',
    default => 'r.created_at DESC'
};

$query = "
    SELECT r.*, c.name as category_name, c.slug as category_slug, u.username, u.first_name, u.last_name,
           AVG(rev.rating) as avg_rating, COUNT(rev.id) as review_count
    FROM recipes r 
    JOIN categories c ON r.category_id = c.id 
    JOIN users u ON r.user_id = u.id 
    LEFT JOIN reviews rev ON r.id = rev.recipe_id
    WHERE {$whereClause}
    GROUP BY r.id
    ORDER BY {$orderBy}
    LIMIT {$perPage} OFFSET {$offset}
";

$stmt = $db->prepare($query);
$stmt->execute($params);
$recipes = $stmt->fetchAll();

// Get categories for filter
$stmt = $db->prepare("SELECT name, slug FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

$pageTitle = 'Recipes';
?>

<section class="search-section">
    <div class="container">
        <div class="search-container">
            <h1 class="section-title">Discover Delicious Recipes</h1>
            <form method="GET" class="search-form">
                <div class="search-box">
                    <input type="text" name="search" class="search-input" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search recipes by name or ingredient...">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
                
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="category">Category:</label>
                        <select name="category" id="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['slug']; ?>" 
                                        <?php echo $category === $cat['slug'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="difficulty">Difficulty:</label>
                        <select name="difficulty" id="difficulty" class="form-control">
                            <option value="">All Levels</option>
                            <option value="easy" <?php echo $difficulty === 'easy' ? 'selected' : ''; ?>>Easy</option>
                            <option value="medium" <?php echo $difficulty === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="hard" <?php echo $difficulty === 'hard' ? 'selected' : ''; ?>>Hard</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sort">Sort by:</label>
                        <select name="sort" id="sort" class="form-control">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title A-Z</option>
                            <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-outline">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="featured-recipes">
    <div class="container">
        <?php if (!empty($recipes)): ?>
            <div class="recipes-grid">
                <?php foreach ($recipes as $recipe): ?>
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
                            
                            <a href="recipe-details.php?id=<?php echo $recipe['id']; ?>" class="btn btn-secondary">View Recipe</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination-container">
                    <?php echo generatePagination($page, $totalPages, 'recipes/recipes.php?' . http_build_query(array_filter([
                        'search' => $search,
                        'category' => $category,
                        'difficulty' => $difficulty,
                        'sort' => $sort
                    ]))); ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-content">
                    <i class="fas fa-search"></i>
                    <h3>No recipes found</h3>
                    <p>Try adjusting your search criteria or browse all recipes.</p>
                    <a href="recipes.php" class="btn btn-primary">Browse All Recipes</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
