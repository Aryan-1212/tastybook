<?php
/**
 * Sitemap
 * Shows all available pages and features
 */

require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Sitemap';
?>

<section class="sitemap-section">
    <div class="container">
        <div class="sitemap-header">
            <h1>Site Map</h1>
            <p>Explore all the features and pages available on TastyBook</p>
        </div>

        <div class="sitemap-content">
            <div class="sitemap-category">
                <h2>Public Pages</h2>
                <div class="sitemap-links">
                    <a href="index.php" class="sitemap-link">
                        <i class="fas fa-home"></i>
                        <div>
                            <h3>Home</h3>
                            <p>Project overview and featured recipes</p>
                        </div>
                    </a>
                    <a href="recipes.php" class="sitemap-link">
                        <i class="fas fa-book"></i>
                        <div>
                            <h3>Browse Recipes</h3>
                            <p>Search and discover recipes from the community</p>
                        </div>
                    </a>
                    <a href="about.php" class="sitemap-link">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <h3>About</h3>
                            <p>Learn more about TastyBook and our mission</p>
                        </div>
                    </a>
                    <a href="contact.php" class="sitemap-link">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h3>Contact</h3>
                            <p>Get in touch with our team</p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="sitemap-category">
                <h2>Authentication</h2>
                <div class="sitemap-links">
                    <a href="auth/login.php" class="sitemap-link">
                        <i class="fas fa-sign-in-alt"></i>
                        <div>
                            <h3>Login</h3>
                            <p>Sign in to your account</p>
                        </div>
                    </a>
                    <a href="auth/register.php" class="sitemap-link">
                        <i class="fas fa-user-plus"></i>
                        <div>
                            <h3>Register</h3>
                            <p>Create a new account</p>
                        </div>
                    </a>
                </div>
            </div>

            <?php if (isLoggedIn()): ?>
            <div class="sitemap-category">
                <h2>User Features</h2>
                <div class="sitemap-links">
                    <a href="dashboard.php" class="sitemap-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <div>
                            <h3>Dashboard</h3>
                            <p>Your personal recipe management center</p>
                        </div>
                    </a>
                    <a href="profile.php" class="sitemap-link">
                        <i class="fas fa-user"></i>
                        <div>
                            <h3>Profile</h3>
                            <p>Manage your account information</p>
                        </div>
                    </a>
                    <a href="my-recipes.php" class="sitemap-link">
                        <i class="fas fa-book-open"></i>
                        <div>
                            <h3>My Recipes</h3>
                            <p>View and manage your shared recipes</p>
                        </div>
                    </a>
                    <a href="favorites.php" class="sitemap-link">
                        <i class="fas fa-heart"></i>
                        <div>
                            <h3>Favorites</h3>
                            <p>Your saved favorite recipes</p>
                        </div>
                    </a>
                    <a href="recipes/add-recipe.php" class="sitemap-link">
                        <i class="fas fa-plus"></i>
                        <div>
                            <h3>Add Recipe</h3>
                            <p>Share a new recipe with the community</p>
                        </div>
                    </a>
                </div>
            </div>

            <?php if (getCurrentUser()['username'] === 'admin'): ?>
            <div class="sitemap-category">
                <h2>Admin Features</h2>
                <div class="sitemap-links">
                    <a href="admin.php" class="sitemap-link">
                        <i class="fas fa-cog"></i>
                        <div>
                            <h3>Admin Panel</h3>
                            <p>Manage users, recipes, and site statistics</p>
                        </div>
                    </a>
                    <a href="setup.php" class="sitemap-link">
                        <i class="fas fa-database"></i>
                        <div>
                            <h3>Database Setup</h3>
                            <p>Initialize or reset the database</p>
                        </div>
                    </a>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <div class="sitemap-category">
                <h2>Features Overview</h2>
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-search"></i>
                        <div>
                            <h4>Advanced Search</h4>
                            <p>Search recipes by name, ingredients, or category with smart filtering</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-star"></i>
                        <div>
                            <h4>Rating & Reviews</h4>
                            <p>Rate and review recipes to help the community discover great dishes</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-heart"></i>
                        <div>
                            <h4>Favorites System</h4>
                            <p>Save your favorite recipes to your personal collection</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-image"></i>
                        <div>
                            <h4>Image Uploads</h4>
                            <p>Upload high-quality photos of your delicious creations</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-user-group"></i>
                        <div>
                            <h4>Community</h4>
                            <p>Join a vibrant community of food lovers and cooking enthusiasts</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-mobile-alt"></i>
                        <div>
                            <h4>Responsive Design</h4>
                            <p>Access TastyBook from any device with our mobile-friendly interface</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.sitemap-section {
    padding: 2rem 0;
    min-height: 80vh;
}

.sitemap-header {
    text-align: center;
    margin-bottom: 3rem;
}

.sitemap-header h1 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
}

.sitemap-content {
    display: flex;
    flex-direction: column;
    gap: 3rem;
}

.sitemap-category h2 {
    color: #333;
    margin-bottom: 1.5rem;
    font-size: 1.8rem;
    border-bottom: 2px solid #667eea;
    padding-bottom: 0.5rem;
}

.sitemap-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.sitemap-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: #333;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.sitemap-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
}

.sitemap-link i {
    font-size: 1.5rem;
    color: #667eea;
    width: 30px;
    text-align: center;
}

.sitemap-link h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
    font-size: 1.2rem;
}

.sitemap-link p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
}

.features-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #667eea;
}

.feature-item i {
    font-size: 1.5rem;
    color: #667eea;
    margin-top: 0.25rem;
}

.feature-item h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
    font-size: 1.1rem;
}

.feature-item p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
}

@media (max-width: 768px) {
    .sitemap-links {
        grid-template-columns: 1fr;
    }
    
    .features-list {
        grid-template-columns: 1fr;
    }
    
    .sitemap-link {
        flex-direction: column;
        text-align: center;
    }
    
    .feature-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
