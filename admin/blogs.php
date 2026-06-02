<?php
// admin/blogs.php
// Blog post CRUD dashboard manager

require_once __DIR__ . '/../models/Admin.php';
Admin::protect();
if (!Admin::hasPermission('blogs')) {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../models/Blog.php';

$blogModel = new Blog();
$error = '';
$success = '';

$action = isset($_GET['op']) ? sanitize_input($_GET['op']) : 'list';
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$edit_blog = null;

if ($edit_id > 0) {
    $edit_blog = $blogModel->getById($edit_id);
}

// Process CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = sanitize_input($_POST['operation'] ?? '');
    
    $title = sanitize_input($_POST['title'] ?? '');
    $summary = sanitize_input($_POST['summary'] ?? '');
    // Content might contain HTML from text editor, sanitize tags but keep markup structures
    $content = $_POST['content'] ?? '';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Manage image file upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_path'] ?? $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileType = $_FILES['image']['type'];
        
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            // Secure filename generation
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = UPLOAD_DIR . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_url = 'uploads/' . $newFileName;
            } else {
                $error = "There was an error moving the uploaded banner image.";
            }
        } else {
            $error = "Upload failed. Allowed image extensions: JPG, JPEG, PNG, WEBP, GIF.";
        }
    }

    if (empty($error)) {
        $title_length = strlen(trim($title));

        if ($op === 'add') {
            if ($title_length > 120) {
                $error = "Blog Title cannot exceed 120 characters.";
            } elseif (empty($title) || empty($summary) || empty($content)) {
                $error = "Please fill in all the required fields.";
            } elseif (empty($image_url)) {
                $error = "A banner image is strictly required to publish a new blog post.";
            } else {
                $img = $image_url !== null ? $image_url : null;
                if ($blogModel->create($title, $summary, $content, $img, $is_featured)) {
                    $success = "Blog post created successfully!";
                    $action = 'list';
                } else {
                    $error = "Failed to create blog post. Please check database logs.";
                }
            }
        } elseif ($op === 'edit' && $edit_id > 0) {
            if ($title_length > 120) {
                $error = "Blog Title cannot exceed 120 characters.";
            } elseif (empty($title) || empty($summary) || empty($content)) {
                $error = "Please fill in all the required fields.";
            } else {
                if ($blogModel->update($edit_id, $title, $summary, $content, $image_url, $is_featured)) {
                    $success = "Blog post updated successfully!";
                    $action = 'list';
                } else {
                    $error = "Failed to update blog post.";
                }
            }
        }
    }
}

// Process direct Delete operations
if ($action === 'delete' && $edit_id > 0) {
    if ($blogModel->delete($edit_id)) {
        $success = "Blog post deleted successfully!";
    } else {
        $error = "Failed to delete blog post.";
    }
    $action = 'list';
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 7;
$offset = ($page - 1) * $limit;
$totalRecords = $blogModel->count();
$totalPages = ceil($totalRecords / $limit);

$allBlogs = $blogModel->getAll($limit, $offset);

include_once __DIR__ . '/admin_header.php';
?>

<!-- Message displays -->
<?php if (!empty($error)): ?>
  <div style="background:#f8d7da;color:#721c24;padding:15px;border-radius:10px;margin-bottom:20px;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
  <div style="background:#d4edda;color:#155724;padding:15px;border-radius:10px;margin-bottom:20px;"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- ── LIST VIEW ── -->
<?php if ($action === 'list'): ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Active Blog Posts</h2>
      <a href="blogs.php?op=add" class="btn-admin-primary">Write New Post</a>
    </div>

    <table class="admin-table">
      <thead>
        <tr>
          <th>Banner</th>
          <th>Blog Title</th>
          <th>Summary preview</th>
          <th>Date Published</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($allBlogs)): ?>
          <?php foreach ($allBlogs as $b): ?>
              <tr id="blog-row-<?= $b['id'] ?>">
              <td><img src="../<?= htmlspecialchars($b['image_url']) ?>" style="width:60px; height:45px; border-radius:6px; object-fit:cover;" alt="banner"></td>
              <td><strong><?= htmlspecialchars($b['title']) ?></strong></td>
              <td><small><?= htmlspecialchars(substr($b['summary'], 0, 75)) ?>...</small></td>
              <td><?= date("M j, Y", strtotime($b['created_at'])) ?></td>
              <td>
                <a href="blogs.php?op=edit&id=<?= $b['id'] ?>" class="btn-admin-action edit">Edit</a>
                <a href="blogs.php?op=delete&id=<?= $b['id'] ?>" class="btn-admin-action delete" onclick="delayedDelete(event, this.href, 'blog-row-<?= $b['id'] ?>', 'Blog post')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center; color: #666; padding: 20px;">No blog posts published yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
    <div style="padding: 20px; display: flex; justify-content: center; gap: 8px; align-items: center;">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" style="width: 12px; height: 12px; border-radius: 50%; display: inline-block; background-color: <?= $i === $page ? '#ff6b00' : '#ddd' ?>; transition: 0.3s;" title="Page <?= $i ?>"></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

  </div>

