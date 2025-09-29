<?php
/**
 * Contact Page
 * Handles contact form submissions
 */

require_once __DIR__ . '/includes/header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($message)) {
            $errors[] = 'Message is required.';
        }
        
        if (empty($errors)) {
            try {
                $db = new Database();
                $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $email, $subject, $message]);
                
                $success = true;
                setFlashMessage('success', 'Thank you for your message! We\'ll get back to you soon.');
                
            } catch (Exception $e) {
                error_log("Contact form error: " . $e->getMessage());
                $errors[] = 'Failed to send message. Please try again.';
            }
        }
    }
}

$pageTitle = 'Contact';
?>

<section class="contact-hero">
    <div class="container">
        <div class="contact-content">
            <h1>Get in Touch</h1>
            <p class="contact-description">
                Have questions, suggestions, or just want to say hello? We'd love to hear from you! 
                Our team is here to help and always excited to connect with fellow food lovers.
            </p>
        </div>
    </div>
</section>

<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-form-container">
                <h2>Send us a Message</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Thank you for your message! We'll get back to you soon.
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
                
                <form method="POST" action="" class="contact-form">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <select id="subject" name="subject" class="form-control">
                            <option value="">Select a subject</option>
                            <option value="general" <?php echo (($_POST['subject'] ?? '') === 'general') ? 'selected' : ''; ?>>General Inquiry</option>
                            <option value="support" <?php echo (($_POST['subject'] ?? '') === 'support') ? 'selected' : ''; ?>>Technical Support</option>
                            <option value="feedback" <?php echo (($_POST['subject'] ?? '') === 'feedback') ? 'selected' : ''; ?>>Feedback & Suggestions</option>
                            <option value="partnership" <?php echo (($_POST['subject'] ?? '') === 'partnership') ? 'selected' : ''; ?>>Partnership Opportunities</option>
                            <option value="bug" <?php echo (($_POST['subject'] ?? '') === 'bug') ? 'selected' : ''; ?>>Report a Bug</option>
                            <option value="other" <?php echo (($_POST['subject'] ?? '') === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" class="form-control" rows="6" 
                                  placeholder="Tell us what's on your mind..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Info -->
            <div class="contact-info">
                <h2>Contact Information</h2>
                <div class="info-items">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h3>Email</h3>
                            <p>aryanparvani12@gmail.com</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h3>Phone</h3>
                            <p>6353953587</p>
                            <p>Mon-Fri: 9AM - 6PM EST</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h3>Address</h3>
                            <p>C-104, Saanvi Nirman</p>
                            <p>Ahmedabad, 380058</p>
                            <p>India</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h3>Business Hours</h3>
                            <p>Monday - Friday: 9AM - 6PM</p>
                            <p>Saturday: 10AM - 4PM</p>
                            <p>Sunday: Closed</p>
                        </div>
                    </div>
                </div>

                <div class="social-contact">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="#" class="member-link">
                            <i class="fa-brands fa-linkedin"></i>
                            <span>Instagram</span>
                        </a>
                        <a href="#" class="member-link">
                            <i class="fa-brands fa-instagram"></i>
                            <span>Pinterest</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="faq-section">
    <div class="container">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <div class="faq-grid">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>How do I add a recipe?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Simply click on "Add Recipe" in the navigation menu, fill out the form with your recipe details, and submit. Your recipe will be published immediately!</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Can I edit my recipes after posting?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes! You can edit your recipes at any time through your dashboard. Changes will be updated immediately.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>How do I report inappropriate content?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>You can report inappropriate content by clicking the "Report" button on any recipe or comment. Our moderation team will review it promptly.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Is TastyBook free to use?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes! TastyBook is completely free to use. You can browse, search, save, and share recipes without any cost.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>How do I create an account?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>You can create an account by clicking the "Sign Up" button in the top navigation. You'll need to provide your email and create a password.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Can I download recipes?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Currently, you can save recipes to your account, but direct downloads are not available. We're working on adding this feature soon!</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// FAQ Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            const faqItem = question.parentElement;
            const answer = faqItem.querySelector('.faq-answer');
            const icon = question.querySelector('i');
            
            // Close other open FAQs
            document.querySelectorAll('.faq-item').forEach(item => {
                if (item !== faqItem) {
                    item.classList.remove('active');
                    item.querySelector('.faq-answer').style.maxHeight = null;
                    item.querySelector('i').classList.remove('fa-chevron-up');
                    item.querySelector('i').classList.add('fa-chevron-down');
                }
            });
            
            // Toggle current FAQ
            faqItem.classList.toggle('active');
            
            if (faqItem.classList.contains('active')) {
                answer.style.maxHeight = answer.scrollHeight + 'px';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                answer.style.maxHeight = null;
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
