<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: text/html; charset=utf-8');

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all';

// Query berdasarkan filter
$sql = "SELECT * FROM notifications WHERE user_id = $user_id";
if ($filter == 'unread') {
    $sql .= " AND is_read = 0";
}
$sql .= " ORDER BY created_at DESC LIMIT 50";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo '<div class="text-center py-5">
            <i class="bi bi-bell-slash" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mt-3">Tidak ada notifikasi</p>
          </div>';
    $conn->close();
    exit();
}

while($notif = $result->fetch_assoc()):
    // Tentukan icon berdasarkan type - PAKAI BOOTSTRAP ICONS
    $icon = '';
    switch ($notif['type']) {
        case 'success':
            $icon = '<i class="bi bi-check-circle-fill text-success" style="font-size: 1.2rem;"></i>';
            break;
        case 'warning':
            $icon = '<i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 1.2rem;"></i>';
            break;
        case 'danger':
            $icon = '<i class="bi bi-x-circle-fill text-danger" style="font-size: 1.2rem;"></i>';
            break;
        case 'info':
        default:
            $icon = '<i class="bi bi-chat-dots-fill text-primary" style="font-size: 1.2rem;"></i>';
            break;
    }
    
    $icon_class = $notif['type'];
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
                    <i class="bi bi-check"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php 
endwhile;
$conn->close();
?>