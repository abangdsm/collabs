// File notifications.js - untuk menangani notifikasi
$(document).ready(function() {
    console.log('Notifications.js loaded');
    
    // Cek notifikasi setiap 30 detik
    setInterval(function() {
        checkNotifications();
    }, 30000);
    
    // Cek notifikasi pertama kali
    setTimeout(function() {
        checkNotifications();
    }, 2000);
});

function checkNotifications() {
    $.ajax({
        url: baseUrl + '/api/get_notifications.php',
        method: 'GET',
        dataType: 'json',
        timeout: 5000,
        success: function(response) {
            console.log('Notifikasi:', response);
            
            // Update badge notifikasi
            if (response.unread_count > 0) {
                $('#notif-badge').text(response.unread_count).show();
            } else {
                $('#notif-badge').hide();
            }
            
            // Update notifikasi list
            var notifHtml = '';
            if (response.notifications && response.notifications.length > 0) {
                response.notifications.forEach(function(notif) {
                    notifHtml += '<li>' +
                        '<a class="dropdown-item" href="#">' +
                        '<div class="d-flex flex-column">' +
                        '<span>' + notif.message + '</span>' +
                        '<small class="text-muted">' + notif.created_at + '</small>' +
                        '</div>' +
                        '</a>' +
                        '</li>';
                });
            } else {
                notifHtml = '<li><span class="dropdown-item text-muted">Tidak ada notifikasi</span></li>';
            }
            $('#notif-content').html(notifHtml);
        },
        error: function(xhr, status, error) {
            console.error('Error ambil notifikasi:', error);
        }
    });
}