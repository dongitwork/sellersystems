<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?? 'User Management' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .navbar {
            background: var(--primary-gradient);
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        
        .sidebar .nav-link {
            color: #495057;
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
        }
        
        .sidebar .nav-link:hover {
            background: #e9ecef;
            color: #212529;
        }
        
        .sidebar .nav-link.active {
            background: var(--primary-gradient);
            color: white;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        
        .badge {
            padding: 0.35em 0.65em;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <i class="bi bi-shield-check"></i> <?= $_ENV['APP_NAME'] ?? 'User Management' ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= auth()['name'] ?? 'User' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/profile">
                                <i class="bi bi-person"></i> Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="/logout">
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar py-3">
                <nav class="nav flex-column">
                    <a href="/dashboard" class="nav-link <?= $_SERVER['REQUEST_URI'] == '/dashboard' ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    
                    <?php if(hasRole('admin')): ?>
                    <a href="/users" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/users') ? 'active' : '' ?>">
                        <i class="bi bi-people"></i> Users
                    </a>
                    <?php endif; ?>

                    <a href="/products" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/products') ? 'active' : '' ?>">
                        <i class="bi bi-box-seam"></i> Products
                    </a>

                    <a href="/profile" class="nav-link <?= $_SERVER['REQUEST_URI'] == '/profile' ? 'active' : '' ?>">
                        <i class="bi bi-person"></i> Profile
                    </a>
                    
                    <a href="/settings" class="nav-link <?= $_SERVER['REQUEST_URI'] == '/settings' ? 'active' : '' ?>">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 py-3">
                <?= $content ?>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>