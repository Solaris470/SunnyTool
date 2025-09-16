document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarToggle = document.getElementById('sidebarToggle');

    // Restore sidebar state from localStorage
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
        sidebarToggle.querySelector('.material-icons').textContent = 'menu';
    }

    // Add event listener for the new toggle button
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            if (mainContent) {
                mainContent.classList.toggle('collapsed');
            }
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebar-collapsed', isCollapsed);

            // Change icon
            if (isCollapsed) {
                sidebarToggle.querySelector('.material-icons').textContent = 'menu';
            } else {
                sidebarToggle.querySelector('.material-icons').textContent = 'menu_open';
            }
        });
    }

    // Add active class to current page in sidebar
    // Check if on a tool page
    const isToolPage = window.location.pathname.includes('/tools/');
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';

    document.querySelectorAll('.sidebar-nav a').forEach(link => {
        const linkHref = link.getAttribute('href');
        link.classList.remove('active');
        let linkPage = linkHref.split('/').pop();

        // Handle index page linking from tools pages
        if(linkPage === '..'){
            linkPage = 'index.html';
        }
        if (linkHref === '../index.html' && currentPage === '') {
             link.classList.add('active');
             return;
        }

        if (isToolPage) {
            // This is a tool page
            if (linkHref.includes(currentPage)) {
                link.classList.add('active');
            }
        } else { // This is the index page
            if (link.getAttribute('href') === '#home' || currentPage === 'index.html' && (linkHref === './index.html' || linkHref === 'index.html')) {
                 link.classList.add('active');
            }
        }
    });

    // Smooth scroll for navigation links on the main page
    if (!isToolPage) {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // Mobile Sidebar functionality
    const overlay = document.getElementById('sidebarOverlay');
    const menuToggle = document.querySelector('.menu-toggle');

    if (menuToggle) {
        menuToggle.addEventListener('click', toggleMobileSidebar);
    }
    
    if (overlay) {
        overlay.addEventListener('click', toggleMobileSidebar);
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Desktop: hide mobile elements
            if(overlay) overlay.classList.remove('show');
            if(sidebar) sidebar.classList.remove('show');
        }
    });
});

function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }
}
