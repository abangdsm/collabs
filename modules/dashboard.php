<?php
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Dashboard';
include '../includes/header.php';

// Cek deadline otomatis
checkDeadlines();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Ambil semua tasks dengan subtasks-nya
$tasks = $conn->query("
    SELECT t.*, u.username as creator 
    FROM tasks t
    JOIN users u ON t.created_by = u.id
    ORDER BY t.created_at DESC
");

$base_url = base_url();
?>

<!-- Tampilkan pesan sukses/error -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-grid me-2"></i>Dashboard</h2>
    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalTask">
        <i class="bi bi-plus-lg"></i> Buat Judul Tugas Baru
    </button>
</div>

<!-- FILTER SECTION -->
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-3">
                <select class="form-select" id="filterStatus">
                    <option value="">Semua Status</option>
                    <option value="proses">Dalam Proses</option>
                    <option value="selesai">Selesai</option>
                    <option value="evaluasi">Evaluasi</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterPriority">
                    <option value="">Semua Prioritas</option>
                    <option value="high">🔴 High</option>
                    <option value="medium">🟡 Medium</option>
                    <option value="low">🟢 Low</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterDeadline">
                    <option value="">Semua Deadline</option>
                    <option value="today">Hari ini</option>
                    <option value="tomorrow">Besok</option>
                    <option value="week">Minggu ini</option>
                    <option value="overdue">Sudah lewat</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Cari tugas..." id="searchInput">
                    <button class="btn btn-dark" type="button" id="btnSearch">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tasks Container -->
<div id="tasks-container">
    <?php if ($tasks->num_rows == 0): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Belum ada judul tugas. Klik tombol "Buat Judul Tugas Baru" untuk memulai.
        </div>
    <?php else: ?>
        <?php while($task = $tasks->fetch_assoc()): ?>
        <div class="card mb-3 task-card shadow-sm" data-task-id="<?php echo $task['id']; ?>">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-folder me-2 text-primary"></i>
                    <?php echo htmlspecialchars($task['judul']); ?>
                </h5>
                <div>
                    <small class="text-muted me-3">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo $task['creator']; ?>
                    </small>
                    
                    <?php if ($_SESSION['role'] == 'admin' || $task['created_by'] == $user_id): ?>
                        <a href="tasks/edit.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($_SESSION['role'] == 'admin' || $task['created_by'] == $user_id): ?>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTask(<?php echo $task['id']; ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <!-- Subtasks akan diload via AJAX -->
                <div class="subtask-list" data-task-id="<?php echo $task['id']; ?>">
                    <div class="text-center text-muted py-3">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        Memuat daftar tugas...
                    </div>
                </div>
                
                <!-- Tombol Tambah Daftar Tugas -->
                <button class="btn btn-sm btn-outline-dark mt-3" onclick="showAddSubtask(<?php echo $task['id']; ?>)">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Daftar Tugas
                </button>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<!-- Modal for CREATE Task -->
<div class="modal fade" id="modalTask" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Buat Judul Tugas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTask" method="POST" action="tasks/create.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Tugas</label>
                        <input type="text" class="form-control" id="judul" name="judul" required 
                               placeholder="Contoh: Pengembangan Fitur A">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for CREATE Subtask -->
<div class="modal fade" id="modalSubtask" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Daftar Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSubtask" method="POST" action="subtasks/create.php">
                <div class="modal-body">
                    <input type="hidden" id="subtask_task_id" name="task_id">
                    
                    <div class="mb-3">
                        <label for="subtask_judul" class="form-label">Judul Daftar Tugas</label>
                        <input type="text" class="form-control" id="subtask_judul" name="judul_sub" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subtask_deskripsi" class="form-label">Deskripsi & Skill yang Dibutuhkan</label>
                        <textarea class="form-control" id="subtask_deskripsi" name="deskripsi" rows="3" placeholder="Jelaskan detail tugas dan skill yang diperlukan..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="subtask_priority" class="form-label">Prioritas</label>
                            <select class="form-select" id="subtask_priority" name="priority">
                                <option value="low">🟢 Low</option>
                                <option value="medium" selected>🟡 Medium</option>
                                <option value="high">🔴 High</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="subtask_deadline" class="form-label">Deadline</label>
                            <input type="date" class="form-control" id="subtask_deadline" name="deadline">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="subtask_status" class="form-label">Status</label>
                            <select class="form-select" id="subtask_status" name="status">
                                <option value="proses" selected>Dalam Proses</option>
                                <option value="selesai">Selesai</option>
                                <option value="evaluasi">Evaluasi</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subtask_link" class="form-label">Link Eksternal (Opsional)</label>
                        <input type="url" class="form-control" id="subtask_link" name="link" 
                               placeholder="https://drive.google.com/...">
                        <small class="text-muted">Link ke Google Drive atau dokumen eksternal</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPT DASHBOARD -->
<?php 
$conn->close();

$additional_scripts = '
<script>
// ============================================
// SEMUA FUNGSI JAVASCRIPT UNTUK DASHBOARD
// ============================================
$(document).ready(function() {
    console.log("Dashboard ready - jQuery is working!");
    
    // Load subtasks untuk setiap task
    $(".subtask-list").each(function() {
        var taskId = $(this).data("task-id");
        loadSubtasks(taskId);
    });
    
    // FILTER REALTIME
    var searchTimeout;
    
    // Filter saat mengetik di search (dengan delay)
    $("#searchInput").on("keyup", function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 500); // Delay 500ms biar gak terlalu banyak request
    });
    
    // Filter saat dropdown berubah
    $("#filterStatus, #filterPriority, #filterDeadline").on("change", function() {
        applyFilters();
    });
    
    // Filter saat tombol search diklik
    $("#btnSearch").on("click", function() {
        applyFilters();
    });
    
    // Filter saat enter di search
    $("#searchInput").on("keypress", function(e) {
        if (e.which == 13) {
            e.preventDefault();
            applyFilters();
        }
    });
    
    // Reset form modal ketika ditutup
    $("#modalSubtask").on("hidden.bs.modal", function () {
        $("#formSubtask")[0].reset();
    });
});

