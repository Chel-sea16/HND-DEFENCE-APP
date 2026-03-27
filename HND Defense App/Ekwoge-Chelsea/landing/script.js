// Smooth scrolling for navigation links
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offsetTop = target.offsetTop - 80; // Account for fixed navbar
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const navActions = document.querySelector('.nav-actions');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('mobile-active');
            navActions.classList.toggle('mobile-active');
            
            // Change icon
            const icon = this.querySelector('i');
            if (icon.classList.contains('bi-list')) {
                icon.classList.remove('bi-list');
                icon.classList.add('bi-x');
            } else {
                icon.classList.remove('bi-x');
                icon.classList.add('bi-list');
            }
        });
    }

    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > 100) {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.backdropFilter = 'blur(10px)';
            navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
        } else {
            navbar.style.background = 'white';
            navbar.style.backdropFilter = 'none';
            navbar.style.boxShadow = 'none';
        }
        
        lastScrollTop = scrollTop;
    });

    // Button click handlers
    const getStartedBtns = document.querySelectorAll('.btn');
    getStartedBtns.forEach(btn => {
        if (btn.textContent.includes('Get Started') || btn.textContent.includes('Create Free Account')) {
            btn.addEventListener('click', function() {
                // Redirect to signup page or show signup modal
                window.location.href = 'signup-page.php';
            });
        }
        
        if (btn.textContent.includes('View Dashboard')) {
            btn.addEventListener('click', function() {
                // Redirect to dashboard
                window.location.href = 'dashboard.php';
            });
        }
        
        if (btn.textContent.includes('Login')) {
            btn.addEventListener('click', function() {
                // Redirect to login page
                window.location.href = 'login-page.php';
            });
        }
        
        if (btn.textContent.includes('Sign Up')) {
            btn.addEventListener('click', function() {
                // Redirect to signup page
                window.location.href = 'signup-page.php';
            });
        }
    });

    // Add scroll animations for elements
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe elements for animation
    const animateElements = document.querySelectorAll('.feature-card, .benefit-card, .pricing-card, .step');
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // Highlight active navigation section
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');

    window.addEventListener('scroll', function() {
        let current = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.pageYOffset >= (sectionTop - 100)) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    });
});

// Add mobile menu styles dynamically
const style = document.createElement('style');
style.textContent = `
    .nav-menu.mobile-active,
    .nav-actions.mobile-active {
        display: flex !important;
        position: absolute;
        top: 70px;
        left: 0;
        right: 0;
        background: white;
        flex-direction: column;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        gap: 16px;
    }
    
    .nav-menu.mobile-active {
        border-bottom: 1px solid #e5e7eb;
    }
    
    .nav-actions.mobile-active {
        border-top: 1px solid #e5e7eb;
    }
    
    .nav-link.active {
        color: #3b82f6 !important;
    }
    
    @media (max-width: 768px) {
        .nav-menu,
        .nav-actions {
            display: none;
        }
    }
`;
document.head.appendChild(style);
