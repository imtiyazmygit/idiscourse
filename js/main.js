/**
 * i-Discourse Mehfil (IDM) Knowledge Hub
 * Professional Enhanced JavaScript
 * Mobile Navigation | Theme Toggle | Accessibility | Performance
 */

// ========== DOM Elements ==========
const menuToggle = document.querySelector('.menu-toggle');
const navMenu = document.querySelector('.nav-menu');
const themeToggle = document.getElementById('themeToggle');
const dropdowns = document.querySelectorAll('.dropdown');
const backToTopBtn = document.querySelector('.back-to-top');

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initThemeToggle();
    initDropdowns();
    initBackToTop();
    initScrollAnimations();
    initSmoothScroll();
    initFormValidation();
    initActiveLinks();
    initAccessibility();
    animateOnLoad();
    console.log('✅ IDM Knowledge Hub - All features initialized');
});

// ========== MOBILE MENU TOGGLE ==========
function initMobileMenu() {
    if (!menuToggle || !navMenu) return;
    
    menuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('show');
        menuToggle.setAttribute('aria-expanded', navMenu.classList.contains('show'));
    });
    
    // Close menu when clicking on a link
    const navLinks = navMenu.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('show');
            menuToggle.setAttribute('aria-expanded', 'false');
        });
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (navMenu.contains(e.target) || menuToggle.contains(e.target)) return;
        navMenu.classList.remove('show');
        menuToggle.setAttribute('aria-expanded', 'false');
    });
    
    // Close menu on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && navMenu.classList.contains('show')) {
            navMenu.classList.remove('show');
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.focus();
        }
    });
    
    // Responsive: close menu on resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            navMenu.classList.remove('show');
            menuToggle.setAttribute('aria-expanded', 'false');
        }
    });
}

// ========== THEME TOGGLE ==========
function initThemeToggle() {
    if (!themeToggle) return;
    
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
    let currentTheme = localStorage.getItem('theme') || (prefersDark.matches ? 'dark' : 'light');
    
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeIcon(currentTheme);
    
    themeToggle.addEventListener('click', () => {
        currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', currentTheme);
        localStorage.setItem('theme', currentTheme);
        updateThemeIcon(currentTheme);
    });
}

function updateThemeIcon(theme) {
    themeToggle.textContent = theme === 'dark' ? '☀️' : '🌙';
    themeToggle.setAttribute('aria-label', `Switch to ${theme === 'dark' ? 'light' : 'dark'} mode`);
}

// ========== DROPDOWNS ==========
function initDropdowns() {
    dropdowns.forEach(dropdown => {
        const btn = dropdown.querySelector('.dropdown-btn');
        if (!btn) return;
        
        btn.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                dropdown.classList.toggle('active');
            }
        });
    });
    
    // Close dropdowns on desktop resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            dropdowns.forEach(d => d.classList.remove('active'));
        }
    });
}

// ========== BACK TO TOP ==========
function initBackToTop() {
    if (!backToTopBtn) return;
    
    window.addEventListener('scroll', debounce(() => {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    }, 100));
    
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

// ========== SCROLL ANIMATIONS ==========
function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    document.querySelectorAll('.feature-card, .scholar-card, .post-card, .stat-card').forEach(el => {
        observer.observe(el);
    });
}

// ========== SMOOTH SCROLL ==========
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && document.querySelector(href)) {
                e.preventDefault();
                document.querySelector(href).scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

// ========== FORM VALIDATION ==========
function initFormValidation() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            this.querySelectorAll('[required]').forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.setAttribute('aria-invalid', 'true');
                    input.classList.add('error');
                } else {
                    input.setAttribute('aria-invalid', 'false');
                    input.classList.remove('error');
                }
            });
            
            if (!isValid) e.preventDefault();
        });
        
        // Input focus states
        form.querySelectorAll('input, textarea, select').forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', () => {
                input.parentElement.classList.remove('focused');
            });
        });
    });
}

// ========== ACTIVE LINK HIGHLIGHTING ==========
function initActiveLinks() {
    const currentPath = location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath || (currentPath === '/' && href === 'index.php')) {
            link.classList.add('active');
        }
    });
}

// ========== ACCESSIBILITY ==========
function initAccessibility() {
    // Skip to main content link
    const skipLink = document.createElement('a');
    skipLink.href = 'main';
    skipLink.className = 'skip-link';
    skipLink.textContent = 'Skip to main content';
    document.body.prepend(skipLink);
    
    // Delete confirmation
    document.querySelectorAll('.action-btn.delete, a[onclick*="confirm"]').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
}

