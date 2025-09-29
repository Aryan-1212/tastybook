
<?php
/**
 * Recipe Details
 * Displays individual recipe details
 */

// --- FAVORITE TOGGLE HANDLER: must be before any output ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_favorite') {
    require_once __DIR__ . '/../includes/functions.php';
    require_once __DIR__ . '/../db/database.php';
    startSecureSession();
    header('Content-Type: application/json');
    $db = new Database();
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to favorite recipes.']);
        exit;
    }
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Invalid request. Please try again.']);
        exit;
    }
    try {
        $userId   = getCurrentUserId();
        $recipeId = (int) ($_POST['recipe_id'] ?? 0);
        if ($recipeId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid recipe.']);
            exit;
        }
        // Check if already favorited
        $stmt = $db->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$userId, $recipeId]);
        $isFavorited = $stmt->fetchColumn();
        if ($isFavorited) {
            // Remove from favorites
            $stmt = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
            $stmt->execute([$userId, $recipeId]);
            echo json_encode([
                'success'   => true,
                'favorited' => false,
                'message'   => 'Removed from favorites.'
            ]);
            exit;
        } else {
            // Add to favorites
            $stmt = $db->prepare("INSERT INTO favorites (user_id, recipe_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $recipeId]);
            echo json_encode([
                'success'   => true,
                'favorited' => true,
                'message'   => 'Added to favorites.'
            ]);
            exit;
        }
    } catch (Exception $e) {
        error_log("Favorite toggle error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update favorites.']);
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';

$recipeId = (int)($_GET['id'] ?? 0);

if ($recipeId <= 0) {
    setFlashMessage('error', 'Recipe not found.');
    redirect('/TastyBook/recipes/recipes.php');
}

$db = new Database();

// Get recipe details
$stmt = $db->prepare("
    SELECT r.*, c.name as category_name, u.username, u.first_name, u.last_name 
    FROM recipes r 
    JOIN categories c ON r.category_id = c.id 
    JOIN users u ON r.user_id = u.id 
    WHERE r.id = ? AND r.is_published = 1
");
$stmt->execute([$recipeId]);
$recipe = $stmt->fetch();

if (!$recipe) {
    setFlashMessage('error', 'Recipe not found.');
    redirect('/TastyBook/recipes/recipes.php');
}

// Get reviews for this recipe
$stmt = $db->prepare("
    SELECT r.*, u.first_name, u.last_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.recipe_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 10
");
$stmt->execute([$recipeId]);
$reviews = $stmt->fetchAll();

// Get average rating
$stmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE recipe_id = ?");
$stmt->execute([$recipeId]);
$ratingData = $stmt->fetch();

// Check if current user has favorited this recipe
$isFavorited = false;
if (isLoggedIn()) {
    $stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $stmt->execute([getCurrentUserId(), $recipeId]);
    $isFavorited = $stmt->fetch() !== false;
}

// Check if current user can edit this recipe
$canEdit = isLoggedIn() && getCurrentUserId() == $recipe['user_id'];

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_review') {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'You must be logged in to add a review.');
    } else {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Invalid request. Please try again.');
        } else {
            $rating = (int)($_POST['rating'] ?? 0);
            $comment = sanitizeInput($_POST['comment'] ?? '');
            
            if ($rating < 1 || $rating > 5) {
                setFlashMessage('error', 'Please select a valid rating.');
            } elseif (empty($comment)) {
                setFlashMessage('error', 'Please write a review comment.');
            } else {
                try {
                    // Check if user already reviewed this recipe
                    $stmt = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND recipe_id = ?");
                    $stmt->execute([getCurrentUserId(), $recipeId]);
                    
                    if ($stmt->fetch()) {
                        // Update existing review
                        $stmt = $db->prepare("UPDATE reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE user_id = ? AND recipe_id = ?");
                        $stmt->execute([$rating, $comment, getCurrentUserId(), $recipeId]);
                        setFlashMessage('success', 'Review updated successfully!');
                    } else {
                        // Add new review
                        $stmt = $db->prepare("INSERT INTO reviews (user_id, recipe_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt->execute([getCurrentUserId(), $recipeId, $rating, $comment]);
                        setFlashMessage('success', 'Review added successfully!');
                    }
                    
                    redirect("recipe-details.php?id={$recipeId}");
                    
                } catch (Exception $e) {
                    error_log("Review submission error: " . $e->getMessage());
                    setFlashMessage('error', 'Failed to submit review. Please try again.');
                }
            }
        }
    }
}

// Handle favorite toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_favorite') {
    header('Content-Type: application/json'); // Ensure JSON response

    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to favorite recipes.']);
        exit;
    }

    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Invalid request. Please try again.']);
        exit;
    }

    try {
        $userId   = getCurrentUserId();
        $recipeId = (int) ($_POST['recipe_id'] ?? 0);

        if ($recipeId <= 0) {
            sendJSONResponse(['success' => false, 'message' => 'Invalid recipe.']);
        }

        // Check if already favorited
        $stmt = $db->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$userId, $recipeId]);
        $isFavorited = $stmt->fetchColumn();

        if ($isFavorited) {
            // Remove from favorites
            $stmt = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
            $stmt->execute([$userId, $recipeId]);
            echo json_encode([
                'success'   => true,
                'favorited' => false,
                'message'   => 'Removed from favorites.'
            ]);
            exit;
        } else {
            // Add to favorites
            $stmt = $db->prepare("INSERT INTO favorites (user_id, recipe_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $recipeId]);
            echo json_encode([
                'success'   => true,
                'favorited' => true,
                'message'   => 'Added to favorites.'
            ]);
            exit;
        }
    } catch (Exception $e) {
        error_log("Favorite toggle error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update favorites.']);
        exit;
    }
}

