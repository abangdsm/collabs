// File main.js - fungsi umum untuk seluruh aplikasi
$(document).ready(function() {
    console.log('Main.js loaded - Collabs App siap!');
    
    // Inisialisasi tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});