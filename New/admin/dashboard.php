<?php
// TR Portfolio Admin Dashboard
session_start();
require_once '../Config/config.php';

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Check session timeout (1 hour)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 3600)) {
    session_destroy();
    header('Location: index.php?timeout=1');
    exit;
}

$message = '';
$messageType = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add_project':
                // Check project limit (max 10)
                $countStmt = $pdo->query("SELECT COUNT(*) FROM projects");
                if ($countStmt->fetchColumn() >= 10) {
                    throw new Exception('Maximum of 10 projects allowed');
                }
                
                $tags = array_filter(array_map('trim', explode(',', $_POST['tags'])));
                $stmt = $pdo->prepare("INSERT INTO projects (title, description, icon, tags, link_url, link_text, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['icon'],
                    json_encode($tags),
                    $_POST['link_url'],
                    $_POST['link_text'],
                    (int)$_POST['display_order']
                ]);
                $message = 'Project added successfully!';
                $messageType = 'success';
                break;
                
            case 'delete_project':
                $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = 'Project deleted successfully!';
                $messageType = 'success';
                break;
                
            case 'toggle_project':
                $stmt = $pdo->prepare("UPDATE projects SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = 'Project status updated!';
                $messageType = 'success';
                break;
                
            case 'add_portfolio':
                // Check portfolio limit per category (max 10)
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM portfolio_items WHERE category = ?");
                $countStmt->execute([$_POST['category']]);
                if ($countStmt->fetchColumn() >= 10) {
                    throw new Exception('Maximum of 10 items per portfolio category allowed');
                }
                
                $tags = array_filter(array_map('trim', explode(',', $_POST['tags'])));
                $stmt = $pdo->prepare("INSERT INTO portfolio_items (category, title, description, image_url, link_url, tags, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['category'],
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['image_url'],
                    $_POST['link_url'],
                    json_encode($tags),
                    (int)$_POST['display_order']
                ]);
                $message = 'Portfolio item added successfully!';
                $messageType = 'success';
                break;
                
            case 'delete_portfolio':
                $stmt = $pdo->prepare("DELETE FROM portfolio_items WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = 'Portfolio item deleted successfully!';
                $messageType = 'success';
                break;
                
            case 'toggle_portfolio':
                $stmt = $pdo->prepare("UPDATE portfolio_items SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = 'Portfolio item status updated!';
                $messageType = 'success';
                break;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Get data for display
$projects = $pdo->query("SELECT * FROM projects ORDER BY display_order ASC, created_at DESC")->fetchAll();
$portfolioItems = $pdo->query("SELECT * FROM portfolio_items ORDER BY category, display_order ASC, created_at DESC")->fetchAll();

// Get counts
$projectCount = count($projects);
$portfolioCount = count($portfolioItems);
$activeProjects = count(array_filter($projects, fn($p) => $p['is_active']));
$activePortfolio = count(array_filter($portfolioItems, fn($p) => $p['is_active']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TR Portfolio - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .nav-link { color: rgba(255,255,255,0.8); transition: all 0.3s ease; }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
        .card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-card { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-gradient { background: linear-gradient(135deg, #667eea, #764ba2); border: none; }
        .table th { border-top: none; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar px-0">
                <div class="p-3">
                    <h4 class="text-white">TR Admin</h4>
                    <small class="text-white-50">Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></small>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="#overview" data-bs-toggle="tab">
                        <i class="fas fa-tachometer-alt me-2"></i> Overview
                    </a>
                    <a class="nav-link" href="#projects" data-bs-toggle="tab">
                        <i class="fas fa-project-diagram me-2"></i> Projects
                    </a>
                    <a class="nav-link" href="#portfolio" data-bs-toggle="tab">
                        <i class="fas fa-folder me-2"></i> Portfolio
                    </a>
                    <hr class="text-white-50">
                    <a class="nav-link" href="../" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i> View Site
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="tab-content">
                        <!-- Overview Tab -->
                        <div class="tab-pane fade show active" id="overview">
                            <h2>Dashboard Overview</h2>
                            <p class="text-muted">Manage your portfolio content efficiently</p>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <div class="card stat-card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-project-diagram fs-1 mb-2"></i>
                                            <h3><?= $projectCount ?></h3>
                                            <p class="mb-0">Total Projects</p>
                                            <small><?= $activeProjects ?> active</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card stat-card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-folder fs-1 mb-2"></i>
                                            <h3><?= $portfolioCount ?></h3>
                                            <p class="mb-0">Portfolio Items</p>
                                            <small><?= $activePortfolio ?> active</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-eye fs-1 mb-2 text-primary"></i>
                                            <h3>Live</h3>
                                            <p class="mb-0">Site Status</p>
                                            <a href="../" target="_blank" class="btn btn-sm btn-outline-primary">View Site</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-clock fs-1 mb-2 text-info"></i>
                                            <h3>Recent</h3>
                                            <p class="mb-0">Last Login</p>
                                            <small class="text-muted"><?= date('M j, Y g:i A') ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5><i class="fas fa-chart-bar me-2"></i>Quick Stats</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Projects Limit:</span>
                                                <span><?= $projectCount ?>/10</span>
                                            </div>
                                            <div class="progress mb-3">
                                                <div class="progress-bar" style="width: <?= ($projectCount/10)*100 ?>%"></div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Portfolio Items:</span>
                                                <span><?= $portfolioCount ?>/30</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-info" style="width: <?= ($portfolioCount/30)*100 ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5><i class="fas fa-link me-2"></i>External Links</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-grid gap-2">
                                                <a href="<?= LINKEDIN_URL ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="fab fa-linkedin me-2"></i>LinkedIn Profile
                                                </a>
                                                <a href="<?= LINKTREE_URL ?>" target="_blank" class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-tree me-2"></i>Linktree
                                                </a>
                                                <a href="<?= RESUME_URL ?>" target="_blank" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-file-pdf me-2"></i>Resume
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Projects Tab -->
                        <div class="tab-pane fade" id="projects">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h2>Manage Projects</h2>
                                    <p class="text-muted"><?= $projectCount ?>/10 projects used</p>
                                </div>
                                <?php if ($projectCount < 10): ?>
                                    <button class="btn btn-gradient text-white" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                                        <i class="fas fa-plus me-2"></i>Add Project
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="fas fa-ban me-2"></i>Limit Reached (10/10)
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="card">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th width="5%">Order</th>
                                                <th width="25%">Title</th>
                                                <th width="35%">Description</th>
                                                <th width="15%">Tags</th>
                                                <th width="10%">Status</th>
                                                <th width="10%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($projects as $project): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-secondary"><?= $project['display_order'] ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="<?= $project['icon'] ?> me-2 text-primary"></i>
                                                            <strong><?= htmlspecialchars($project['title']) ?></strong>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <small><?= htmlspecialchars(substr($project['description'], 0, 100)) ?>...</small>
                                                    </td>
                                                    <td>
                                                        <?php foreach (formatTags($project['tags']) as $tag): ?>
                                                            <span class="badge bg-light text-dark me-1"><?= htmlspecialchars($tag) ?></span>
                                                        <?php endforeach; ?>
                                                    </td>
                                                    <td>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="toggle_project">
                                                            <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                                            <button type="submit" class="btn btn-sm <?= $project['is_active'] ? 'btn-success' : 'btn-secondary' ?>">
                                                                <?= $project['is_active'] ? 'Active' : 'Inactive' ?>
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="delete_project">
                                                            <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this project?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Portfolio Tab -->
                        <div class="tab-pane fade" id="portfolio">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h2>Manage Portfolio</h2>
                                    <p class="text-muted"><?= $portfolioCount ?> total items across all categories</p>
                                </div>
                                <button class="btn btn-gradient text-white" data-bs-toggle="modal" data-bs-target="#addPortfolioModal">
                                    <i class="fas fa-plus me-2"></i>Add Portfolio Item
                                </button>
                            </div>

                            <?php
                            $categories = ['websites', 'digital', 'films'];
                            foreach ($categories as $category):
                                $categoryItems = array_filter($portfolioItems, fn($item) => $item['category'] === $category);
                                $categoryCount = count($categoryItems);
                            ?>
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-<?= $category === 'websites' ? 'globe' : ($category === 'digital' ? 'laptop-code' : 'film') ?> me-2"></i>
                                            <?= ucfirst($category) ?> (<?= $categoryCount ?>/10)
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($categoryItems)): ?>
                                            <div class="text-center py-4 text-muted">
                                                <i class="fas fa-folder-open fs-1 mb-3"></i>
                                                <p>No <?= $category ?> items yet. Add your first one!</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Order</th>
                                                            <th>Title</th>
                                                            <th>Description</th>
                                                            <th>Status</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($categoryItems as $item): ?>
                                                            <tr>
                                                                <td><span class="badge bg-secondary"><?= $item['display_order'] ?></span></td>
                                                                <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                                                <td><small><?= htmlspecialchars(substr($item['description'], 0, 80)) ?>...</small></td>
                                                                <td>
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="action" value="toggle_portfolio">
                                                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                                        <button type="submit" class="btn btn-sm <?= $item['is_active'] ? 'btn-success' : 'btn-secondary' ?>">
                                                                            <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                                <td>
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="action" value="delete_portfolio">
                                                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Project Modal -->
    <div class="modal fade" id="addProjectModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_project">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Project Title</label>
                                    <input type="text" class="form-control" name="title" required maxlength="200">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" class="form-control" name="display_order" value="0" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Icon Class (FontAwesome)</label>
                                    <input type="text" class="form-control" name="icon" value="fas fa-project-diagram" required>
                                    <small class="text-muted">e.g., fas fa-magic, fas fa-chart-line</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Link Text</label>
                                    <input type="text" class="form-control" name="link_text" value="View Project" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tags (comma separated)</label>
                            <input type="text" class="form-control" name="tags" placeholder="Project Management, Team Leadership, Quality Assurance">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link URL</label>
                            <input type="url" class="form-control" name="link_url" placeholder="https://example.com">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-gradient text-white">Add Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Portfolio Modal -->
    <div class="modal fade" id="addPortfolioModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Portfolio Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_portfolio">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category" required>
                                        <option value="websites">Websites</option>
                                        <option value="digital">Digital</option>
                                        <option value="films">Films</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" class="form-control" name="display_order" value="0" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required maxlength="200">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image URL (optional)</label>
                            <input type="url" class="form-control" name="image_url" placeholder="https://example.com/image.jpg">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link URL (optional)</label>
                            <input type="url" class="form-control" name="link_url" placeholder="https://example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tags (comma separated)</label>
                            <input type="text" class="form-control" name="tags" placeholder="Web Design, Responsive, Modern">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-gradient text-white">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Tab persistence
        const activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            const tabElement = document.querySelector(`a[href="${activeTab}"]`);
            if (tabElement) {
                const tab = new bootstrap.Tab(tabElement);
                tab.show();
            }
        }
        
        // Save active tab
        document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                localStorage.setItem('activeTab', e.target.getAttribute('href'));
            });
        });
    </script>
</body>
</html>