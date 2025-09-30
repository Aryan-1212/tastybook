<?php
/**
 * Footer Template
 * Common footer for all pages
 */
?>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-utensils"></i>
                        <span>TastyBook</span>
                    </div>
                    <p>Your ultimate destination for discovering and sharing delicious recipes from around the world.</p>
                    <div class="social-links">
                        <a href="#"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#"><i class="fa-brands fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="/TastyBook/index.php">Home</a></li>
                        <li><a href="/TastyBook/recipes/recipes.php">Recipes</a></li>
                        <?php if (isLoggedIn()): ?>
                        <li><a href="/TastyBook/recipes/add-recipe.php">Add Recipe</a></li>
                        <li><a href="/TastyBook/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="/TastyBook/about.php">About</a></li>
                        <li><a href="/TastyBook/contact.php">Contact</a></li>
                        <li><a href="/TastyBook/leaderboard.php">Leaderboard</a></li>
                        <!-- <li><a href="/TastyBook/sitemap.php">Sitemap</a></li> -->
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Categories</h3>
                    <ul>
                        <li><a href="/TastyBook/recipes/recipes.php?category=breakfast">Breakfast</a></li>
                        <li><a href="/TastyBook/recipes/recipes.php?category=lunch">Lunch</a></li>
                        <li><a href="/TastyBook/recipes/recipes.php?category=dinner">Dinner</a></li>
                        <li><a href="/TastyBook/recipes/recipes.php?category=vegan">Vegan</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-envelope"></i> aryanparvani12@gmail.com</p>
                    <p><i class="fas fa-phone"></i> +91 6353953587</p>
                    <p><i class="fas fa-map-marker-alt"></i> C-104, Saanvi Nirman, Estella, VIP Road</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> TastyBook. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <button id="scrollToTop" class="scroll-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="/TastyBook/public/js/main.js"></script>
    <script>
        // Close alert messages
        document.addEventListener('DOMContentLoaded', function() {
            const alertCloseButtons = document.querySelectorAll('.alert-close');
            alertCloseButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.parentElement.style.display = 'none';
                });
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 5000);
            });
        });
        
        // CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Add CSRF token to all forms
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                if (!form.querySelector('input[name="csrf_token"]')) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = 'csrf_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                }
            });
        });
    </script>
</body>
</html>
