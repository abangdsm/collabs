/**
 * notifications.js - Menangani semua fungsionalitas notifikasi di Collabs
 * Fitur: polling notifikasi, update badge, mark as read, mark all as read
 */

$(document).ready(function() {
    console.log('🔔 Notifications.js loaded - sistem notifikasi siap');
    
    // Cek notifikasi pertama kali saat halaman dimuat
    setTimeout(function() {
        checkNotifications();
    }, 500); // Delay 500ms biar halaman stabil dulu
    
    // Cek notifikasi setiap 10 detik
    setInterval(checkNotifications, 10000);
    
    // Event listener untuk klik pada notifikasi
    $(document).on('click', '.notif-item', function(e) {
        e.preventDefault();
        var notifId = $(this).data('notif-id');
        var link = $(this).data('link');
        
        console.log('Notifikasi diklik - ID:', notifId, 'Link:', link);
        
        // Tandai sudah dibaca
        markAsRead(notifId);
        
        // Redirect ke link jika ada
        if (link) {
            window.location.href = link;
        }
    });
    
    // Event listener untuk tombol "Tandai semua sudah dibaca"
    $(document).on('click', '#markAllRead', function(e) {
        e.preventDefault();
        console.log('Tandai semua notifikasi sudah dibaca');
        markAllAsRead();
    });
    
    // Saat dropdown notifikasi dibuka, refresh data
    $(document).on('show.bs.dropdown', '#notifDropdown', function() {
        console.log('Dropdown notifikasi dibuka - merefresh data');
        checkNotifications();
    });
    
    // Debug: cek elemen notifikasi ada
    console.log('Elemen notifikasi:', {
        badge: $('#notif-badge').length ? 'Ada' : 'Tidak ada',
        menu: $('#notif-menu').length ? 'Ada' : 'Tidak ada',
        content: $('#notif-content').length ? 'Ada' : 'Tidak ada'
    });
});

/**
 * Fungsi utama untuk mengambil notifikasi dari server
 */
function checkNotifications() {
    console.log('📡 Mengambil notifikasi dari server...');
    
    $.ajax({
        url: baseUrl + '/api/get_notifications.php',
        method: 'GET',
        dataType: 'json',
        cache: false,
        timeout: 8000, // Timeout 8 detik
        success: function(response) {
            console.log('✅ Response notifikasi:', response);
            
            // Validasi response
            if (!response) {
                console.error('Response kosong');
                showNotifError('Response kosong');
                return;
            }
            
            if (!response.success) {
                console.error('Server error:', response.message);
                showNotifError(response.message || 'Unknown error');
                return;
            }
            
            // Update badge notifikasi
            updateNotifBadge(response.unread_count);
            
            // Update daftar notifikasi
            updateNotifList(response.notifications);
        },
        error: function(xhr, status, error) {
            console.error('❌ Gagal mengambil notifikasi:', {
                status: status,
                error: error,
                response: xhr.responseText,
                statusCode: xhr.status
            });
            
            var errorMsg = 'Gagal memuat notifikasi';
            if (xhr.status === 404) {
                errorMsg = 'API notifikasi tidak ditemukan';
            } else if (xhr.status === 500) {
                errorMsg = 'Error server (500)';
            } else if (status === 'timeout') {
                errorMsg = 'Koneksi timeout';
            }
            
            showNotifError(errorMsg + '<br><small>' + (error || '') + '</small>');
        }
    });
}

/**
 * Update badge notifikasi
 */
function updateNotifBadge(unreadCount) {
    console.log('Update badge - notifikasi belum dibaca:', unreadCount);
    
    if (unreadCount > 0) {
        $('#notif-badge').text(unreadCount).show();
        
        // Efek kedip untuk menarik perhatian
        $('#notif-badge').fadeOut(150).fadeIn(150).fadeOut(150).fadeIn(150);
    } else {
        $('#notif-badge').hide();
    }
}

/**
 * Update daftar notifikasi di dropdown
 */
