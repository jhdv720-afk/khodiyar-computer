/**
 * KHODIYAR COMPUTER - Admin JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    'use strict';
    
    // ============================================
    // 1. CONFIRM DELETE
    // ============================================
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm') || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });
    
    // ============================================
    // 2. AUTO-HIDE ALERTS
    // ============================================
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            var closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });
    
    // ============================================
    // 3. SIDEBAR TOGGLE (Mobile)
    // ============================================
    var sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.admin-sidebar').classList.toggle('show');
        });
    }
    
    // ============================================
    // 4. TOOLTIP INIT
    // ============================================
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) {
        return new bootstrap.Tooltip(el);
    });
    
});
