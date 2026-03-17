$(document).ready(function() {
    console.log('Notifications.js loaded');
    
    // Cek notifikasi setiap 10 detik
    checkNotifications();
    setInterval(checkNotifications, 10000);
    
    // Klik pada notifikasi untuk menandai sudah dibaca dan buka link
    $(document).on('click', '.notif-item', function(e) {
        e.preventDefault();
        var notifId = $(this).data('notif-id');
        var link = $(this).data('link');
        
        markAsRead(notifId);
        
        if (link) {
            window.location.href = link;
        }
    });
    
    // Tombol "Tandai semua sudah dibaca"
    $(document).on('click', '#markAllRead', function(e) {
        e.preventDefault();
        markAllAsRead();
    });
    
    // Hover efek
    $(document).on('mouseenter', '.notif-item', function() {
        $(this).addClass('bg-light');
    }).on('mouseleave', '.notif-item', function() {
        if (!$(this).data('is-read')) {
            $(this).removeClass('bg-light');
        }
    });
});

function checkNotifications() {
    $.ajax({
        url: baseUrl + '/api/get_notifications.php',
        method: 'GET',
        dataType: 'json',
        cache: false,
        success: function(response) {
            console.log('Notifikasi:', response.unread_count + ' belum dibaca');
            
            // Update badge
            if (response.unread_count > 0) {
                $('#notif-badge').text(response.unread_count).show();
                
                // Efek kedip-kedip kalau ada notif baru
                $('#notif-badge').fadeOut(100).fadeIn(100);
            } else {
                $('#notif-badge').hide();
            }
            
            // Update notifikasi list
            var notifHtml = '';
            if (response.notifications.length > 0) {
                response.notifications.forEach(function(notif) {
                    var bgClass = notif.is_read ? '' : 'bg-warning bg-opacity-10';
                    var boldClass = notif.is_read ? '' : 'fw-bold';
                    
                    notifHtml += '<li>' +
                        '<a class="dropdown-item notif-item ' + bgClass + '" href="#" ' +
                           'data-notif-id="' + notif.id + '" ' +
                           'data-link="' + (notif.link || '') + '">' +
                        '<div class="d-flex flex-column">' +
                        '<span class="' + boldClass + '">' + notif.message + '</span>' +
                        '<small class="text-muted">' + notif.created_at + '</small>' +
                        '</div>' +
                        '</a>' +
                        '</li>';
                });
                
                notifHtml += '<li><hr class="dropdown-divider"></li>' +
                    '<li><a class="dropdown-item text-center small" href="#" id="markAllRead">' +
                    '<i class="bi bi-check2-all"></i> Tandai semua sudah dibaca</a></li>';
            } else {
                notifHtml = '<li><span class="dropdown-item text-muted text-center py-3">' +
                    '<i class="bi bi-bell-slash"></i> Tidak ada notifikasi</span></li>';
            }
            $('#notif-content').html(notifHtml);
        },
        error: function(xhr, status, error) {
            console.error('Error ambil notifikasi:', error);
        }
    });
}

function markAsRead(notifId) {
    $.ajax({
        url: baseUrl + '/api/mark_notification_read.php',
        method: 'POST',
        data: { id: notifId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update badge tanpa refresh semua
                var currentCount = parseInt($('#notif-badge').text() || 0);
                $('#notif-badge').text(Math.max(0, currentCount - 1));
                if ($('#notif-badge').text() == '0') {
                    $('#notif-badge').hide();
                }
            }
        }
    });
}

function markAllAsRead() {
    $.ajax({
        url: baseUrl + '/api/mark_notification_read.php',
        method: 'POST',
        data: { all: 'true' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#notif-badge').hide();
                // Tandai semua item sebagai sudah dibaca
                $('.notif-item').removeClass('bg-warning bg-opacity-10 fw-bold');
            }
        }
    });
}