function updateNotifList(notifications) {
    console.log('Update daftar notifikasi - jumlah:', notifications ? notifications.length : 0);
    
    var notifHtml = '';
    
    if (notifications && notifications.length > 0) {
        // Loop semua notifikasi
        notifications.forEach(function(notif) {
            // Tentukan class berdasarkan status baca
            var bgClass = notif.is_read ? '' : 'bg-warning bg-opacity-10';
            
            // Bersihkan pesan dari emoji dobel
            var cleanMessage = notif.message.replace(/^[📢✅⚠️🔴\s]+/, '');
            
            notifHtml += '<a class="dropdown-item notif-item px-3 py-2 ' + bgClass + '" href="#" ' +
                       'data-notif-id="' + notif.id + '" ' +
                       'data-link="' + (notif.link || '') + '" ' +
                       'style="border-bottom: 1px solid #eee; white-space: normal; word-wrap: break-word;">' +
                '<div class="d-flex">' +
                '<span class="me-2" style="font-size: 1.1rem;">' + 
                    (notif.type == 'success' ? '✅' : 
                     notif.type == 'warning' ? '⚠️' : 
                     notif.type == 'danger' ? '🔴' : '📢') + 
                '</span>' +
                '<div class="flex-grow-1">' +
                '<div style="font-size: 0.9rem; line-height: 1.4; ' + (notif.is_read ? '' : 'font-weight: 500;') + '">' + 
                    cleanMessage + 
                '</div>' +
                '<small class="text-muted d-block mt-1" style="font-size: 0.75rem;">' + notif.created_at + '</small>' +
                '</div>' +
                '</div>' +
                '</a>';
        });
        
        // Tambahkan tombol "Tandai semua"
        notifHtml += '<div class="dropdown-divider my-0"></div>' +
            '<a class="dropdown-item text-center py-2" href="#" id="markAllRead" ' +
            'style="font-size: 0.85rem; background-color: #f8f9fa;">' +
            '<i class="bi bi-check2-all me-1"></i> Tandai semua sudah dibaca</a>';
    } else {
        // Tidak ada notifikasi
        notifHtml = '<div class="text-muted text-center p-4">' +
            '<i class="bi bi-bell-slash d-block mb-2" style="font-size: 2rem;"></i>' +
            '<span style="font-size: 0.9rem;">Tidak ada notifikasi</span>' +
            '</div>';
    }
    
    // Update ke DOM
    $('#notif-content').html(notifHtml);
    console.log('✅ Daftar notifikasi telah diupdate');
}

/**
 * Tampilkan pesan error di dropdown notifikasi
 */
function showNotifError(message) {
    $('#notif-content').html(
        '<div class="text-danger text-center p-3">' +
        '<i class="bi bi-exclamation-triangle"></i><br>' +
        message +
        '</div>'
    );
}

/**
 * Tandai satu notifikasi sudah dibaca
 */
function markAsRead(notifId) {
    console.log('Menandai notifikasi sudah dibaca - ID:', notifId);
    
    $.ajax({
        url: baseUrl + '/api/mark_notification_read.php',
        method: 'POST',
        data: { id: notifId },
        dataType: 'json',
        timeout: 5000,
        success: function(response) {
            console.log('Response mark as read:', response);
            
            if (response.success) {
                // Update badge (kurangi 1)
                var currentCount = parseInt($('#notif-badge').text() || 0);
                $('#notif-badge').text(Math.max(0, currentCount - 1));
                
                if ($('#notif-badge').text() == '0') {
                    $('#notif-badge').hide();
                }
                
                // Refresh daftar notifikasi
                checkNotifications();
            } else {
                console.error('Gagal mark as read:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error mark as read:', error);
        }
    });
}

/**
 * Tandai semua notifikasi sudah dibaca
 */
function markAllAsRead() {
    console.log('Menandai SEMUA notifikasi sudah dibaca');
    
    $.ajax({
        url: baseUrl + '/api/mark_notification_read.php',
        method: 'POST',
        data: { all: 'true' },
        dataType: 'json',
        timeout: 5000,
        success: function(response) {
            console.log('Response mark all as read:', response);
            
            if (response.success) {
                // Sembunyikan badge
                $('#notif-badge').hide();
                
                // Refresh daftar notifikasi
                checkNotifications();
                
                // Tampilkan pesan sukses
                alert('✅ Semua notifikasi telah ditandai sudah dibaca');
            } else {
                console.error('Gagal mark all as read:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error mark all as read:', error);
        }
    });
}