$pageTitle = $recipe['title'];
?>

<section class="recipe-details">
    <div class="container">
        <div class="recipe-header">
            <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
            
            <?php if ($recipe['image_url']): ?>
                <img src="/TastyBook/recipes/public/uploads/<?php echo htmlspecialchars($recipe['image_url']); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
            <?php else: ?>
                <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?q=80&w=781&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
            <?php endif; ?>
            
            <div class="recipe-meta">
                <span class="recipe-category"><?php echo htmlspecialchars($recipe['category_name']); ?></span>
                <span class="recipe-time">
                    <i class="fas fa-clock"></i> 
                    <?php 
                    $totalTime = $recipe['prep_time'] + $recipe['cook_time'];
                    echo $totalTime > 0 ? $totalTime . ' minutes' : 'Time not specified';
                    ?>
                </span>
                <span class="recipe-servings">
                    <i class="fas fa-users"></i> 
                    <?php echo $recipe['servings']; ?> servings
                </span>
                <span class="recipe-difficulty">
                    <i class="fas fa-star"></i> 
                    <?php echo ucfirst($recipe['difficulty']); ?>
                </span>
            </div>
            
            <div class="recipe-actions">
                <?php if (isLoggedIn()): ?>
                    <button id="favoriteBtn" class="btn btn-outline <?php echo $isFavorited ? 'favorited' : ''; ?>" 
                            data-recipe-id="<?php echo $recipeId; ?>">
                        <i class="fas fa-heart"></i>
                        <?php echo $isFavorited ? 'Favorited' : 'Add to Favorites'; ?>
                    </button>
                <?php endif; ?>
                
                <?php if ($canEdit): ?>
                    <a href="edit-recipe.php?id=<?php echo $recipeId; ?>" class="btn btn-outline">
                        <i class="fas fa-edit"></i> Edit Recipe
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($recipe['description']): ?>
            <div class="recipe-description">
                <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
            </div>
        <?php endif; ?>

        <div class="recipe-info">
            <div class="ingredients-section">
                <h2>Ingredients</h2>
                <ul class="ingredients-list">
                    <?php 
                    $ingredients = array_filter(explode("\n", $recipe['ingredients']));
                    foreach ($ingredients as $ingredient): 
                    ?>
                        <li><?php echo htmlspecialchars(trim($ingredient)); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="instructions-section">
                <h2>Instructions</h2>
                <ol class="instructions-list">
                    <?php 
                    $instructions = array_filter(explode("\n", $recipe['instructions']));
                    foreach ($instructions as $instruction): 
                    ?>
                        <li><?php echo nl2br(htmlspecialchars(trim($instruction))); ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>

        <?php if ($recipe['tips']): ?>
            <div class="recipe-tips">
                <h2>Cooking Tips</h2>
                <div class="tips-content">
                    <p><?php echo nl2br(htmlspecialchars($recipe['tips'])); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="ratings-section">
            <h2>Ratings & Reviews</h2>
            
            <?php if ($ratingData['total_reviews'] > 0): ?>
                <div class="rating-summary">
                    <div class="average-rating">
                        <span class="rating-number"><?php echo number_format($ratingData['avg_rating'], 1); ?></span>
                        <div class="stars">
                            <?php 
                            $avgRating = round($ratingData['avg_rating']);
                            for ($i = 1; $i <= 5; $i++): 
                            ?>
                                <i class="fas fa-star<?php echo $i <= $avgRating ? '' : '-o'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="total-reviews">Based on <?php echo $ratingData['total_reviews']; ?> review<?php echo $ratingData['total_reviews'] != 1 ? 's' : ''; ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="rating-summary">
                    <p>No reviews yet. Be the first to review this recipe!</p>
                </div>
            <?php endif; ?>

            <?php if (isLoggedIn()): ?>
                <div class="add-review">
                    <h3>Add Your Review</h3>
                    <form class="review-form" method="POST">
                        <input type="hidden" name="action" value="add_review">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="rating-input" style="text-align:center; margin-bottom:1rem;">
                            <div id="custom-star-rating" class="custom-star-rating" style="font-size:2.2rem; display:inline-block;">
                                <span data-value="1" class="star" tabindex="0" aria-label="1 star" style="cursor:pointer;">&#9733;</span>
                                <span data-value="2" class="star" tabindex="0" aria-label="2 stars" style="cursor:pointer;">&#9733;</span>
                                <span data-value="3" class="star" tabindex="0" aria-label="3 stars" style="cursor:pointer;">&#9733;</span>
                                <span data-value="4" class="star" tabindex="0" aria-label="4 stars" style="cursor:pointer;">&#9733;</span>
                                <span data-value="5" class="star" tabindex="0" aria-label="5 stars" style="cursor:pointer;">&#9733;</span>
                            </div>
                            <input type="hidden" name="rating" id="rating-input" value="0" required>
                        </div>
                        <div class="form-group">
                            <label for="reviewComment">Your Review:</label>
                            <textarea id="reviewComment" name="comment" class="form-control" rows="4" 
                                      placeholder="Share your experience with this recipe..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (!empty($reviews)): ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <h4><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h4>
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <span class="review-date"><?php echo timeAgo($review['created_at']); ?></span>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center" style="margin-top: 3rem;">
            <a href="/TastyBook/recipes/recipes.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Recipes
            </a>
        </div>
    </div>