<!-- ── ADD OR EDIT VIEW ── -->
<?php elseif ($action === 'add' || ($action === 'edit' && $edit_blog)): ?>
  <!-- Include Quill library -->
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

  <div class="admin-card" style="max-width: 800px;">
    <h2><?= $action === 'add' ? 'Publish a New Blog Post' : 'Edit Post: ' . htmlspecialchars($edit_blog['title']) ?></h2>
    
    <script>
    let quill;

    function validateBlogWords(form) {
      const title = document.getElementById('title');
      const summary = document.getElementById('summary');
      const contentInput = document.getElementById('content');

      if (quill && contentInput) {
        // Only set HTML if there is actual text
        if (quill.getText().trim().length === 0) {
            contentInput.value = '';
        } else {
            contentInput.value = quill.root.innerHTML;
        }
      }

      if (title && title.value.trim().length > 120) {
        Swal.fire({
          title: 'Error',
          text: 'Blog Title cannot exceed 120 characters.',
          icon: 'error',
          confirmButtonColor: '#ff6b00'
        });
        return false;
      }

      if (summary && summary.value.trim().split(/\s+/).filter(w => w.length > 0).length > 50) {
        Swal.fire({
          title: 'Error',
          text: 'Short Summary cannot exceed 50 words.',
          icon: 'error',
          confirmButtonColor: '#ff6b00'
        });
        return false;
      }
      if (quill) {
        const plainText = quill.getText().trim();
        const wordCount = plainText.length > 0 ? plainText.split(/\s+/).length : 0;
        if (wordCount > 800) {
          Swal.fire({
            title: 'Error',
            text: 'Body Content cannot exceed 800 words.',
            icon: 'error',
            confirmButtonColor: '#ff6b00'
          });
          return false;
        }
      }
      return true;
    }

    document.addEventListener("DOMContentLoaded", function() {
      // Initialize Quill Editor
      quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Write your full story here...',
        modules: {
          toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link', 'image', 'video'],
            ['clean']
          ]
        }
      });
      const imageInput = document.getElementById('image');
      if (imageInput) {
        imageInput.addEventListener('change', function(e) {
          const file = e.target.files[0];
          if (!file) return;

          const img = new Image();
          img.onload = function() {
            const ratio = this.width / this.height;
            // Flexible image ratio, no strict rejection
            if (ratio < 1.0) {
              console.log("Image is portrait, but allowing flexible upload.");
            }
          };
          img.src = URL.createObjectURL(file);
        });
      }
    });
    </script>
    <form method="POST" action="blogs.php<?= $action === 'edit' ? '?op=edit&id=' . $edit_id : '' ?>" enctype="multipart/form-data" style="margin-top: 25px;" onsubmit="return validateBlogWords(this);">
      <input type="hidden" name="operation" value="<?= $action ?>">
      
      <div class="admin-form-group">
        <label for="title">Blog Post Title</label>
        <input type="text" id="title" name="title" required maxlength="120" placeholder="e.g. Empowering Youth in Accra (Max 120)" value="<?= $action === 'edit' ? htmlspecialchars($edit_blog['title']) : '' ?>">
      </div>

      <div class="admin-form-group">
        <label for="summary">Short Summary (Featured Snippet)</label>
        <textarea id="summary" name="summary" rows="3" required placeholder="Provide a brief summary for page previews..." style="resize:vertical;"><?= $action === 'edit' ? htmlspecialchars($edit_blog['summary']) : '' ?></textarea>
      </div>

      <div class="admin-form-group">
        <label for="content">Body Content</label>
        <input type="hidden" id="content" name="content">
        <div id="editor-container" style="height: 350px; background: #fff; font-family: 'Poppins', sans-serif; font-size: 15px; border-radius: 0 0 10px 10px;">
          <?= $action === 'edit' ? $edit_blog['content'] : '' ?>
        </div>
      </div>

      <div class="admin-form-group">
        <label for="image">Upload Banner Image (Landscape / 16:9 recommended)</label>
        <?php if ($action === 'edit' && !empty($edit_blog['image_url'])): ?>
          <div style="margin-bottom:10px;"><img src="../<?= htmlspecialchars($edit_blog['image_url']) ?>" style="width:120px;border-radius:8px;" alt="current"></div>
        <?php endif; ?>
        <input type="file" id="image" name="image" <?= $action === 'add' ? 'required' : '' ?>>
      </div>

      <div style="margin-top: 30px;">
        <button type="submit" class="btn-admin-primary"><?= $action === 'add' ? 'PUBLISH NOW' : 'SAVE CHANGES' ?></button>
        <a href="blogs.php" class="btn-admin-action" style="background:#eee;color:#333;padding:12px 24px;border-radius:8px;font-size:14px;">Cancel</a>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php
include_once __DIR__ . '/admin_footer.php';
?>
