// Mobile Menu Toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');

if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    // Close menu when clicking on a link
    document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    }));
}

// Scroll to Top Button
const scrollToTopBtn = document.getElementById('scrollToTop');

if (scrollToTopBtn) {
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.add('show');
        } else {
            scrollToTopBtn.classList.remove('show');
        }
    });

    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Active Navigation Link Highlighting
function setActiveNavLink() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
}

// Initialize active navigation
setActiveNavLink();

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Form Validation Functions
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateRequired(value) {
    return value.trim().length > 0;
}

function showError(input, message) {
    const formGroup = input.closest('.form-group');
    const errorDiv = formGroup.querySelector('.error-message') || document.createElement('div');
    
    input.classList.add('error');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    if (!formGroup.querySelector('.error-message')) {
        formGroup.appendChild(errorDiv);
    }
}

function clearError(input) {
    const formGroup = input.closest('.form-group');
    const errorDiv = formGroup.querySelector('.error-message');
    
    input.classList.remove('error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Contact Form Validation
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('name');
        const email = document.getElementById('email');
        const message = document.getElementById('message');
        let isValid = true;
        
        // Clear previous errors
        [name, email, message].forEach(input => clearError(input));
        
        // Validate name
        if (!validateRequired(name.value)) {
            showError(name, 'Name is required');
            isValid = false;
        }
        
        // Validate email
        if (!validateRequired(email.value)) {
            showError(email, 'Email is required');
            isValid = false;
        } else if (!validateEmail(email.value)) {
            showError(email, 'Please enter a valid email address');
            isValid = false;
        }
        
        // Validate message
        if (!validateRequired(message.value)) {
            showError(message, 'Message is required');
            isValid = false;
        }
        
        if (isValid) {
            // Show success message
            alert('Thank you for your message! We will get back to you soon.');
            contactForm.reset();
        }
    });
}

// Add Recipe Form Validation
const addRecipeForm = document.getElementById('addRecipeForm');
if (addRecipeForm) {
    addRecipeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const title = document.getElementById('title');
        const category = document.getElementById('category');
        const ingredients = document.getElementById('ingredients');
        const steps = document.getElementById('steps');
        const image = document.getElementById('image');
        let isValid = true;
        
        // Clear previous errors
        [title, category, ingredients, steps].forEach(input => clearError(input));
        
        // Validate title
        if (!validateRequired(title.value)) {
            showError(title, 'Recipe title is required');
            isValid = false;
        }
        
        // Validate category
        if (!validateRequired(category.value)) {
            showError(category, 'Please select a category');
            isValid = false;
        }
        
        // Validate ingredients
        if (!validateRequired(ingredients.value)) {
            showError(ingredients, 'Ingredients are required');
            isValid = false;
        }
        
        // Validate steps
        if (!validateRequired(steps.value)) {
            showError(steps, 'Cooking steps are required');
            isValid = false;
        }
        
        if (isValid) {
            alert('Recipe added successfully! Thank you for sharing.');
            addRecipeForm.reset();
        }
    });
}

// Search and Filter Functionality
const searchInput = document.getElementById('searchInput');
const filterButtons = document.querySelectorAll('.filter-btn');
const recipeCards = document.querySelectorAll('.recipe-card');

if (searchInput) {
    searchInput.addEventListener('input', filterRecipes);
}

if (filterButtons.length > 0) {
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            button.classList.add('active');
            filterRecipes();
        });
    });
}

function filterRecipes() {
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const activeFilter = document.querySelector('.filter-btn.active');
    const filterCategory = activeFilter ? activeFilter.textContent.toLowerCase() : '';
    
    recipeCards.forEach(card => {
        const title = card.querySelector('.recipe-title').textContent.toLowerCase();
        const description = card.querySelector('.recipe-description').textContent.toLowerCase();
        const category = card.querySelector('.recipe-category').textContent.toLowerCase();
        
        const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
        const matchesFilter = !filterCategory || category === filterCategory;
        
        if (matchesSearch && matchesFilter) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Pagination Functionality
function createPagination(totalPages, currentPage) {
    const pagination = document.querySelector('.pagination');
    if (!pagination) return;
    
    pagination.innerHTML = '';
    
    // Previous button
    if (currentPage > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.className = 'pagination-btn';
        prevBtn.textContent = 'Previous';
        prevBtn.addEventListener('click', () => goToPage(currentPage - 1));
        pagination.appendChild(prevBtn);
    }
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.className = 'pagination-btn';
        pageBtn.textContent = i;
        
        if (i === currentPage) {
            pageBtn.classList.add('active');
        }
        
        pageBtn.addEventListener('click', () => goToPage(i));
        pagination.appendChild(pageBtn);
    }
    
    // Next button
    if (currentPage < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.className = 'pagination-btn';
        nextBtn.textContent = 'Next';
        nextBtn.addEventListener('click', () => goToPage(currentPage + 1));
        pagination.appendChild(nextBtn);
    }
}

function goToPage(page) {
    // In a real application, this would make an AJAX request
    // For now, I'm just updating the URL and reload
    const url = new URL(window.location);
    url.searchParams.set('page', page);
    window.location.href = url.toString();
}

// Initialize pagination if on recipes page
if (window.location.pathname.includes('/recipes/recipes.php')) {
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = parseInt(urlParams.get('page')) || 1;
    createPagination(5, currentPage); // 5 for demo
}

// Lazy loading for images
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Initialize lazy loading
lazyLoadImages();

// Add loading animation for recipe cards
function addLoadingAnimation() {
    const recipeCards = document.querySelectorAll('.recipe-card');
    
    recipeCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// Initialize loading animation
addLoadingAnimation();

// Utility function to debounce search input
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Apply debouncing to search input
if (searchInput) {
    const debouncedFilter = debounce(filterRecipes, 300);
    searchInput.addEventListener('input', debouncedFilter);
}
