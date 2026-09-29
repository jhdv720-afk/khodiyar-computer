/**
 * KHODIYAR COMPUTER - Main JavaScript
 * IT Services Company Website
 */

document.addEventListener('DOMContentLoaded', function() {
    
    'use strict';
    
    // ============================================
    // 1. BACK TO TOP BUTTON
    // ============================================
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        
        backToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    
    // ============================================
    // 2. NAVBAR SCROLL EFFECT
    // ============================================
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.style.padding = '8px 0';
                navbar.style.boxShadow = '0 2px 30px rgba(0,0,0,0.4)';
            } else {
                navbar.style.padding = '12px 0';
                navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.3)';
            }
        });
    }
    
    // ============================================
    // 3. COUNTER ANIMATION
    // ============================================
    function animateCounter(element, target, duration) {
        let current = 0;
        const increment = target / (duration / 16);
        const timer = setInterval(function() {
            current += increment;
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 16);
    }
    
    // Initialize counters when visible
    const counterElements = document.querySelectorAll('[data-counter]');
    if (counterElements.length > 0) {
        const counterObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const target = parseInt(entry.target.getAttribute('data-counter'));
                    animateCounter(entry.target, target, 2000);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        counterElements.forEach(function(el) {
            counterObserver.observe(el);
        });
    }
    
    // ============================================
    // 4. BOOKING FORM VALIDATION
    // ============================================
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            const dateInput = document.getElementById('preferred_date');
            const phoneInput = document.getElementById('customer_phone');
            
            if (dateInput) {
                const selectedDate = new Date(dateInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    e.preventDefault();
                    alert('Please select a future date for booking.');
                    return false;
                }
            }
            
            if (phoneInput) {
                const phone = phoneInput.value.replace(/\s/g, '');
                if (phone.length !== 10 || !/^[0-9]+$/.test(phone)) {
                    e.preventDefault();
                    alert('Please enter a valid 10-digit phone number.');
                    return false;
                }
            }
        });
    }
    
    // ============================================
    // 5. PHONE INPUT FORMATTING
    // ============================================
    document.querySelectorAll('input[type="tel"]').forEach(function(input) {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    
    // ============================================
    // 6. AUTO-HIDE ALERTS
    // ============================================
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.click();
            }
        }, 5000);
    });
    
    // ============================================
    // 7. SMOOTH SCROLL FOR ANCHOR LINKS
    // ============================================
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
    
    // ============================================
    // 8. TOOLTIP INITIALIZATION
    // ============================================
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) {
        return new bootstrap.Tooltip(el);
    });
    
});

