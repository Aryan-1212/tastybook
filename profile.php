<?php
/**
 * User Profile Page
 * Displays and allows editing of user profile
 */

require_once __DIR__ . '/includes/header.php';

// Add profile statistics CSS
echo '<link rel="stylesheet" href="/TastyBook/public/css/profile-statistics.css">';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to view your profile.');
    redirect('/TastyBook/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$db = new Database();
$userId = getCurrentUserId();
$user = getCurrentUser();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $bio = sanitizeInput($_POST['bio'] ?? '');
        
        error_log("Profile update - First Name: " . $firstName);
        error_log("Profile update - Last Name: " . $lastName);
        error_log("Profile update - Bio: " . $bio);
        
        // Validation
        if (empty($firstName)) {
            $errors[] = 'First name is required.';
        }
        
        if (empty($lastName)) {
            $errors[] = 'Last name is required.';
        }
        
        // Handle profile image upload
        $profileImage = $user['profile_image']; // Keep existing image by default
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            try {
                // Delete old image if exists
                if ($profileImage && file_exists('public/uploads/' . $profileImage)) {
                    deleteFile($profileImage);
                }
                $profileImage = uploadFile($_FILES['profile_image']);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        
        // Update profile if no errors
        if (empty($errors)) {
            try {
                $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, bio = ?, profile_image = ?, updated_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$firstName, $lastName, $bio, $profileImage, $userId]);
                
                if ($result && $stmt->rowCount() > 0) {
                    $success = true;
                    setFlashMessage('success', 'Profile updated successfully!');
                    
                    // Refresh user data
                    $user = getCurrentUser();
                } else {
                    $errors[] = 'No changes were made to your profile.';
                }
                
            } catch (Exception $e) {
                error_log("Profile update error: " . $e->getMessage());
                $errors[] = 'Failed to update profile. Please try again.';
            }
        }
    }
}

$pageTitle = 'My Profile';
?>

<section class="profile-section">
    <div class="container">
        <div class="profile-header">
            <h1>My Profile</h1>
            <p>Manage your account information and preferences</p>
        </div>

        <div class="profile-content">
            <div class="profile-form-container">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Profile updated successfully!
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data" class="profile-form">
                    <div class="profile-image-section">
                        <div class="current-image">
                            <?php if ($user['profile_image']): ?>
                                 <img src="/TastyBook/public/uploads/<?php echo htmlspecialchars($user['profile_image']); ?>"
                                     alt="Profile Image" id="profile-preview">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/150x150?text=<?php echo substr($user['first_name'], 0, 1); ?>" 
                                     alt="Profile Image" id="profile-preview">
                            <?php endif; ?>
                        </div>
                        <div class="image-upload">
                            <label for="profile_image" class="btn btn-outline">
                                <i class="fas fa-camera"></i> Change Photo
                            </label>
                            <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" class="form-control" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="form-text">Username cannot be changed</small>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <small class="form-text">Email cannot be changed</small>
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" class="form-control" rows="4" 
                                  placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                        <a href="dashboard.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>

            <div class="profile-stats">
                <h3>Account Statistics</h3>
                <div class="stats-grid">
                    <?php
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
                    ?>
                    
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $recipeCount; ?></div>
                        <div class="stat-label">Recipes Shared</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $favoriteCount; ?></div>
                        <div class="stat-label">Favorites</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $reviewCount; ?></div>
                        <div class="stat-label">Reviews Given</div>
                    </div>
                </div>
                <div class="account-statistics">
                    <h2 class="statistics-title">
                        <i class="fas fa-chart-bar"></i>
                        ACCOUNT STATISTICS
                    </h2>
                    <div class="points-badge">
                        <?php $badge = getBadgeForPoints($user['points_total'] ?? 0); ?>
                        <div class="points-content">
                            <div class="points-total">
                                <span class="number"><?php echo (int)($user['points_total'] ?? 0); ?></span>
                                <span class="label">TOTAL POINTS</span>
                            </div>
                            <div class="badge-info">
                                <div class="current-badge">
                                    <i class="fas fa-medal"></i>
                                    <span><?php echo htmlspecialchars($badge); ?></span>
                                </div>
                                <div class="redeem-link">
                                    <a href="redeem.php" class="btn btn-primary">
                                        <i class="fas fa-gift"></i>
                                        Redeem Rewards
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Profile image preview
document.getElementById('profile_image').addEventListener('change', function(e) {
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
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
