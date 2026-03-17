<?php
require_once '../includes/functions.php';
requireLogin();

$conn = getConnection();
$parent_id = (int)$_GET['parent_id'];

$replies = $conn->query("
    SELECT c.*, u.username, u.role
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.parent_id = $parent_id
    ORDER BY c.created_at ASC
");

if ($replies->num_rows == 0) {
    echo '';
} else {
    while($reply = $replies->fetch_assoc()):
        $waktu = waktuLalu($reply['created_at']);
?>
<div class="d-flex mb-2 reply-item">
    <div class="me-2">
        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" 
             style="width: 28px; height: 28px; font-size: 12px;">
            <?php echo strtoupper(substr($reply['username'], 0, 1)); ?>
        </div>
    </div>
    <div class="flex-grow-1">
        <div class="bg-light rounded-3 p-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <strong class="small"><?php echo htmlspecialchars($reply['username']); ?></strong>
                <small class="text-muted" style="font-size: 10px;"><?php echo $waktu; ?></small>
            </div>
            <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($reply['komentar'])); ?></p>
            <?php if (!empty($reply['link_attachment'])): ?>
            <a href="<?php echo htmlspecialchars($reply['link_attachment']); ?>" target="_blank" class="small text-primary">
                <i class="bi bi-link-45deg"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php 
    endwhile;
}
$conn->close();
?>