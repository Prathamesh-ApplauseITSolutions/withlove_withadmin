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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - With Love For You</title>
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
                    <a href="#" class="nav-link flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white rounded-xl transition-all duration-200 group bg-slate-800 text-white ring-1 ring-white/10" data-page="dashboard">
                        <i class="fas fa-tachometer-alt w-5 mr-3 text-center"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white rounded-xl transition-all duration-200 group" data-page="projects">
                        <i class="fas fa-project-diagram w-5 mr-3 text-center"></i>
                        <span class="nav-text">Projects</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white rounded-xl transition-all duration-200 group" data-page="media">
                        <i class="fas fa-images w-5 mr-3 text-center"></i>
                        <span class="nav-text">Media Library</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white rounded-xl transition-all duration-200 group" data-page="users">
                        <i class="fas fa-users w-5 mr-3 text-center"></i>
                        <span class="nav-text">Users</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white rounded-xl transition-all duration-200 group" data-page="settings">
                        <i class="fas fa-cog w-5 mr-3 text-center"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </li>
                <li class="pt-8 mt-8 border-t border-slate-700/80">
                    <a href="#" id="logoutBtn" class="flex items-center px-4 py-3 text-red-300 hover:bg-red-500/20 hover:text-red-200 rounded-xl transition-all duration-200">
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
                    <h1 class="ml-4 text-xl sm:text-2xl font-bold text-gray-800" id="pageTitle">Dashboard</h1>
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
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto" id="contentArea">
            <!-- Dashboard Content -->
            <div id="dashboardContent">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6 sm:mb-8">Dashboard Overview</h2>
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center">
                                <i class="fas fa-project-diagram text-2xl text-primary"></i>
                            </div>
                        </div>
                        <div class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2" id="totalProjects">0</div>
                        <div class="text-sm text-gray-600">Total Projects</div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-check-circle text-2xl text-green-600"></i>
                            </div>
                        </div>
                        <div class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2" id="publishedProjects">0</div>
                        <div class="text-sm text-gray-600">Published</div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-edit text-2xl text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2" id="draftProjects">0</div>
                        <div class="text-sm text-gray-600">Drafts</div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-images text-2xl text-blue-600"></i>
                            </div>
                        </div>
                        <div class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2" id="totalImages">0</div>
                        <div class="text-sm text-gray-600">Total Images</div>
                    </div>
                </div>

                <!-- Recent Projects -->
                <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
                        <h3 class="text-xl font-bold text-gray-800">Recent Projects</h3>
                        <button onclick="loadProjectsPage()" class="bg-gradient-to-r from-primary to-secondary text-white font-semibold px-6 py-3 rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 w-full sm:w-auto">
                            <i class="fas fa-plus mr-2"></i>Manage Projects
                        </button>
                    </div>
                    
                    <div id="projectsLoading" class="text-center py-8 text-gray-500">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading projects...</p>
                    </div>
                    
                    <div class="overflow-x-auto hidden" id="projectsTable">
                        <table class="w-full min-w-[600px]">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Title</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Category</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Created</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="recentProjectsTable">
                                <!-- Projects will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load dashboard data
            loadDashboardData();
            const sidebarStateKey = 'wl_admin_sidebar_collapsed';
            
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

            // Sidebar toggle
            $('#toggleSidebar').click(function() {
                if ($('#sidebar').hasClass('-translate-x-full')) {
                    openSidebar();
                } else {
                    closeSidebar();
                }
            });

            // Close sidebar on overlay click
            $('#sidebarOverlay').click(function() {
                closeSidebar();
            });

            // Desktop collapse toggle
            $('#collapseSidebar').click(function() {
                if (isMobileView()) {
                    return;
                }
                const collapsed = !$('#sidebar').hasClass('sidebar-collapsed');
                setSidebarCollapsed(collapsed);
            });

            // Close sidebar on escape
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && isMobileView()) {
                    closeSidebar();
                }
            });

            // Keep sidebar behavior stable across resize
            $(window).on('resize', function() {
                syncSidebarForViewport();
            });
            syncSidebarForViewport();
            
            // Navigation
            $('.nav-link').click(function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                
                // Update active state
                $('.nav-link').removeClass('bg-slate-800 text-white ring-1 ring-white/10');
                $(this).addClass('bg-slate-800 text-white ring-1 ring-white/10');

                // Close sidebar after selecting menu on mobile
                if (isMobileView()) {
                    closeSidebar();
                }
                
                // Load page content
                if (page === 'dashboard') {
                    loadDashboardContent();
                } else if (page === 'projects') {
                    loadProjectsPage();
                } else if (page === 'media') {
                    loadMediaPage();
                } else if (page === 'users') {
                    loadUsersPage();
                } else if (page === 'settings') {
                    loadSettingsPage();
                }
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
        
        function loadDashboardData() {
            // Load statistics
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
            
            // Load recent projects
            $.ajax({
                url: 'api/projects.php',
                type: 'GET',
                data: { action: 'get_recent_projects', limit: 5 },
                dataType: 'json',
                success: function(response) {
                    $('#projectsLoading').hide();
                    $('#projectsTable').removeClass('hidden');
                    
                    if (response.success && Array.isArray(response.data) && response.data.length > 0) {
                        let html = '';
                        response.data.forEach(project => {
                            const categoryLabel = project.category_name || project.category || 'Uncategorized';
                            html += `
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-4 font-medium text-gray-800">${project.title}</td>
                                    <td class="py-3 px-4 text-gray-600">${categoryLabel}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${
                                            project.status === 'published' ? 'bg-green-100 text-green-800' :
                                            project.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                            'bg-gray-100 text-gray-800'
                                        }">${project.status}</span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">${new Date(project.created_at).toLocaleDateString()}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex space-x-2">
                                            <button onclick="loadProjectsPage()" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#recentProjectsTable').html(html);
                    } else {
                        $('#recentProjectsTable').html('<tr><td colspan="5" class="text-center py-4 text-gray-500">No projects found</td></tr>');
                    }
                },
                error: function() {
                    $('#projectsLoading').html('<p class="text-red-500">Failed to load projects</p>');
                    console.log('Failed to load projects');
                }
            });
        }
        
        function loadDashboardContent() {
            $('#pageTitle').text('Dashboard');
            $('#contentArea').load('dashboard_content.php');
        }
        
        function loadProjectsPage() {
            $('#pageTitle').text('Projects');
            window.location.href = 'projects.php';
        }
        
        function loadMediaPage() {
            const content = `
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6 sm:mb-8">Media Library</h2>
                <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8">
                    <p class="text-gray-600">Media library functionality coming soon...</p>
                </div>
            `;
            $('#contentArea').html(content);
        }
        
        function loadUsersPage() {
            const content = `
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6 sm:mb-8">User Management</h2>
                <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8">
                    <p class="text-gray-600">User management functionality coming soon...</p>
                </div>
            `;
            $('#contentArea').html(content);
        }
        
        function loadSettingsPage() {
            const content = `
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6 sm:mb-8">Settings</h2>
                <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8">
                    <p class="text-gray-600">Settings functionality coming soon...</p>
                </div>
            `;
            $('#contentArea').html(content);
        }
    </script>
</body>
</html>
