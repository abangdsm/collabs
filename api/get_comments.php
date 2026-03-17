<?php
require_once '../includes/functions.php';
requireLogin();

$conn = getConnection();
$subtask_id = (int)$_GET['subtask_id'];

$comments = $conn->query("
    SELECT c.*, u.username, u.role
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.subtask_id = $subtask_id
    ORDER BY c.created_at DESC
");

if ($comments->num_rows == 0) {
    echo '<div class="text-muted text-center py-3">Belum ada komentar</div>';
} else {
    while($comment = $comments->fetch_assoc()):
        $is_owner = ($comment['user_id'] == $_SESSION['user_id']);
        $waktu = waktuLalu($comment['created_at']);
?>
<div class="d-flex mb-3 comment-item" data-comment-id="<?php echo $comment['id']; ?>">
    <div class="me-2">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
             style="width: 36px; height: 36px; font-size: 14px;">
            <?php echo strtoupper(substr($comment['username'], 0, 1)); ?>
        </div>
    </div>
    <div class="flex-grow-1">
        <div class="bg-light rounded-3 p-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                <small class="text-muted"><?php echo $waktu; ?></small>
            </div>
            <p class="mb-1 small"><?php echo nl2br(htmlspecialchars($comment['komentar'])); ?></p>
            <?php if (!empty($comment['link_attachment'])): ?>
            <a href="<?php echo htmlspecialchars($comment['link_attachment']); ?>" target="_blank" class="small text-primary">
                <i class="bi bi-link-45deg"></i> <?php echo htmlspecialchars($comment['link_attachment']); ?>
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