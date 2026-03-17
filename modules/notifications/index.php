<?php
require_once '../../includes/functions.php';
requireLogin();

$page_title = 'Semua Notifikasi';
include '../../includes/header.php';

// Definisikan fungsi waktuLalu di sini
function waktuLalu($datetime) {
    if (!$datetime) return '';
    
    $waktu = strtotime($datetime);
    $sekarang = time();
    $diff = $sekarang - $waktu;
    
    if ($diff < 60) return "baru saja";
    if ($diff < 3600) return floor($diff/60) . " menit lalu";
    if ($diff < 86400) return floor($diff/3600) . " jam lalu";
    if ($diff < 259200) return floor($diff/86400) . " hari lalu";
    return date('d/m/Y H:i', $waktu);
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// ... sisanya tetap sama ...

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Ambil total notifikasi
$total_result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = $user_id");
$total_row = $total_result->fetch_assoc();
$total_notifications = $total_row['total'];
$total_pages = ceil($total_notifications / $limit);

// Ambil notifikasi dengan pagination
$result = $conn->query("
    SELECT * FROM notifications 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC 
    LIMIT $offset, $limit
");

// Hitung notifikasi yang belum dibaca (untuk tombol mark all)
$unread_count = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = $user_id AND is_read = 0")->fetch_assoc()['total'];
?>

<style>
.notif-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.notif-icon.info { background-color: #e3f2fd; color: #0d6efd; }
.notif-icon.success { background-color: #d1e7dd; color: #198754; }
.notif-icon.warning { background-color: #fff3cd; color: #ffc107; }
.notif-icon.danger { background-color: #f8d7da; color: #dc3545; }

.notif-item {
    transition: background-color 0.2s;
    border-left: 3px solid transparent;
}

.notif-item.unread {
    background-color: #f0f7ff;
    border-left-color: #0d6efd;
}

.notif-item:hover {
    background-color: #f8f9fa;
}

.notif-time {
    font-size: 0.75rem;
    color: #6c757d;
}

.mark-read-btn {
    opacity: 0;
    transition: opacity 0.2s;
}

.notif-item:hover .mark-read-btn {
    opacity: 1;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0">
        <i class="bi bi-bell me-2"></i> Semua Notifikasi
    </h2>
    <?php if ($unread_count > 0): ?>
        <button class="btn btn-outline-primary btn-sm" id="markAllRead">
            <i class="bi bi-check2-all me-1"></i> Tandai semua sudah dibaca
        </button>
    <?php endif; ?>
</div>

<!-- Filter tabs -->
<div class="card mb-4">
    <div class="card-body p-2">
        <ul class="nav nav-pills nav-fill" id="notifTabs">
            <li class="nav-item">
                <a class="nav-link active" href="#" data-filter="all">
                    Semua <span class="badge bg-secondary ms-1"><?php echo $total_notifications; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-filter="unread">
                    Belum dibaca <span class="badge bg-primary ms-1"><?php echo $unread_count; ?></span>
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Daftar notifikasi -->
<div class="card shadow-sm" id="notifications-list">
    <div class="list-group list-group-flush" id="notif-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while($notif = $result->fetch_assoc()): 
                $icon_class = $notif['type'];
                $icon = $notif['type'] == 'success' ? '✅' : ($notif['type'] == 'warning' ? '⚠️' : ($notif['type'] == 'danger' ? '🔴' : '📢'));
                $unread_class = $notif['is_read'] ? '' : 'unread';
                $waktu = waktuLalu($notif['created_at']);
            ?>
            <div class="list-group-item notif-item <?php echo $unread_class; ?>" data-notif-id="<?php echo $notif['id']; ?>" data-link="<?php echo $notif['link'] ?? ''; ?>">
                <div class="d-flex align-items-start">
                    <div class="notif-icon <?php echo $icon_class; ?> me-3">
                        <?php echo $icon; ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1 <?php echo $notif['is_read'] ? '' : 'fw-semibold'; ?>">
                                    <?php echo htmlspecialchars($notif['message']); ?>
                                </p>
                                <small class="notif-time">
                                    <i class="bi bi-clock me-1"></i> <?php echo $waktu; ?>
                                </small>
                            </div>
                            <?php if (!$notif['is_read']): ?>
                            <button class="btn btn-sm btn-link mark-read-btn" onclick="markAsRead(<?php echo $notif['id']; ?>, this)">
                                <i class="bi bi-check"></i> Tandai dibaca
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-bell-slash" style="font-size: 3rem; color: #dee2e6;"></i>
                <p class="text-muted mt-3">Belum ada notifikasi</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="card-footer bg-white">
        <nav aria-label="Pagination notifikasi">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page-1; ?>">Sebelumnya</a>
                </li>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page+1; ?>">Selanjutnya</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Klik pada notifikasi
    $('.notif-item').click(function(e) {
        if ($(e.target).is('button') || $(e.target).parent().is('button')) {
            return;
        }
        
        var notifId = $(this).data('notif-id');
        var link = $(this).data('link');
        
        markAsRead(notifId);
        
        if (link) {
            window.location.href = link;
        }
    });
    
    // Filter notifikasi
    $('#notifTabs a').click(function(e) {
        e.preventDefault();
        $('#notifTabs a').removeClass('active');
        $(this).addClass('active');
        
        var filter = $(this).data('filter');
        filterNotifications(filter);
    });
    
    // Mark all as read
    $('#markAllRead').click(function() {
        markAllAsRead();
    });
});

function filterNotifications(filter) {
    $.ajax({
        url: baseUrl + '/api/filter_notifications.php',
        data: { filter: filter },
        dataType: 'html',
        success: function(data) {
            $('#notif-container').html(data);
        }
    });
}

function markAsRead(notifId, btn) {
    $.ajax({
        url: baseUrl + '/api/mark_notification_read.php',
        method: 'POST',
        data: { id: notifId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var item = $('.notif-item[data-notif-id="' + notifId + '"]');
                item.removeClass('unread');
                if (btn) $(btn).remove();
                
                // Update badge di navbar
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
                $('.notif-item').removeClass('unread');
                $('.mark-read-btn').remove();
                $('#markAllRead').remove();
                $('#notif-badge').hide();
                
                // Update tab badge
                $('a[data-filter="unread"] .badge').text('0');
            }
        }
    });
}
</script>

<?php 
$conn->close();
include '../../includes/footer.php'; 
?>