<?php
/**
 * Add Recipe
 * Handles recipe creation
 */

require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to add a recipe.');
    redirect('/TastyBook/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$errors = [];
$success = false;

// Get categories for dropdown
$db = new Database();
$stmt = $db->prepare("SELECT id, name FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

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
        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imageUrl = uploadFile($_FILES['image']);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        
        // Create recipe if no errors
        if (empty($errors)) {
            try {
                $db = new Database();
                
                $stmt = $db->prepare("INSERT INTO recipes (title, description, ingredients, instructions, prep_time, cook_time, servings, difficulty, category_id, user_id, image_url, tips, tags, is_published, approval_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'pending', NOW())");
                $stmt->execute([
                    $title, $description, $ingredients, $instructions, 
                    $prepTime, $cookTime, $servings, $difficulty, 
                    $categoryId, getCurrentUserId(), $imageUrl, $tips, $tags
                ]);
                
                $recipeId = $db->lastInsertId();
                
                setFlashMessage('success', 'Recipe submitted! Awaiting admin approval.');
                redirect("/TastyBook/my-recipes.php");
                
            } catch (Exception $e) {
                error_log("Recipe creation error: " . $e->getMessage());
                $errors[] = 'Failed to add recipe. Please try again.';
            }
        }
    }
}

$pageTitle = 'Add Recipe';
?>

<section class="add-recipe-section">
    <div class="container">
        <div class="form-container">
            <h1 class="section-title">Share Your Recipe</h1>
            <p class="form-description">Help others discover your delicious creations by sharing your favorite recipes with the TastyBook community.</p>
            
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
                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="prep_time">Prep Time (minutes)</label>
                        <input type="number" id="prep_time" name="prep_time" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['prep_time'] ?? ''); ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label for="cook_time">Cook Time (minutes)</label>
                        <input type="number" id="cook_time" name="cook_time" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['cook_time'] ?? ''); ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label for="servings">Servings</label>
                        <input type="number" id="servings" name="servings" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['servings'] ?? '4'); ?>" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label for="difficulty">Difficulty Level</label>
                    <select id="difficulty" name="difficulty" class="form-control">
                        <option value="easy" <?php echo (($_POST['difficulty'] ?? '') == 'easy') ? 'selected' : ''; ?>>Easy</option>
                        <option value="medium" <?php echo (($_POST['difficulty'] ?? '') == 'medium') ? 'selected' : ''; ?>>Medium</option>
                        <option value="hard" <?php echo (($_POST['difficulty'] ?? '') == 'hard') ? 'selected' : ''; ?>>Hard</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Recipe Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3" 
                              placeholder="Brief description of your recipe..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="ingredients">Ingredients *</label>
                    <textarea id="ingredients" name="ingredients" class="form-control" rows="8" 
                              placeholder="List all ingredients, one per line. For example:&#10;2 cups all-purpose flour&#10;1 cup warm water&#10;1 packet active dry yeast&#10;1 teaspoon salt" required><?php echo htmlspecialchars($_POST['ingredients'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="instructions">Cooking Steps *</label>
                    <textarea id="instructions" name="instructions" class="form-control" rows="10" 
                              placeholder="List all cooking steps, one per line. For example:&#10;1. In a large bowl, combine warm water and yeast&#10;2. Add flour, salt, and olive oil to the mixture&#10;3. Knead the dough for 10 minutes&#10;4. Let it rise for 1 hour" required><?php echo htmlspecialchars($_POST['instructions'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Recipe Image</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    <small class="form-text">Upload a high-quality image of your finished dish (JPG, PNG, max 5MB)</small>
                </div>

                <div class="form-group">
                    <label for="tips">Cooking Tips (Optional)</label>
                    <textarea id="tips" name="tips" class="form-control" rows="3" 
                              placeholder="Share any helpful tips or tricks for making this recipe..."><?php echo htmlspecialchars($_POST['tips'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tags">Tags (Optional)</label>
                    <input type="text" id="tags" name="tags" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>" 
                           placeholder="Enter tags separated by commas (e.g., quick, healthy, family-friendly)">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Recipe
                    </button>
                    <button type="button" class="btn btn-outline" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Reset Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        document.querySelector('.recipe-form').reset();
    }
}

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
