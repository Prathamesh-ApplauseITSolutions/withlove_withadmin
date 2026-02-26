<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get user info
$user_name = $_SESSION['admin_name'] ?? 'Admin User';
$user_initial = substr($user_name, 0, 1);

// Get categories for dropdown
require_once __DIR__ . '/api/config.php';

try {
    $pdo = getDatabaseConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT * FROM project_categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - With Love For You Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#ef3090',
                        secondary: '#ff6b9d',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slideUp {
            animation: slideUp 0.6s ease-out;
        }
        @media (min-width: 1024px) {
            #sidebar {
                transition: width 0.25s ease, transform 0.3s ease;
            }
            #sidebar.sidebar-collapsed {
                width: 5.5rem;
            }
            #sidebar.sidebar-collapsed .nav-text,
            #sidebar.sidebar-collapsed .sidebar-brand-title,
            #sidebar.sidebar-collapsed .sidebar-user-name {
                display: none;
            }
            #sidebar.sidebar-collapsed .sidebar-brand-wrap {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            #sidebar.sidebar-collapsed .nav-link,
            #sidebar.sidebar-collapsed #logoutBtn {
                justify-content: center;
            }
            #sidebar.sidebar-collapsed .nav-link i,
            #sidebar.sidebar-collapsed #logoutBtn i {
                margin-right: 0;
            }
            #sidebar.sidebar-collapsed #collapseSidebar {
                right: -0.75rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden hidden"></div>
    
    <!-- Sidebar -->
    <div id="sidebar" class="relative fixed inset-y-0 left-0 h-screen w-64 bg-slate-900 border-r border-slate-800 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:z-auto lg:flex-shrink-0 shadow-2xl overflow-y-auto overflow-x-hidden">
        <button id="collapseSidebar" type="button" aria-label="Collapse sidebar" class="hidden lg:flex absolute right-2 top-20 w-7 h-7 rounded-full bg-white text-slate-700 border border-slate-200 shadow items-center justify-center hover:bg-slate-100 transition-colors">
            <i id="collapseSidebarIcon" class="fas fa-angle-left text-xs"></i>
        </button>
        <div class="sidebar-brand-wrap bg-gradient-to-br from-primary to-secondary p-6 text-white text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-white/20 flex items-center justify-center mb-3">
                <i class="fas fa-heart text-2xl"></i>
            </div>
            <h3 class="sidebar-brand-title text-xl font-bold tracking-wide">With Love Admin</h3>
            <p class="sidebar-user-name text-sm opacity-95 mt-1"><?php echo htmlspecialchars($user_name); ?></p>
        </div>
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="dashboard.php" class="nav-link flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 group">
                        <i class="fas fa-tachometer-alt w-5 mr-3 text-center"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 group bg-gray-700 text-white" data-page="projects">
                        <i class="fas fa-project-diagram w-5 mr-3 text-center"></i>
                        <span class="nav-text">Projects</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 group" data-page="media">
                        <i class="fas fa-images w-5 mr-3 text-center"></i>
                        <span class="nav-text">Media Library</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 group" data-page="users">
                        <i class="fas fa-users w-5 mr-3 text-center"></i>
                        <span class="nav-text">Users</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 group" data-page="settings">
                        <i class="fas fa-cog w-5 mr-3 text-center"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </li>
                <li class="pt-8 mt-8 border-t border-gray-700">
                    <a href="#" id="logoutBtn" class="flex items-center px-4 py-3 text-red-400 hover:bg-red-600/20 hover:text-red-300 rounded-lg transition-all duration-200">
                        <i class="fas fa-sign-out-alt w-5 mr-3 text-center"></i>
                        <span class="nav-text">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-0 flex flex-col min-h-screen">
        <!-- Top Header -->
        <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center">
                    <button id="toggleSidebar" aria-label="Toggle sidebar" aria-expanded="false" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors duration-200">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="ml-4 text-xl sm:text-2xl font-bold text-gray-800">Projects</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="hidden sm:block text-sm text-gray-600"><?php echo htmlspecialchars($user_name); ?></span>
                    <div class="w-10 h-10 bg-gradient-to-r from-primary to-secondary rounded-full flex items-center justify-center text-white font-semibold text-sm">
                        <?php echo htmlspecialchars($user_initial); ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 sm:mb-8 space-y-4 sm:space-y-0">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Project Management</h2>
                    <p class="text-gray-600 mt-1">Manage your projects and content</p>
                </div>
                <button onclick="showAddProjectModal()" class="bg-gradient-to-r from-primary to-secondary text-white font-semibold px-6 py-3 rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 w-full sm:w-auto">
                    <i class="fas fa-plus mr-2"></i>Add New Project
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center">
                            <i class="fas fa-project-diagram text-2xl text-primary"></i>
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2" id="totalProjects">0</div>
                    <div class="text-sm text-gray-600">Total Projects</div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-2xl text-green-600"></i>
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2" id="publishedProjects">0</div>
                    <div class="text-sm text-gray-600">Published</div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-edit text-2xl text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2" id="draftProjects">0</div>
                    <div class="text-sm text-gray-600">Drafts</div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-images text-2xl text-blue-600"></i>
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2" id="totalImages">0</div>
                    <div class="text-sm text-gray-600">Total Images</div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search projects..." 
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="lg:w-48">
                        <select id="categoryFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="lg:w-48">
                        <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <button onclick="searchProjects()" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </div>

            <!-- Projects Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">All Projects</h3>
                </div>
                
                <div id="projectsLoading" class="text-center py-12 text-gray-500">
                    <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
                    <p>Loading projects...</p>
                </div>
                
                <div class="overflow-x-auto hidden" id="projectsTableContainer">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Title</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Category</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Images</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Created</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="projectsTableBody">
                            <!-- Projects will be loaded here -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div id="paginationContainer" class="p-6 border-t border-gray-200 hidden">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <span id="showingFrom">0</span> to <span id="showingTo">0</span> of <span id="totalRecords">0</span> results
                        </div>
                        <div class="flex space-x-2" id="paginationButtons">
                            <!-- Pagination buttons will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Project Modal -->
    <div id="projectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
                <div class="sticky top-0 bg-white border-b border-gray-200 p-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800" id="modalTitle">Add New Project</h3>
                        <button type="button" onclick="closeProjectModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <form id="projectForm" class="p-6 space-y-6">
                    <input type="hidden" id="projectId">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="projectTitle" class="block text-sm font-medium text-gray-700 mb-2">Project Title *</label>
                            <input type="text" id="projectTitle" name="title" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                placeholder="Enter project title">
                        </div>
                        
                        <div>
                            <label for="projectCategory" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <select id="projectCategory" name="category_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="projectShortDesc" class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                        <input type="text" id="projectShortDesc" name="short_description"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary"
                            placeholder="Brief project description">
                    </div>
                    
                    <div>
                        <label for="projectDescription" class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                        <textarea id="projectDescription" name="description" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary"
                            placeholder="Detailed project description"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="projectMonth" class="block text-sm font-medium text-gray-700 mb-2">Month of Execution</label>
                            <input type="text" id="projectMonth" name="month_of_execution"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                placeholder="e.g., January 2024">
                        </div>
                        
                        <div>
                            <label for="projectVenue" class="block text-sm font-medium text-gray-700 mb-2">Venue</label>
                            <input type="text" id="projectVenue" name="venue"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                placeholder="Project location">
                        </div>
                    </div>
                    
                    <div>
                        <label for="projectBeneficiaries" class="block text-sm font-medium text-gray-700 mb-2">Beneficiaries</label>
                        <input type="text" id="projectBeneficiaries" name="beneficiaries"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary"
                            placeholder="Who benefits from this project">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="projectStatus" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="projectStatus" name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="projectDisplayOrder" class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                            <input type="number" id="projectDisplayOrder" name="display_order" min="1" value="1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                placeholder="1">
                        </div>
                    </div>
                    
                    <div>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" id="projectShowFirst" name="show_first" value="1"
                                class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <span class="text-sm font-medium text-gray-700">Show this project first on the page</span>
                        </label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Featured Image</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                            <input type="file" id="featuredImageFile" name="featured_image_file" accept="image/*" class="hidden">
                            <label for="featuredImageFile" class="cursor-pointer">
                                <i class="fas fa-image text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Click to upload featured image</p>
                                <p class="text-sm text-gray-500 mt-1">PNG, JPG, GIF up to 10MB</p>
                            </label>
                        </div>
                        <div id="featuredImagePreview" class="mt-4">
                            <!-- Featured image preview will be shown here -->
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Project Images</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                            <input type="file" id="projectImages" name="images[]" multiple accept="image/*" class="hidden">
                            <label for="projectImages" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Click to upload images or drag and drop</p>
                                <p class="text-sm text-gray-500 mt-1">PNG, JPG, GIF up to 10MB each</p>
                            </label>
                        </div>
                        <div id="imagePreview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                            <!-- Image previews will be shown here -->
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeProjectModal()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="bg-gradient-to-r from-primary to-secondary text-white font-semibold px-6 py-2 rounded-lg hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300">
                            <span id="saveButtonText">Save Project</span>
                            <div id="saveSpinner" class="hidden ml-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin inline-block"></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            loadProjects();
            loadStats();
            const sidebarStateKey = 'wl_admin_projects_sidebar_collapsed';
            
            function isMobileView() {
                return window.matchMedia('(max-width: 1023px)').matches;
            }

            function setSidebarCollapsed(collapsed) {
                $('#sidebar').toggleClass('sidebar-collapsed', collapsed);
                $('#collapseSidebarIcon')
                    .toggleClass('fa-angle-left', !collapsed)
                    .toggleClass('fa-angle-right', collapsed);
                localStorage.setItem(sidebarStateKey, collapsed ? '1' : '0');
            }

            function openSidebar() {
                $('#sidebar').removeClass('-translate-x-full');
                $('#sidebarOverlay').removeClass('hidden');
                $('body').css('overflow', 'hidden');
                $('#toggleSidebar').attr('aria-expanded', 'true');
            }

            function closeSidebar() {
                $('#sidebar').addClass('-translate-x-full');
                $('#sidebarOverlay').addClass('hidden');
                $('body').css('overflow', '');
                $('#toggleSidebar').attr('aria-expanded', 'false');
            }

            function syncSidebarForViewport() {
                if (!isMobileView()) {
                    $('#sidebar').removeClass('-translate-x-full');
                    $('#sidebarOverlay').addClass('hidden');
                    $('body').css('overflow', '');
                    $('#toggleSidebar').attr('aria-expanded', 'false');
                    const collapsed = localStorage.getItem(sidebarStateKey) === '1';
                    setSidebarCollapsed(collapsed);
                } else {
                    closeSidebar();
                    $('#sidebar').removeClass('sidebar-collapsed');
                }
            }

            $('#toggleSidebar').click(function() {
                if ($('#sidebar').hasClass('-translate-x-full')) {
                    openSidebar();
                } else {
                    closeSidebar();
                }
            });

            $('#sidebarOverlay').click(function() {
                closeSidebar();
            });

            $('#collapseSidebar').click(function() {
                if (isMobileView()) {
                    return;
                }
                const collapsed = !$('#sidebar').hasClass('sidebar-collapsed');
                setSidebarCollapsed(collapsed);
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && isMobileView()) {
                    closeSidebar();
                }
            });

            $(window).on('resize', function() {
                syncSidebarForViewport();
            });
            syncSidebarForViewport();
            
            // Search on Enter key
            $('#searchInput').on('keypress', function(e) {
                if (e.which === 13) {
                    searchProjects();
                }
            });
            
            // Project form submission
            $('#projectForm').on('submit', function(e) {
                e.preventDefault();
                saveProject();
            });
            
            // Image upload preview
            $('#projectImages').on('change', function(e) {
                previewImages(e.target.files);
            });
            
            $('#featuredImageFile').on('change', function() {
                previewFeaturedImage(this.files[0]);
            });
            
            // Logout
            $('#logoutBtn').click(function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    $.ajax({
                        url: 'api/auth.php',
                        type: 'POST',
                        data: { action: 'logout' },
                        dataType: 'json',
                        success: function(response) {
                            window.location.href = 'login.php';
                        },
                        error: function() {
                            window.location.href = 'login.php';
                        }
                    });
                }
            });
        });
        
        function loadStats() {
            $.ajax({
                url: 'api/projects.php',
                type: 'GET',
                data: { action: 'get_stats' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#totalProjects').text(response.data.total_projects);
                        $('#publishedProjects').text(response.data.published_projects);
                        $('#draftProjects').text(response.data.draft_projects);
                        $('#totalImages').text(response.data.total_images);
                    }
                },
                error: function() {
                    console.log('Failed to load stats');
                }
            });
        }
        
        function loadProjects(page = 1, search = '', category = '', status = '') {
            $('#projectsLoading').show();
            $('#projectsTableContainer').hide();
            
            $.ajax({
                url: 'api/projects.php',
                type: 'GET',
                data: { 
                    action: 'get_all_projects',
                    page: page,
                    limit: 10,
                    search: search,
                    category: category,
                    status: status
                },
                dataType: 'json',
                success: function(response) {
                    $('#projectsLoading').hide();
                    $('#projectsTableContainer').show();
                    
                    if (response.success && response.data && Array.isArray(response.data.projects)) {
                        renderProjectsTable(response.data.projects);
                        renderPagination(response.data.pagination);
                    } else {
                        $('#projectsTableBody').html('<tr><td colspan="6" class="text-center py-8 text-gray-500">No projects found</td></tr>');
                    }
                },
                error: function() {
                    $('#projectsLoading').html('<p class="text-red-500">Failed to load projects</p>');
                }
            });
        }
        
        function renderProjectsTable(projects) {
            let html = '';
            
            projects.forEach(project => {
                const categoryLabel = project.category_name || project.category || 'Uncategorized';
                html += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-6">
                            <div class="font-medium text-gray-800">${project.title}</div>
                            ${project.short_description ? `<div class="text-sm text-gray-600 mt-1">${project.short_description}</div>` : ''}
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-gray-600">${categoryLabel}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold ${
                                project.status === 'published' ? 'bg-green-100 text-green-800' :
                                project.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-gray-100 text-gray-800'
                            }">${project.status}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-gray-600">${project.image_count} images</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-gray-600">${new Date(project.created_at).toLocaleDateString()}</span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex space-x-2">
                                <button onclick="editProject(${project.id})" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteProject(${project.id})" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            $('#projectsTableBody').html(html);
        }
        
        function renderPagination(pagination) {
            if (pagination.total_pages <= 1) {
                $('#paginationContainer').hide();
                return;
            }
            
            $('#paginationContainer').show();
            $('#showingFrom').text(pagination.from || 0);
            $('#showingTo').text(pagination.to || 0);
            $('#totalRecords').text(pagination.total || pagination.total_projects || 0);
            
            let buttons = '';
            
            // Previous button
            if (pagination.current_page > 1) {
                buttons += `<button onclick="loadProjects(${pagination.current_page - 1}, $('#searchInput').val(), $('#categoryFilter').val(), $('#statusFilter').val())" class="px-3 py-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Previous</button>`;
            }
            
            // Page numbers
            for (let i = 1; i <= pagination.total_pages; i++) {
                const active = i === pagination.current_page ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100';
                buttons += `<button onclick="loadProjects(${i}, $('#searchInput').val(), $('#categoryFilter').val(), $('#statusFilter').val())" class="px-3 py-2 ${active} rounded-lg transition-colors">${i}</button>`;
            }
            
            // Next button
            if (pagination.current_page < pagination.total_pages) {
                buttons += `<button onclick="loadProjects(${pagination.current_page + 1}, $('#searchInput').val(), $('#categoryFilter').val(), $('#statusFilter').val())" class="px-3 py-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Next</button>`;
            }
            
            $('#paginationButtons').html(buttons);
        }
        
        function searchProjects() {
            const search = $('#searchInput').val();
            const category = $('#categoryFilter').val();
            const status = $('#statusFilter').val();
            
            loadProjects(1, search, category, status);
        }
        
        function showAddProjectModal() {
            $('#modalTitle').text('Add New Project');
            $('#projectForm')[0].reset();
            $('#projectId').val('');
            $('#projectDisplayOrder').val(1);
            $('#imagePreview').empty();
            $('#projectModal').removeClass('hidden');
            
            setTimeout(() => {
                $('#modalContent').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
            }, 10);
        }
        
        function closeProjectModal() {
            const modal = $('#projectModal');
            const content = $('#modalContent');
            
            content.removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
            
            setTimeout(() => {
                modal.addClass('hidden');
            }, 300);
        }
        
        function editProject(projectId) {
            $.ajax({
                url: 'api/projects.php',
                type: 'GET',
                data: { action: 'get_project', id: projectId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const project = response.data;
                        
                        $('#modalTitle').text('Edit Project');
                        $('#projectId').val(project.id);
                        $('#projectTitle').val(project.title);
                        if (project.category_id) {
                            $('#projectCategory').val(project.category_id);
                        } else if (project.category) {
                            $('#projectCategory option').filter(function() {
                                return $(this).text().trim() === String(project.category).trim();
                            }).prop('selected', true);
                        } else {
                            $('#projectCategory').val('');
                        }
                        $('#projectShortDesc').val(project.short_description);
                        $('#projectDescription').val(project.description);
                        $('#projectMonth').val(project.month_of_execution || '');
                        $('#projectVenue').val(project.venue || '');
                        $('#projectBeneficiaries').val(project.beneficiaries || '');
                        $('#projectStatus').val(project.status);
                        $('#projectDisplayOrder').val(project.display_order || 1);
                        $('#projectShowFirst').prop('checked', project.show_first == 1);
                        
                        // Load existing featured image
                        $('#featuredImagePreview').empty();
                        if (project.featured_image) {
                            $('#featuredImagePreview').html(`
                                <div class="relative group">
                                    <img src="${project.featured_image}" class="w-full h-32 object-cover rounded-lg">
                                    <button type="button" class="absolute top-2 right-2 w-7 h-7 rounded-full bg-red-600 text-white text-xs hover:bg-red-700 transition-colors" onclick="clearFeaturedImage()" title="Remove featured image">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `);
                        }
                        
                        // Load existing images
                        $('#imagePreview').empty();
                        $('#projectImages').val(''); // Clear file input to prevent duplicates
                        if (project.images && project.images.length > 0) {
                            let imageHtml = '';
                            project.images.forEach(image => {
                                imageHtml += `
                                    <div class="relative group existing-image-item" data-image-id="${image.id}">
                                        <img src="${image.image_path}" class="w-full h-32 object-cover rounded-lg">
                                        <button type="button" class="remove-existing-image absolute top-2 right-2 w-7 h-7 rounded-full bg-red-600 text-white text-xs hover:bg-red-700 transition-colors" title="Remove image">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <p class="text-xs text-gray-600 mt-1 truncate">${image.image_name}</p>
                                    </div>
                                `;
                            });
                            $('#imagePreview').html(imageHtml);
                        }
                        
                        $('#projectModal').removeClass('hidden');
                        setTimeout(() => {
                            $('#modalContent').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                        }, 10);
                    }
                },
                error: function() {
                    alert('Failed to load project details');
                }
            });
        }

        $(document).on('click', '.remove-existing-image', function() {
            const projectId = $('#projectId').val();
            const imageItem = $(this).closest('.existing-image-item');
            const imageId = imageItem.data('image-id');

            if (!projectId || !imageId) {
                return;
            }

            if (!confirm('Remove this image from the project?')) {
                return;
            }

            const button = $(this);
            button.prop('disabled', true).addClass('opacity-60');

            $.ajax({
                url: 'api/projects.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'delete_project_image',
                    project_id: projectId,
                    image_id: imageId
                },
                success: function(response) {
                    if (response.success) {
                        imageItem.remove();
                        loadProjects();
                        loadStats();
                    } else {
                        button.prop('disabled', false).removeClass('opacity-60');
                        alert(response.message || 'Failed to remove image');
                    }
                },
                error: function() {
                    button.prop('disabled', false).removeClass('opacity-60');
                    alert('Failed to remove image');
                }
            });
        });
        
        function deleteProject(projectId) {
            if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
                $.ajax({
                    url: 'api/projects.php',
                    type: 'POST',
                    data: { action: 'delete_project', id: projectId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            loadProjects();
                            loadStats();
                        } else {
                            alert(response.message || 'Failed to delete project');
                        }
                    },
                    error: function() {
                        alert('Failed to delete project');
                    }
                });
            }
        }
        
        function saveProject() {
            const projectId = $('#projectId').val();
            const formData = new FormData();
            
            formData.append('action', projectId ? 'update_project' : 'add_project');
            formData.append('id', projectId);
            formData.append('title', $('#projectTitle').val());
            const selectedCategoryOption = $('#projectCategory option:selected');
            formData.append('category_id', $('#projectCategory').val());
            formData.append('category', selectedCategoryOption.text().trim());
            formData.append('short_description', $('#projectShortDesc').val());
            formData.append('description', $('#projectDescription').val());
            formData.append('month_of_execution', $('#projectMonth').val());
            formData.append('venue', $('#projectVenue').val());
            formData.append('beneficiaries', $('#projectBeneficiaries').val());
            formData.append('status', $('#projectStatus').val());
            formData.append('display_order', $('#projectDisplayOrder').val());
            formData.append('show_first', $('#projectShowFirst').is(':checked') ? 1 : 0);
            
            // Add featured image
            const featuredImageFile = $('#featuredImageFile')[0].files[0];
            if (featuredImageFile) {
                formData.append('featured_image_file', featuredImageFile);
            }
            
            // Add images
            const imageFiles = $('#projectImages')[0].files;
            for (let i = 0; i < imageFiles.length; i++) {
                formData.append('images[]', imageFiles[i]);
            }
            
            $('#saveButtonText').text('Saving...');
            $('#saveSpinner').removeClass('hidden');
            
            $.ajax({
                url: 'api/projects.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    $('#saveButtonText').text('Save Project');
                    $('#saveSpinner').addClass('hidden');
                    
                    if (response.success) {
                        closeProjectModal();
                        loadProjects();
                        loadStats();
                    } else {
                        alert(response.message || 'Failed to save project');
                    }
                },
                error: function() {
                    $('#saveButtonText').text('Save Project');
                    $('#saveSpinner').addClass('hidden');
                    alert('Failed to save project');
                }
            });
        }
        
        function previewImages(files) {
            const preview = $('#imagePreview');
            preview.empty();
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.append(`
                            <div class="relative group">
                                <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg">
                                <p class="text-xs text-gray-600 mt-1 truncate">${file.name}</p>
                            </div>
                        `);
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
        
        function previewFeaturedImage(file) {
            const preview = $('#featuredImagePreview');
            preview.empty();
            
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.html(`
                        <div class="relative group">
                            <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg">
                            <button type="button" class="absolute top-2 right-2 w-7 h-7 rounded-full bg-red-600 text-white text-xs hover:bg-red-700 transition-colors" onclick="clearFeaturedImage()" title="Remove featured image">
                                <i class="fas fa-times"></i>
                            </button>
                            <p class="text-xs text-gray-600 mt-1 truncate">${file.name}</p>
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            }
        }
        
        function clearFeaturedImage() {
            $('#featuredImagePreview').empty();
            $('#featuredImageFile').val('');
        }
    </script>
</body>
</html>
