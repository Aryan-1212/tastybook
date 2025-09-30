<?php
/**
 * Edit Recipe
 * Handles recipe editing
 */

require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to edit recipes.');
    redirect('auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$recipeId = (int)($_GET['id'] ?? ($_POST['recipe_id'] ?? 0));

if ($recipeId <= 0) {
    setFlashMessage('error', 'Invalid recipe ID.');
    redirect('my-recipes.php');
}

$db = new Database();

// If admin actions: handle approve/reject/top and exit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdmin() && isset($_POST['action']) && in_array($_POST['action'], ['admin_approve','admin_reject','admin_top','admin_good'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.');
        redirect('/TastyBook/admin.php');
    }
    $action = $_POST['action'];
    try {
        if ($action === 'admin_approve') {
            $stmt = $db->prepare("UPDATE recipes SET approval_status = 'approved', is_published = 1, approved_at = NOW(), approved_by = ? WHERE id = ?");
            $stmt->execute([getCurrentUserId(), $recipeId]);
            // Award +10 to owner for approval
            $stmt = $db->prepare("SELECT user_id FROM recipes WHERE id = ?");
            $stmt->execute([$recipeId]);
            $ownerId = (int)$stmt->fetchColumn();
            if ($ownerId) {
                awardPoints($ownerId, 10, 'recipe_approved', $recipeId);
                maybeAwardFiveDayStreak($ownerId);
            }
            setFlashMessage('success', 'Recipe approved.');
        } elseif ($action === 'admin_reject') {
            $stmt = $db->prepare("UPDATE recipes SET approval_status = 'rejected', is_published = 0 WHERE id = ?");
            $stmt->execute([$recipeId]);
            setFlashMessage('info', 'Recipe rejected.');
        } elseif ($action === 'admin_top') {
            $stmt = $db->prepare("UPDATE recipes SET is_top_of_week = 1, top_of_week_at = NOW() WHERE id = ?");
            $stmt->execute([$recipeId]);
            // Award +50 to owner
            $stmt = $db->prepare("SELECT user_id FROM recipes WHERE id = ?");
            $stmt->execute([$recipeId]);
            $ownerId = (int)$stmt->fetchColumn();
            if ($ownerId) {
                awardPoints($ownerId, 50, 'top_of_week', $recipeId);
            }
            setFlashMessage('success', 'Marked as Top Recipe of the Week.');
        } elseif ($action === 'admin_good') {
            $stmt = $db->prepare("UPDATE recipes SET quality = 'good' WHERE id = ?");
            $stmt->execute([$recipeId]);
            setFlashMessage('success', 'Marked recipe as Good.');
        }
    } catch (Exception $e) {
        error_log('Admin recipe action: ' . $e->getMessage());
        setFlashMessage('error', 'Failed to update recipe.');
    }
    redirect('/TastyBook/admin.php');
}

// Get recipe details (owner only)
$stmt = $db->prepare("SELECT * FROM recipes WHERE id = ? AND user_id = ?");
$stmt->execute([$recipeId, getCurrentUserId()]);
$recipe = $stmt->fetch();

if (!$recipe) {
    setFlashMessage('error', 'Recipe not found or you do not have permission to edit it.');
    redirect('my-recipes.php');
}

// Get categories for dropdown
$stmt = $db->prepare("SELECT id, name FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Get form data
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $ingredients = sanitizeInput($_POST['ingredients'] ?? '');
        $instructions = sanitizeInput($_POST['instructions'] ?? '');
        $prepTime = (int)($_POST['prep_time'] ?? 0);
        $cookTime = (int)($_POST['cook_time'] ?? 0);
        $servings = (int)($_POST['servings'] ?? 1);
        $difficulty = sanitizeInput($_POST['difficulty'] ?? 'easy');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $tips = sanitizeInput($_POST['tips'] ?? '');
        $tags = sanitizeInput($_POST['tags'] ?? '');
        
        // Validation
        if (empty($title)) {
            $errors[] = 'Recipe title is required.';
        } elseif (strlen($title) < 3) {
            $errors[] = 'Recipe title must be at least 3 characters long.';
        }
        
        if (empty($ingredients)) {
            $errors[] = 'Ingredients are required.';
        } elseif (count(array_filter(explode("\n", $ingredients))) < 2) {
            $errors[] = 'Please provide at least 2 ingredients.';
        }
        
        if (empty($instructions)) {
            $errors[] = 'Cooking instructions are required.';
        } elseif (count(array_filter(explode("\n", $instructions))) < 2) {
            $errors[] = 'Please provide at least 2 cooking steps.';
        }
        
        if ($categoryId <= 0) {
            $errors[] = 'Please select a category.';
        }
        
        if ($servings < 1) {
            $errors[] = 'Servings must be at least 1.';
        }
        
        if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
            $errors[] = 'Invalid difficulty level.';
        }
        
        // Handle image upload
        $imageUrl = $recipe['image_url']; // Keep existing image by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                // Delete old image if exists
                if ($imageUrl && file_exists('public/uploads/' . $imageUrl)) {
                    deleteFile($imageUrl);
                }
                $imageUrl = uploadFile($_FILES['image']);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        
        // Update recipe if no errors
        if (empty($errors)) {
            try {
                $stmt = $db->prepare("UPDATE recipes SET title = ?, description = ?, ingredients = ?, instructions = ?, prep_time = ?, cook_time = ?, servings = ?, difficulty = ?, category_id = ?, image_url = ?, tips = ?, tags = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([
                    $title, $description, $ingredients, $instructions, 
                    $prepTime, $cookTime, $servings, $difficulty, 
                    $categoryId, $imageUrl, $tips, $tags, $recipeId
                ]);
                
                setFlashMessage('success', 'Recipe updated successfully!');
                redirect("recipe-details.php?id={$recipeId}");
                
            } catch (Exception $e) {
                error_log("Recipe update error: " . $e->getMessage());
                $errors[] = 'Failed to update recipe. Please try again.';
            }
        }
    }
}

