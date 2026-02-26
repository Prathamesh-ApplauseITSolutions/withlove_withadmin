$(document).ready(function() {
    checkAuth();
    loadDashboardData();
    
    // Mobile sidebar toggle
    $('#toggleSidebar').click(function() {
        const sidebar = $('#sidebar');
        const overlay = $('#sidebarOverlay');
        
        sidebar.toggleClass('-translate-x-full');
        overlay.toggleClass('hidden');
    });
    
    // Close sidebar on overlay click
    $('#sidebarOverlay').click(function() {
        $('#sidebar').addClass('-translate-x-full');
        $(this).addClass('hidden');
    });
    
    // Navigation
    $('.nav-link[data-page]').click(function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        
        $('.nav-link').removeClass('bg-gray-700 text-white');
        $(this).addClass('bg-gray-700 text-white');
        
        loadPage(page);
        
        // Close mobile sidebar after navigation
        if (window.innerWidth < 1024) {
            $('#sidebar').addClass('-translate-x-full');
            $('#sidebarOverlay').addClass('hidden');
        }
    });
    
    // Logout
    $('#logoutBtn').click(function(e) {
        e.preventDefault();
        logout();
    });
    
    // Project form image preview
    $('#projectImages').change(function(e) {
        const files = e.target.files;
        const preview = $('#imagePreview');
        preview.empty();
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
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
    });
});

function checkAuth() {
    $.ajax({
        url: 'api/auth.php',
        type: 'POST',
        data: { action: 'check_auth' },
        dataType: 'json',
        success: function(response) {
            if (!response.authenticated) {
                window.location.href = 'login.php';
            } else {
                // Update user info
                $('#userName').text(response.user.name);
                $('#userAvatar').text(response.user.name.charAt(0).toUpperCase());
            }
        },
        error: function() {
            window.location.href = 'login.php';
        }
    });
}

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
        }
    });
    
    // Load recent projects
    $.ajax({
        url: 'api/projects.php',
        type: 'GET',
        data: { action: 'get_recent_projects', limit: 5 },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '';
                response.data.forEach(project => {
                    html += `
                        <tr>
                            <td>${project.title}</td>
                            <td>${project.category}</td>
                            <td><span class="status-badge status-${project.status}">${project.status}</span></td>
                            <td>${formatDate(project.created_at)}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" onclick="editProject(${project.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteProject(${project.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                $('#recentProjectsTable').html(html);
            }
        }
    });
}

function loadPage(page) {
    const contentArea = $('#contentArea');
    
    // Update page title
    const titles = {
        dashboard: 'Dashboard',
        projects: 'Projects Management',
        media: 'Media Library',
        users: 'User Management',
        settings: 'Settings'
    };
    $('#pageTitle').text(titles[page] || 'Dashboard');
    
    switch(page) {
        case 'projects':
            loadProjectsPage();
            break;
        case 'media':
            loadMediaPage();
            break;
        case 'users':
            loadUsersPage();
            break;
        case 'settings':
            loadSettingsPage();
            break;
        default:
            // Dashboard is already loaded
            break;
    }
}

function loadProjectsPage() {
    const content = `
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6 sm:mb-8">Projects Management</h2>
        
        <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8 mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 space-y-4 lg:space-y-0">
                <h3 class="text-xl font-bold text-gray-800">All Projects</h3>
                <button onclick="showAddProject()" class="bg-gradient-to-r from-primary to-secondary text-white font-semibold px-6 py-3 rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 w-full lg:w-auto">
                    <i class="fas fa-plus mr-2"></i>Add New Project
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <input type="text" id="searchProjects" placeholder="Search projects..." 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-300">
                </div>
                <div>
                    <select id="filterCategory" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-300">
                        <option value="">All Categories</option>
                        <option value="Education">Education</option>
                        <option value="Healthcare">Healthcare</option>
                        <option value="Community Development">Community Development</option>
                        <option value="Environment">Environment</option>
                        <option value="Youth Empowerment">Youth Empowerment</option>
                    </select>
                </div>
                <div>
                    <select id="filterStatus" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-300">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px]">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Title</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Category</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Images</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Created</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="allProjectsTable">
                        <!-- Projects will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <nav aria-label="Projects pagination" class="mt-6">
                <ul class="flex justify-center space-x-2" id="projectsPagination">
                    <!-- Pagination will be loaded here -->
                </ul>
            </nav>
        </div>
    `;
    
    $('#contentArea').html(content);
    loadAllProjects();
    
    // Setup filters
    $('#searchProjects, #filterCategory, #filterStatus').on('input change', function() {
        loadAllProjects();
    });
}

function loadAllProjects() {
    const search = $('#searchProjects').val();
    const category = $('#filterCategory').val();
    const status = $('#filterStatus').val();
    
    $.ajax({
        url: 'api/projects.php',
        type: 'GET',
        data: {
            action: 'get_all_projects',
            search: search,
            category: category,
            status: status
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '';
                response.data.projects.forEach(project => {
                    html += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 font-medium text-gray-800">${project.title}</td>
                        <td class="py-3 px-4 text-gray-600">${project.category}</td>
                        <td class="py-3 px-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold ${
                                project.status === 'published' ? 'bg-green-100 text-green-800' :
                                project.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-gray-100 text-gray-800'
                            }">${project.status}</span>
                        </td>
                        <td class="py-3 px-4 text-gray-600">${project.image_count} images</td>
                        <td class="py-3 px-4 text-gray-600">${formatDate(project.created_at)}</td>
                        <td class="py-3 px-4">
                            <div class="flex space-x-2">
                                <button onclick="editProject(${project.id})" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteProject(${project.id})" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                });
                $('#allProjectsTable').html(html);
                
                // Load pagination
                loadPagination(response.data.pagination);
            }
        }
    });
}

function loadPagination(pagination) {
    let html = '';
    
    // Previous button
    if (pagination.current_page > 1) {
        html += `<li><button onclick="changePage(${pagination.current_page - 1})" class="px-3 py-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Previous</button></li>`;
    }
    
    // Page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
        const active = i === pagination.current_page ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100';
        html += `<li><button onclick="changePage(${i})" class="px-3 py-2 ${active} rounded-lg transition-colors">${i}</button></li>`;
    }
    
    // Next button
    if (pagination.current_page < pagination.total_pages) {
        html += `<li><button onclick="changePage(${pagination.current_page + 1})" class="px-3 py-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Next</button></li>`;
    }
    
    $('#projectsPagination').html(html);
}

function changePage(page) {
    // This would need to be implemented to handle pagination
    console.log('Changing to page:', page);
}

function showAddProject() {
    $('#projectModalLabel').text('Add New Project');
    $('#projectForm')[0].reset();
    $('#projectId').val('');
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
                $('#projectModalLabel').text('Edit Project');
                $('#projectId').val(project.id);
                $('#projectTitle').val(project.title);
                $('#projectCategory').val(project.category);
                $('#projectShortDesc').val(project.short_description);
                $('#projectDescription').val(project.description);
                $('#projectStatus').val(project.status);
                
                // Load existing images
                if (project.images && project.images.length > 0) {
                    let imageHtml = '';
                    project.images.forEach(image => {
                        imageHtml += `
                            <div class="relative group">
                                <img src="${image.image_path}" class="w-full h-32 object-cover rounded-lg">
                                <p class="text-xs text-gray-600 mt-1 truncate">${image.image_name}</p>
                            </div>
                        `;
                    });
                    $('#imagePreview').html(imageHtml);
                }
                
                showAddProject();
                $('#projectModalLabel').text('Edit Project');
            }
        }
    });
}