</section>

<script>

// Pure HTML/CSS/JS interactive star rating (Google Maps style)
document.addEventListener('DOMContentLoaded', function() {
    const starContainer = document.getElementById('custom-star-rating');
    const stars = starContainer ? starContainer.querySelectorAll('.star') : [];
    const ratingInput = document.getElementById('rating-input');
    let selected = parseInt(ratingInput.value) || 0;

    function setStars(rating) {
        stars.forEach((star, idx) => {
            if (idx < rating) {
                star.style.color = '#ffc107';
            } else {
                star.style.color = '#888';
            }
        });
    }

    setStars(selected);

    stars.forEach((star, idx) => {
        star.addEventListener('mouseover', function() {
            setStars(idx + 1);
        });
        star.addEventListener('focus', function() {
            setStars(idx + 1);
        });
        star.addEventListener('mouseout', function() {
            setStars(selected);
        });
        star.addEventListener('blur', function() {
            setStars(selected);
        });
        star.addEventListener('click', function() {
            selected = idx + 1;
            ratingInput.value = selected;
            setStars(selected);
        });
        star.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                selected = idx + 1;
                ratingInput.value = selected;
                setStars(selected);
                e.preventDefault();
            }
        });
    });
});

// Favorite button functionality
const favoriteBtn = document.getElementById('favoriteBtn');
if (favoriteBtn) {
    favoriteBtn.addEventListener('click', function() {
        const recipeId = this.dataset.recipeId;
        fetch('recipe-details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'same-origin',
            body: `action=toggle_favorite&recipe_id=${recipeId}&csrf_token=${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.classList.toggle('favorited');
                this.innerHTML = data.favorited ? 
                    '<i class="fas fa-heart"></i> Favorited' : 
                    '<i class="fas fa-heart"></i> Add to Favorites';
                
                // Show message
                const message = document.createElement('div');
                message.className = 'alert alert-success';
                message.innerHTML = data.message;
                document.body.insertBefore(message, document.body.firstChild);
                
                setTimeout(() => message.remove(), 3000);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
