<?php
require_once '../includes/functions.php';
requireLogin();

$conn = getConnection();
$subtask_id = (int)$_GET['subtask_id'];

// Ambil komentar utama (parent_id IS NULL)
$comments = $conn->query("
    SELECT c.*, u.username, u.role
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.subtask_id = $subtask_id AND c.parent_id IS NULL
    ORDER BY c.created_at DESC
");

if ($comments->num_rows == 0) {
    echo '<div class="text-muted text-center py-3">Belum ada komentar</div>';
} else {
    while($comment = $comments->fetch_assoc()):
        $is_owner = ($comment['user_id'] == $_SESSION['user_id']);
        $waktu = waktuLalu($comment['created_at']);
?>
<div class="comment-thread mb-3" data-comment-id="<?php echo $comment['id']; ?>">
    <!-- Komentar Utama -->
    <div class="d-flex mb-2 comment-item">
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
                    <i class="bi bi-link-45deg"></i> Link
                </a>
                <?php endif; ?>
                
                <!-- Tombol Balas -->
                <div class="mt-1">
                    <button class="btn btn-sm btn-link p-0 small" onclick="showReplyForm(<?php echo $comment['id']; ?>)">
                        <i class="bi bi-reply"></i> Balas
                    </button>
                </div>
                
                <!-- Form Balas (hidden by default) -->
                <div id="reply-form-<?php echo $comment['id']; ?>" style="display: none;" class="mt-2">
                    <form class="reply-form" data-parent-id="<?php echo $comment['id']; ?>" data-subtask-id="<?php echo $subtask_id; ?>">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control form-control-sm" placeholder="Tulis balasan..." name="komentar" required>
                            <button class="btn btn-sm btn-outline-primary" type="submit">Kirim</button>
                            <button class="btn btn-sm btn-outline-secondary" type="button" onclick="hideReplyForm(<?php echo $comment['id']; ?>)">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Balasan-balasan (di-load via AJAX) -->
            <div class="replies-container ms-4 mt-2" id="replies-<?php echo $comment['id']; ?>">
                <div class="small text-muted">Memuat balasan...</div>
            </div>
        </div>
    </div>
</div>
<script>
    loadReplies(<?php echo $comment['id']; ?>);
</script>
<?php 
    endwhile;
}
$conn->close();
?>