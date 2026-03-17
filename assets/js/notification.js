$(document).ready(function() {
    // Cek notifikasi setiap 30 detik
    setInterval(function() {
        checkNotifications();
    }, 30000);
});

function checkNotifications() {
    $.ajax({
        url: baseUrl + '/api/get_notifications.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.unread_count > 0) {
                $('#notif-badge').text(response.unread_count).show();
            } else {
                $('#notif-badge').hide();
            }
            
            // Update notifikasi list
            var notifHtml = '';
            if (response.notifications.length > 0) {
                response.notifications.forEach(function(notif) {
                    notifHtml += '<li><a class="dropdown-item" href="#">' + 
                                 notif.message + 
                                 '<br><small>' + notif.created_at + '</small></a></li>';
                });
            } else {
                notifHtml = '<li><span class="dropdown-item text-muted">Tidak ada notifikasi</span></li>';
            }
            $('#notif-content').html(notifHtml);
        }
    });
}

// Base URL dari PHP (akan di-set di footer)
var baseUrl = '<?php echo base_url(); ?>';