// ============================================
// FUNGSI LOAD SUBTASKS
// ============================================
function loadSubtasks(taskId) {
    $.ajax({
        url: baseUrl + "/api/get_subtasks.php",
        data: { task_id: taskId },
        method: "GET",
        dataType: "html",
        timeout: 10000,
        success: function(data) {
            $(".subtask-list[data-task-id=\"" + taskId + "\"]").html(data);
            
            // Load comments untuk setiap subtask
            $(".comments-container").each(function() {
                var subtaskId = $(this).data("subtask-id");
                if (subtaskId) {
                    loadComments(subtaskId);
                }
            });
            
            // INIT DRAG & DROP UNTUK TASK INI
            initDragDrop(taskId);
        },
        error: function(xhr, status, error) {
            console.error("Error load subtasks:", error);
            $(".subtask-list[data-task-id=\"" + taskId + "\"]").html(
                "<div class=\"text-danger p-2\">" +
                "<i class=\"bi bi-exclamation-triangle me-2\"></i>" +
                "Gagal memuat subtasks. " +
                "<button class=\"btn btn-sm btn-link p-0 ms-2\" onclick=\"loadSubtasks(" + taskId + ")\">Coba lagi</button>" +
                "</div>"
            );
        }
    });
}

// ============================================
// FUNGSI LOAD COMMENTS
// ============================================
function loadComments(subtaskId) {
    $.ajax({
        url: baseUrl + "/api/get_comments.php",
        data: { subtask_id: subtaskId },
        method: "GET",
        dataType: "html",
        timeout: 5000,
        success: function(data) {
            $("#comments-" + subtaskId).html(data);
        },
        error: function(xhr, status, error) {
            console.error("Error load comments:", error);
            $("#comments-" + subtaskId).html(
                "<div class=\"text-danger small p-1\">" +
                "<i class=\"bi bi-exclamation-triangle me-1\"></i>" +
                "Gagal memuat komentar" +
                "</div>"
            );
        }
    });
}

// ============================================
// FUNGSI LOAD REPLIES
// ============================================
function loadReplies(parentId) {
    $.ajax({
        url: baseUrl + "/api/get_replies.php",
        data: { parent_id: parentId },
        method: "GET",
        dataType: "html",
        timeout: 5000,
        success: function(data) {
            $("#replies-" + parentId).html(data);
        },
        error: function(xhr, status, error) {
            console.error("Error load replies:", error);
            $("#replies-" + parentId).html("");
        }
    });
}

