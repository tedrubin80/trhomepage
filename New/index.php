<?php
// TR Portfolio Main Site
require_once 'Config/config.php';

$page = $_GET['page'] ?? 'projects';
$pageTitle = $page === 'projects' ? 'Projects I\'ve Built' : 'Portfolio';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - TR Portfolio | Expert Project Manager & Creative Producer</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Ted Rubin - Expert Project Manager & Creative Producer specializing in film production, digital solutions, and technical project management. Delivering exceptional results on time and within budget.">
    <meta name="keywords" content="project manager, film producer, digital project management, creative producer, technical producer, project coordination, team leadership, budget management, timeline management, agile project management, creative projects, film production management">
    <meta name="author" content="Ted Rubin">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?= $pageTitle ?> - TR Portfolio | Expert Project Manager">
    <meta property="og:description" content="Expert Project Manager & Creative Producer delivering exceptional results in film, digital, and technical projects. Master of organized chaos with a people-first approach.">
    <meta property="og:site_name" content="TR Portfolio">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .navbar { 
            background: rgba(255,255,255,0.95) !important; 
            backdrop-filter: blur(10px); 
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .logo { 
            font-weight: bold; 
            background: linear-gradient(135deg, #667eea, #764ba2); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            background-clip: text;
        }
        
        .card { 
            background: rgba(255,255,255,0.95); 
            backdrop-filter: blur(20px); 
            border: none; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        }
        
        .btn-gradient { 
            background: linear-gradient(135deg, #667eea, #764ba2); 
            border: none; 
            color: white;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a419a);
            color: white;
            transform: translateY(-2px);
        }
        
        .page-header { 
            background: rgba(255,255,255,0.95); 
            backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .project-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }
        
        .card:hover .project-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .badge {
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
        }
        
        .nav-link.active {
            background: rgba(102, 126, 234, 0.1) !important;
            color: #667eea !important;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand logo fs-2" href="?">TR</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'projects' ? 'active' : '' ?>" href="?page=projects">
                            <i class="fas fa-project-diagram"></i> Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'portfolio' ? 'active' : '' ?>" href="?page=portfolio">
                            <i class="fas fa-folder"></i> Portfolio
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= LINKEDIN_URL ?>" target="_blank">
                            <i class="fab fa-linkedin"></i> LinkedIn
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= LINKTREE_URL ?>" target="_blank">
                            <i class="fas fa-tree"></i> Linktree
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= RESUME_URL ?>" target="_blank">
                            <i class="fas fa-file-pdf"></i> Resume
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mt-5 pt-5">
        <!-- Page Header -->
        <div class="page-header text-center p-5 rounded-3 mb-5">
            <h1 class="display-4 logo"><?= $pageTitle ?></h1>
            <p class="lead text-muted">
                <?= $page === 'projects' ? 'Expert project management delivering innovative solutions on time and within budget' : 'Explore my work across different creative domains' ?>
            </p>
        </div>

        <?php if ($page === 'projects'): ?>
            <!-- Projects Grid -->
            <div class="row g-4">
                <?php foreach (getProjects() as $project): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="project-icon">
                                    <i class="<?= $project['icon'] ?>"></i>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($project['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($project['description']) ?></p>
                                <div class="mb-3">
                                    <?php foreach (formatTags($project['tags']) as $tag): ?>
                                        <span class="badge me-1 mb-1"><?= htmlspecialchars($tag) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <a href="<?= $project['link_url'] ?>" class="btn btn-gradient">
                                    <?= htmlspecialchars($project['link_text']) ?> <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- Portfolio Sections -->
            <?php 
            $sections = [
                ['websites', 'fas fa-globe', 'Websites', 'Modern web applications, corporate websites, and interactive experiences'],
                ['digital', 'fas fa-laptop-code', 'Digital', 'Innovative digital products, mobile applications, and interactive digital experiences'],
                ['films', 'fas fa-film', 'Films', 'Film production work, from short films to feature projects and documentary work']
            ];
            
            foreach ($sections as [$category, $icon, $title, $description]): 
                $items = getPortfolioItems($category);
            ?>
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-gradient text-white rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea, #764ba2) !important;">
                                <i class="<?= $icon ?> fs-5"></i>
                            </div>
                            <div>
                                <h2 class="mb-0"><?= $title ?></h2>
                                <p class="text-muted mb-0"><?= $description ?></p>
                            </div>
                        </div>
                        
                        <?php if (empty($items)): ?>
                            <div class="text-center py-5 bg-light rounded">
                                <i class="<?= $icon ?> fs-1 text-muted mb-3"></i>
                                <h5><?= $title ?> Coming Soon</h5>
                                <p class="text-muted">Check back soon for a showcase of <?= strtolower($description) ?>.</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($items as $item): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card">
                                            <?php if ($item['image_url']): ?>
                                                <img src="<?= htmlspecialchars($item['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['title']) ?>" style="height: 200px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h6 class="card-title"><?= htmlspecialchars($item['title']) ?></h6>
                                                <p class="card-text small"><?= htmlspecialchars(truncateText($item['description'], 120)) ?></p>
                                                <div class="mb-2">
                                                    <?php foreach (formatTags($item['tags']) as $tag): ?>
                                                        <span class="badge badge-sm me-1"><?= htmlspecialchars($tag) ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php if ($item['link_url']): ?>
                                                    <a href="<?= $item['link_url'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="fas fa-external-link-alt"></i> View
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 TR Portfolio. Made with passion for great design.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Header scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html>