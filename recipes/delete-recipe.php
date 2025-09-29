<?php
/**
 * Delete Recipe
 * Handles recipe deletion
 */

require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    sendJSONResponse(['success' => false, 'message' => 'You must be logged in to delete recipes.']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Invalid request method.']);
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    sendJSONResponse(['success' => false, 'message' => 'Invalid request. Please try again.']);
}

$recipeId = (int)($_POST['recipe_id'] ?? 0);

if ($recipeId <= 0) {
    sendJSONResponse(['success' => false, 'message' => 'Invalid recipe ID.']);
}

try {
    $db = new Database();
    
    // Check if recipe exists and belongs to current user
    $stmt = $db->prepare("SELECT id, title, image_url, user_id FROM recipes WHERE id = ?");
    $stmt->execute([$recipeId]);
    $recipe = $stmt->fetch();
    
    if (!$recipe) {
        sendJSONResponse(['success' => false, 'message' => 'Recipe not found.']);
    }
    
    // Check if user owns this recipe
    if ($recipe['user_id'] != getCurrentUserId()) {
        sendJSONResponse(['success' => false, 'message' => 'You can only delete your own recipes.']);
    }
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Delete related data first
        $db->prepare("DELETE FROM favorites WHERE recipe_id = ?")->execute([$recipeId]);
        $db->prepare("DELETE FROM reviews WHERE recipe_id = ?")->execute([$recipeId]);
        $db->prepare("DELETE FROM recipe_views WHERE recipe_id = ?")->execute([$recipeId]);
        
        // Delete the recipe
        $db->prepare("DELETE FROM recipes WHERE id = ?")->execute([$recipeId]);
        
        // Delete image file if exists
        if ($recipe['image_url'] && file_exists('public/uploads/' . $recipe['image_url'])) {
            deleteFile($recipe['image_url']);
        }
        
        $db->commit();
        
        sendJSONResponse([
            'success' => true, 
            'message' => 'Recipe "' . $recipe['title'] . '" deleted successfully.'
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Recipe deletion error: " . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Failed to delete recipe. Please try again.']);
}
?>
