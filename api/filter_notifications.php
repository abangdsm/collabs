<?php
require_once '../includes/functions.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all';

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
<?php 
endwhile;
$conn->close();

function waktuLalu($datetime) {
    $waktu = strtotime($datetime);
    $sekarang = time();
    $diff = $sekarang - $waktu;
    
    if ($diff < 60) return "baru saja";
    if ($diff < 3600) return floor($diff/60) . " menit lalu";
    if ($diff < 86400) return floor($diff/3600) . " jam lalu";
    if ($diff < 259200) return floor($diff/86400) . " hari lalu";
    return date('d/m/Y H:i', $waktu);
}
?>