// ============================================
// FUNGSI DRAG & DROP UNTUK SUBTASKS
// ============================================
function initDragDrop(taskId) {
    var container = $(".subtask-list[data-task-id=\"" + taskId + "\"]");
    
    container.sortable({
        items: ".subtask-item",
        handle: ".drag-handle",
        cursor: "grabbing",
        opacity: 0.6,
        placeholder: "sortable-placeholder",
        tolerance: "pointer",
        update: function(event, ui) {
            // Ambil urutan baru
            var subtaskIds = [];
            $(this).find(".subtask-item").each(function() {
                subtaskIds.push($(this).data("subtask-id"));
            });
            
            console.log("Urutan baru:", subtaskIds);
            
            // Kirim ke server
            $.ajax({
                url: baseUrl + "/api/update_subtask_order.php",
                method: "POST",
                data: {
                    task_id: taskId,
                    order: JSON.stringify(subtaskIds)
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        console.log("Urutan berhasil disimpan");
                        showNotification("Urutan subtasks berhasil diupdate", "success");
                    } else {
                        console.error("Gagal simpan urutan:", response.message);
                        showNotification("Gagal menyimpan urutan", "danger");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error);
                    showNotification("Gagal menyimpan urutan", "danger");
                }
            });
        }
    });
    
    container.disableSelection();
}

// ============================================
// FUNGSI SHOW/HIDE REPLY FORM
// ============================================
function showReplyForm(commentId) {
    $("#reply-form-" + commentId).slideDown(200);
}

function hideReplyForm(commentId) {
    $("#reply-form-" + commentId).slideUp(200);
}

// ============================================
// FUNGSI SHOW ADD SUBTASK
// ============================================
function showAddSubtask(taskId) {
    $("#subtask_task_id").val(taskId);
    $("#modalSubtask").modal("show");
}

// ============================================
// FUNGSI DELETE TASK
// ============================================
function deleteTask(taskId) {
    if(confirm("Yakin ingin menghapus judul tugas ini? Semua daftar tugas di dalamnya juga akan ikut terhapus!")) {
        $.ajax({
            url: baseUrl + "/api/delete_task.php",
            method: "POST",
            data: { task_id: taskId },
            dataType: "json",
            timeout: 10000,
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert("Gagal: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert("Terjadi kesalahan: " + error);
            }
        });
    }
}

// ============================================
// FUNGSI DELETE SUBTASK
// ============================================
function deleteSubtask(subtaskId) {
    if(confirm("Yakin ingin menghapus daftar tugas ini?")) {
        $.ajax({
            url: baseUrl + "/api/delete_subtask.php",
            method: "POST",
            data: { subtask_id: subtaskId },
            dataType: "json",
            timeout: 10000,
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert("Gagal: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert("Terjadi kesalahan: " + error);
            }
        });
    }
}

