<?php
/**
 * About Page
 * Displays information about TastyBook
 */

require_once __DIR__ . '/includes/header.php';

$pageTitle = 'About';
?>

<section class="about-hero">
    <div class="container">
        <div class="about-content">
            <h1>About TastyBook</h1>
            <p class="about-description">
                TastyBook is your ultimate destination for discovering, sharing, and creating delicious recipes from around the world. 
                We believe that food brings people together, and every recipe tells a story.
            </p>
        </div>
    </div>
</section>

<section class="mission-section">
    <div class="container">
        <div class="mission-content">
            <div class="mission-text">
                <h2>Our Mission</h2>
                <p>
                    At TastyBook, we're passionate about connecting food lovers worldwide through the joy of cooking. 
                    Our mission is to create a vibrant community where home cooks, professional chefs, and food enthusiasts 
                    can share their culinary creations and discover new flavors from every corner of the globe.
                </p>
                <p>
                    Whether you're a seasoned chef or just starting your culinary journey, TastyBook provides the tools, 
                    inspiration, and community support you need to explore the wonderful world of cooking.
                </p>
            </div>
            <div class="mission-image">
                <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Cooking Together">
            </div>
        </div>
    </div>
</section>

<section class="how-it-works">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Discover</h3>
                <p>Browse through thousands of recipes from our community. Use our search and filter tools to find exactly what you're looking for.</p>
            </div>
            <div class="step-card">
                <div class="step-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Save & Rate</h3>
                <p>Save your favorite recipes to your personal collection and rate recipes to help others discover the best dishes.</p>
            </div>
            <div class="step-card">
                <div class="step-icon">
                    <i class="fa-solid fa-share"></i>
                </div>
                <h3>Share</h3>
                <p>Share your own recipes with the community. Upload photos, write detailed instructions, and help others recreate your dishes.</p>
            </div>
            <div class="step-card">
                <div class="step-icon">
                    <i class="fa-brands fa-connectdevelop"></i>
                </div>
                <h3>Connect</h3>
                <p>Join our community of food lovers. Comment on recipes, ask questions, and connect with fellow cooking enthusiasts.</p>
            </div>
        </div>
    </div>
</section>

<section class="team-section">
    <div class="container">
        <h2 class="section-title">Meet Our Team</h2>
        <div class="team-grid">
            <div class="team-member">
                <div class="member-image-div">
                    <div class="member-image">
                        <img src="/TastyBook/public/images/240283116010.jpg" alt="Aryan Parvani">
                    </div>
                </div>
                <div class="member-info">
                    <h3>Aryan Parvani</h3>
                    <p class="member-role">Founder & CEO</p>
                    <p class="member-bio">Passionate foodie and tech entrepreneur who believes in the power of community-driven platforms.</p>
                    <div class="member-social">
                        <a href="#"><i class="fa-brands fa-linkedin"></i></a>
                        <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">10,000+</div>
                <div class="stat-label">Recipes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">50,000+</div>
                <div class="stat-label">Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">1M+</div>
                <div class="stat-label">Reviews</div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