// ========== PAGE LOAD ANIMATION ==========
function animateOnLoad() {
    document.body.classList.add('loaded');
    
    const elements = document.querySelectorAll('.hero h1, .hero p, .feature-card, .scholar-card');
    elements.forEach((el, i) => {
        el.style.cssText = `opacity: 0; transform: translateY(20px); transition: all 0.6s ease ${i * 0.1}s;`;
        setTimeout(() => {
            el.style.cssText = `opacity: 1; transform: translateY(0); transition: all 0.6s ease ${i * 0.1}s;`;
        }, 50);
    });
}

// ========== UTILITY: Debounce ==========
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

document.addEventListener('DOMContentLoaded', function() {
    
    // ========== MOBILE MENU TOGGLE ==========
    const navBrand = document.querySelector('.nav-brand');
    const navMenu = document.querySelector('.nav-menu');
    
    // Create hamburger menu button for mobile
    if (navBrand && navMenu && !document.querySelector('.menu-toggle')) {
        const menuToggle = document.createElement('button');
        menuToggle.innerHTML = '☰';
        menuToggle.className = 'menu-toggle';
        menuToggle.setAttribute('aria-label', 'Menu');
        menuToggle.setAttribute('aria-expanded', 'false');
        
        // Insert hamburger button into nav-brand
        navBrand.appendChild(menuToggle);
        
        // Toggle menu on button click
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            navMenu.classList.toggle('show');
            const isExpanded = navMenu.classList.contains('show');
            menuToggle.setAttribute('aria-expanded', isExpanded);
            menuToggle.innerHTML = isExpanded ? '✕' : '☰';
        });
    }
    
    // ========== DROPDOWN TOGGLE FOR MOBILE ==========
    const dropdownBtns = document.querySelectorAll('.dropdown-btn');
    
    dropdownBtns.forEach(btn => {
        // Remove any existing listeners to prevent duplicates
        btn.removeEventListener('click', handleDropdownClick);
        btn.removeEventListener('touchstart', handleDropdownClick);
        btn.addEventListener('click', handleDropdownClick);
        btn.addEventListener('touchstart', handleDropdownClick);
    });
    
    function handleDropdownClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close other dropdowns
        dropdownBtns.forEach(otherBtn => {
            if (otherBtn !== this) {
                otherBtn.parentElement.classList.remove('active');
            }
        });
        
        // Toggle current dropdown
        const parent = this.parentElement;
        parent.classList.toggle('active');
    }
    
    // ========== CLOSE MENU WHEN CLICKING OUTSIDE ==========
    document.addEventListener('click', function(event) {
        // Close mobile menu if clicking outside
        if (navMenu && navMenu.classList.contains('show')) {
            if (!navMenu.contains(event.target) && 
                !event.target.classList.contains('menu-toggle')) {
                navMenu.classList.remove('show');
                const menuToggle = document.querySelector('.menu-toggle');
                if (menuToggle) {
                    menuToggle.innerHTML = '☰';
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            }
        }
        
        // Close dropdowns when clicking outside
        if (!event.target.closest('.dropdown')) {
            dropdownBtns.forEach(btn => {
                btn.parentElement.classList.remove('active');
            });
        }
    });
    
    // ========== SMOOTH SCROLLING FOR ANCHOR LINKS ==========
    const anchorLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Close mobile menu after clicking a link
                if (navMenu && navMenu.classList.contains('show')) {
                    navMenu.classList.remove('show');
                    const menuToggle = document.querySelector('.menu-toggle');
                    if (menuToggle) {
                        menuToggle.innerHTML = '☰';
                        menuToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            }
        });
    });
    
    // ========== RESPONSIVE RESIZE HANDLER ==========
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // On desktop, ensure menu is visible
            if (window.innerWidth >= 768) {
                if (navMenu) {
                    navMenu.classList.remove('show');
                    navMenu.style.display = '';
                }
                const menuToggle = document.querySelector('.menu-toggle');
                if (menuToggle) {
                    menuToggle.style.display = 'none';
                }
                // Reset dropdowns
                dropdownBtns.forEach(btn => {
                    btn.parentElement.classList.remove('active');
                });
            } else {
                const menuToggle = document.querySelector('.menu-toggle');
                if (menuToggle) {
                    menuToggle.style.display = 'block';
                }
                if (navMenu && !navMenu.classList.contains('show')) {
                    navMenu.style.display = '';
                }
            }
        }, 250);
    });
    
    // Trigger resize handler on load
    window.dispatchEvent(new Event('resize'));
    
    // ========== SEARCH FORM ENHANCEMENT ==========
    const searchForms = document.querySelectorAll('.search-form');
    searchForms.forEach(form => {
        const searchInput = form.querySelector('input[type="text"]');
        if (searchInput) {
            searchInput.addEventListener('search', function() {
                if (this.value === '') {
                    form.submit();
                }
            });
        }
    });
    
    // ========== FORM VALIDATION ==========
    const forms = document.querySelectorAll('form:not(.no-validate)');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#e53e3e';
                    
                    // Add error message if not exists
                    let errorMsg = field.parentElement.querySelector('.error-message');
                    if (!errorMsg) {
                        errorMsg = document.createElement('small');
                        errorMsg.className = 'error-message';
                        errorMsg.style.color = '#e53e3e';
                        errorMsg.style.fontSize = '11px';
                        errorMsg.style.marginTop = '4px';
                        errorMsg.style.display = 'block';
                        field.parentElement.appendChild(errorMsg);
                    }
                    errorMsg.textContent = 'This field is required';
                } else {
                    field.style.borderColor = '#e2e8f0';
                    const errorMsg = field.parentElement.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Remove error styling on input
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '#e2e8f0';
                const errorMsg = this.parentElement.querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.remove();
                }
            });
        });
    });
    
    // ========== CONFIRM DELETE DIALOGS ==========
    const deleteLinks = document.querySelectorAll('.action-btn.delete, a[onclick*="confirm"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const originalOnClick = this.getAttribute('onclick');
            if (originalOnClick && originalOnClick.includes('confirm')) {
                // Allow the original confirm to work
                return;
            }
            
            const message = this.getAttribute('data-confirm') || 'Are you sure you want to delete this item? This action cannot be undone.';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // ========== BACK TO TOP BUTTON ==========
    const backToTop = document.createElement('button');
    backToTop.innerHTML = '↑';
    backToTop.className = 'back-to-top';
    backToTop.setAttribute('aria-label', 'Back to top');
    backToTop.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #1e3a5f, #2d5a3b);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 20px;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(backToTop);
    
    // Show/hide back to top button
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTop.style.display = 'flex';
        } else {
            backToTop.style.display = 'none';
        }
    });
    
    backToTop.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // ========== LOADING INDICATOR FOR AJAX FORMS ==========
    const submitButtons = document.querySelectorAll('form button[type="submit"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            if (form && form.classList.contains('no-validate')) {
                return;
            }

            if (form && form.checkValidity()) {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                this.disabled = true;
                
                // Reset button after form submission (fallback)
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 3000);
            }
        });
    });
    
    // ========== ANIMATION ON SCROLL ==========
    const animateElements = document.querySelectorAll('.feature-card, .scholar-card, .post-card');
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });
    
    // ========== SEARCH INPUT CLEAR BUTTON ==========
    const searchInputs = document.querySelectorAll('.search-form input[type="text"]');
    searchInputs.forEach(input => {
        // Add clear button wrapper
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.flex = '1';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        
        // Create clear button
        const clearBtn = document.createElement('button');
        clearBtn.innerHTML = '✕';
        clearBtn.type = 'button';
        clearBtn.style.cssText = `
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 14px;
            display: none;
            padding: 5px;
        `;
        wrapper.appendChild(clearBtn);
        
        input.style.paddingRight = '30px';
        
        input.addEventListener('input', function() {
            clearBtn.style.display = this.value.length > 0 ? 'block' : 'none';
        });
        
        clearBtn.addEventListener('click', function() {
            input.value = '';
            input.focus();
            clearBtn.style.display = 'none';
            input.dispatchEvent(new Event('input'));
        });
    });
    
    // ========== TOOLTIP FOR TRUNCATED TEXT ==========
    const truncatedTexts = document.querySelectorAll('.scholar-specialization, .post-excerpt');
    truncatedTexts.forEach(el => {
        if (el.scrollWidth > el.clientWidth) {
            el.setAttribute('title', el.textContent);
        }
    });
    
    // ========== DARK MODE TOGGLE (OPTIONAL) ==========
    // Check if user prefers dark mode
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
    const darkModeToggle = document.querySelector('.dark-mode-toggle');
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });
        
        // Load saved preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    }
    
    // ========== LOGGING (Remove in production) ==========
    console.log('i-Discourse Mehfil (IDM) Knowledge Hub - JavaScript Loaded');
    console.log('Mobile menu enabled:', window.innerWidth < 768);
});