// ============================================
// FUNGSI SUBMIT KOMENTAR UTAMA
// ============================================
$(document).on("submit", ".add-comment-form", function(e) {
    e.preventDefault();
    
    var form = $(this);
    var subtaskId = form.data("subtask-id");
    var komentar = form.find("input[name=\"komentar\"]").val();
    var link = form.find("input[name=\"link\"]").val();
    
    if (!komentar.trim()) {
        alert("Komentar tidak boleh kosong");
        return;
    }
    
    var submitBtn = form.find("button[type=\"submit\"]");
    submitBtn.prop("disabled", true).html("<span class=\"spinner-border spinner-border-sm\"></span>");
    
    $.ajax({
        url: baseUrl + "/modules/comments/create.php",
        method: "POST",
        data: {
            subtask_id: subtaskId,
            komentar: komentar,
            link: link
        },
        dataType: "json",
        timeout: 10000,
        success: function(response) {
            if (response.success) {
                form.find("input[name=\"komentar\"]").val("");
                form.find("input[name=\"link\"]").val("");
                loadComments(subtaskId);
                showNotification("Komentar berhasil ditambahkan", "success");
            } else {
                alert("Gagal: " + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error submit komentar:", error);
            alert("Terjadi kesalahan: " + error);
        },
        complete: function() {
            submitBtn.prop("disabled", false).html("Kirim");
        }
    });
});

// ============================================
// FUNGSI SUBMIT BALASAN
// ============================================
$(document).on("submit", ".reply-form", function(e) {
    e.preventDefault();
    
    var form = $(this);
    var parentId = form.data("parent-id");
    var subtaskId = form.data("subtask-id");
    var komentar = form.find("input[name=\"komentar\"]").val();
    
    if (!komentar.trim()) {
        alert("Balasan tidak boleh kosong");
        return;
    }
    
    var submitBtn = form.find("button[type=\"submit\"]");
    submitBtn.prop("disabled", true).html("<span class=\"spinner-border spinner-border-sm\"></span>");
    
    $.ajax({
        url: baseUrl + "/modules/comments/create.php",
        method: "POST",
        data: {
            subtask_id: subtaskId,
            komentar: komentar,
            parent_id: parentId
        },
        dataType: "json",
        timeout: 10000,
        success: function(response) {
            if (response.success) {
                form.find("input[name=\"komentar\"]").val("");
                hideReplyForm(parentId);
                loadReplies(parentId);
                showNotification("Balasan berhasil dikirim", "success");
            } else {
                alert("Gagal: " + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error submit balasan:", error);
            alert("Terjadi kesalahan: " + error);
        },
        complete: function() {
            submitBtn.prop("disabled", false).html("Kirim");
        }
    });
});

// ============================================
// FUNGSI NOTIFIKASI
// ============================================
function showNotification(message, type) {
    // Tentukan class dan icon berdasarkan type
    var bgClass = "";
    var icon = "";
    
    switch(type) {
        case "success":
            bgClass = "alert-success";
            icon = "bi-check-circle";
            break;
        case "danger":
            bgClass = "alert-danger";
            icon = "bi-exclamation-triangle";
            break;
        case "warning":
            bgClass = "alert-warning";
            icon = "bi-exclamation-circle";
            break;
        default:
            bgClass = "alert-info";
            icon = "bi-info-circle";
    }
    
    var alertHtml = "<div class=\"alert " + bgClass + " alert-dismissible fade show alert-sm py-2\" role=\"alert\">" +
                    "<i class=\"bi " + icon + " me-2\"></i>" + message +
                    "<button type=\"button\" class=\"btn-close btn-sm\" data-bs-dismiss=\"alert\"></button>" +
                    "</div>";
    $("#tasks-container").before(alertHtml);
    
    setTimeout(function() {
        $(".alert").alert("close");
    }, 3000);
}

// ============================================
// FUNGSI APPLY FILTERS - REALTIME
// ============================================
function applyFilters() {
    var status = $("#filterStatus").val();
    var priority = $("#filterPriority").val();
    var deadline = $("#filterDeadline").val();
    var search = $("#searchInput").val();
    
    console.log("Filter:", {status, priority, deadline, search});
    
    // Tampilkan loading
    $("#tasks-container").html(
        "<div class=\"text-center py-5\">" +
        "<div class=\"spinner-border text-primary\" role=\"status\"></div>" +
        "<p class=\"mt-2 text-muted\">Memfilter tugas...</p>" +
        "</div>"
    );
    
    $.ajax({
        url: baseUrl + "/api/filter_tasks.php",
        data: {
            status: status,
            priority: priority,
            deadline: deadline,
            search: search
        },
        dataType: "json",
        timeout: 10000,
        success: function(response) {
            console.log("Filter response:", response);
            
            // Update tasks container dengan HTML baru
            $("#tasks-container").html(response.tasks_html);
            
            // Load subtasks untuk setiap task yang baru
            $(".subtask-list").each(function() {
                var taskId = $(this).data("task-id");
                loadSubtasks(taskId);
            });
            
            // Tampilkan notifikasi hasil filter
            if (response.subtasks_count > 0) {
                showNotification("Ditemukan " + response.subtasks_count + " tugas yang sesuai", "info");
            } else {
                showNotification("Tidak ada tugas yang sesuai dengan filter", "warning");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error filter:", error);
            showNotification("Gagal memfilter tugas", "danger");
            
            // Reload halaman sebagai fallback
            location.reload();
        }
    });
}
</script>
';

include '../includes/footer.php'; 
?>