function saveProject() {
    const projectId = $('#projectId').val();
    const formData = new FormData();
    
    formData.append('action', projectId ? 'update_project' : 'add_project');
    if (projectId) {
        formData.append('id', projectId);
    }
    formData.append('title', $('#projectTitle').val());
    formData.append('category', $('#projectCategory').val());
    formData.append('short_description', $('#projectShortDesc').val());
    formData.append('description', $('#projectDescription').val());
    formData.append('status', $('#projectStatus').val());
    
    // Add images
    const imageFiles = $('#projectImages')[0].files;
    for (let i = 0; i < imageFiles.length; i++) {
        formData.append('images[]', imageFiles[i]);
    }
    
    // Show loading
    $('#saveProjectText').text('Saving...');
    $('#saveProjectSpinner').show();
    
    $.ajax({
        url: 'api/projects.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            $('#saveProjectSpinner').hide();
            $('#saveProjectText').text('Save Project');
            
            if (response.success) {
                $('#projectModal').modal('hide');
                showAlert('success', response.message);
                loadDashboardData();
                
                // Reload projects page if it's currently active
                if ($('.nav-link[data-page="projects"]').hasClass('active')) {
                    loadAllProjects();
                }
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
            $('#saveProjectSpinner').hide();
            $('#saveProjectText').text('Save Project');
            showAlert('danger', 'Network error. Please try again.');
        }
    });
}

function deleteProject(projectId) {
    if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
        $.ajax({
            url: 'api/projects.php',
            type: 'POST',
            data: {
                action: 'delete_project',
                id: projectId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    loadDashboardData();
                    
                    // Reload projects page if it's currently active
                    if ($('.nav-link[data-page="projects"]').hasClass('active')) {
                        loadAllProjects();
                    }
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', 'Network error. Please try again.');
            }
        });
    }
}

function logout() {
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

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function showAlert(type, message) {
    const alertColors = {
        success: 'bg-green-50 border-green-200 text-green-800',
        danger: 'bg-red-50 border-red-200 text-red-800',
        warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
        info: 'bg-blue-50 border-blue-200 text-blue-800'
    };
    
    const alertHtml = `
        <div class="border rounded-xl p-4 mb-4 animate-slideUp ${alertColors[type]} flex justify-between items-center">
            <span>${message}</span>
            <button type="button" onclick="this.parentElement.remove()" class="ml-4 text-current hover:opacity-70">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Show alert at the top of the content area
    $('#contentArea').prepend(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        $('.border').first().fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
}

// Placeholder functions for other pages
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