$pageTitle = 'Edit Recipe - ' . $recipe['title'];
?>

<section class="add-recipe-section">
    <div class="container">
        <div class="form-container">
            <h1 class="section-title">Edit Recipe</h1>
            <p class="form-description">Update your recipe details and share the changes with the community.</p>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data" class="recipe-form">
                <div class="form-group">
                    <label for="title">Recipe Title *</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?php echo htmlspecialchars($recipe['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $recipe['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="prep_time">Prep Time (minutes)</label>
                        <input type="number" id="prep_time" name="prep_time" class="form-control" 
                               value="<?php echo $recipe['prep_time']; ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label for="cook_time">Cook Time (minutes)</label>
                        <input type="number" id="cook_time" name="cook_time" class="form-control" 
                               value="<?php echo $recipe['cook_time']; ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label for="servings">Servings</label>
                        <input type="number" id="servings" name="servings" class="form-control" 
                               value="<?php echo $recipe['servings']; ?>" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label for="difficulty">Difficulty Level</label>
                    <select id="difficulty" name="difficulty" class="form-control">
                        <option value="easy" <?php echo $recipe['difficulty'] == 'easy' ? 'selected' : ''; ?>>Easy</option>
                        <option value="medium" <?php echo $recipe['difficulty'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="hard" <?php echo $recipe['difficulty'] == 'hard' ? 'selected' : ''; ?>>Hard</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Recipe Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3" 
                              placeholder="Brief description of your recipe..."><?php echo htmlspecialchars($recipe['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="ingredients">Ingredients *</label>
                    <textarea id="ingredients" name="ingredients" class="form-control" rows="8" 
                              placeholder="List all ingredients, one per line. For example:&#10;2 cups all-purpose flour&#10;1 cup warm water&#10;1 packet active dry yeast&#10;1 teaspoon salt" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="instructions">Cooking Steps *</label>
                    <textarea id="instructions" name="instructions" class="form-control" rows="10" 
                              placeholder="List all cooking steps, one per line. For example:&#10;1. In a large bowl, combine warm water and yeast&#10;2. Add flour, salt, and olive oil to the mixture&#10;3. Knead the dough for 10 minutes&#10;4. Let it rise for 1 hour" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Recipe Image</label>
                    <?php if ($recipe['image_url']): ?>
                        <div class="current-image">
                            <p>Current image:</p>
                            <img src="/TastyBook/recipes/public/uploads/<?php echo htmlspecialchars($recipe['image_url']); ?>" 
                                 alt="Current recipe image" style="max-width: 200px; height: auto; border-radius: 5px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    <small class="form-text">Upload a new image to replace the current one (JPG, PNG, max 5MB)</small>
                </div>

                <div class="form-group">
                    <label for="tips">Cooking Tips (Optional)</label>
                    <textarea id="tips" name="tips" class="form-control" rows="3" 
                              placeholder="Share any helpful tips or tricks for making this recipe..."><?php echo htmlspecialchars($recipe['tips']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tags">Tags (Optional)</label>
                    <input type="text" id="tags" name="tags" class="form-control" 
                           value="<?php echo htmlspecialchars($recipe['tags']); ?>" 
                           placeholder="Enter tags separated by commas (e.g., quick, healthy, family-friendly)">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Recipe
                    </button>
                    <a href="recipe-details.php?id=<?php echo $recipeId; ?>" class="btn btn-outline">
                        <i class="fas fa-eye"></i> View Recipe
                    </a>
                    <a href="my-recipes.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to My Recipes
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
// File upload preview
const imageInput = document.getElementById('image');
if (imageInput) {
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (5MB limit)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                this.value = '';
                return;
            }
            
            // Check file type
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                this.value = '';
                return;
            }
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
