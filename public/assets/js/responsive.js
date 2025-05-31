/**
 * Responsive enhancements for the application
 * This script adds mobile navigation and improves responsiveness
 */

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const toggleBtn = document.querySelector('.toggle-sidebar');

    // Mobile detection
    const isMobile = window.innerWidth <= 768;

    // Fungsi untuk membuka sidebar
    function showSidebar() {
        sidebar.classList.add('show');
        content.style.opacity = '0.7';
        content.style.width = '100%';
        content.style.marginLeft = '0';
    }

    // Fungsi untuk menutup sidebar
    function hideSidebar() {
        sidebar.classList.remove('show');
        content.style.opacity = '1';
        content.style.width = '100%';
        content.style.marginLeft = '0';
        content.style.left = '0';
    }

    // Fungsi untuk toggle sidebar
    function toggleSidebar(e) {
        e.preventDefault();
        e.stopPropagation();

        if (sidebar.classList.contains('show')) {
            hideSidebar();
        } else {
            showSidebar();
        }
    }

    // Add mobile classes if needed
    if (isMobile) {
        document.body.classList.add('mobile-view');

        // Make sure toggle sidebar button is working
        if (toggleBtn) {
            toggleBtn.removeEventListener('click', toggleSidebar);
            toggleBtn.addEventListener('click', toggleSidebar);
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) &&
                !toggleBtn.contains(e.target) &&
                sidebar.classList.contains('show')) {
                hideSidebar();
            }
        });

        // Prevent clicks inside sidebar from closing it
        sidebar.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            document.body.classList.add('mobile-view');
            content.style.width = '100%';
            content.style.marginLeft = '0';
            content.style.left = '0';
            if (sidebar.classList.contains('show')) {
                content.style.opacity = '0.7';
            }
        } else {
            document.body.classList.remove('mobile-view');
            sidebar.classList.remove('show');
            content.style.opacity = '1';
            content.style.width = 'calc(100% - 60px)';
            content.style.marginLeft = '60px';
            content.style.left = '60px';
        }
    });

    // Make DataTables responsive on all pages
    if ($.fn.dataTable) {
        // Check if there are any datatables that need to be responsive
        const tables = document.querySelectorAll('.table');
        if (tables.length > 0) {
            tables.forEach(function(table) {
                if ($.fn.DataTable.isDataTable(table)) {
                    // This table already has DataTable initialized
                    // Just make sure it's responsive
                    const dt = $(table).DataTable();
                    if (dt.responsive) {
                        dt.responsive.recalc();
                    }
                }
            });
        }
    }

    // Improve form layouts for mobile
    const forms = document.querySelectorAll('form');
    if (forms.length > 0 && window.innerWidth <= 768) {
        forms.forEach(form => {
            const formGroups = form.querySelectorAll('.form-group');
            formGroups.forEach(group => {
                const inputs = group.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (!input.classList.contains('form-control')) {
                        input.classList.add('form-control');
                    }
                });
            });
        });
    }

    // Make tab navigation scrollable on mobile
    const tabNavigations = document.querySelectorAll('.nav-tabs');
    if (tabNavigations.length > 0) {
        tabNavigations.forEach(nav => {
            nav.style.overflowX = 'auto';
            nav.style.flexWrap = 'nowrap';
        });
    }

    // Adjust filter areas for better mobile display
    const filters = document.querySelectorAll('.filter');
    if (filters.length > 0 && window.innerWidth <= 768) {
        filters.forEach(filter => {
            const formGroups = filter.querySelectorAll('.form-group');
            formGroups.forEach(group => {
                group.style.flexDirection = 'column';

                const dateInputGroups = group.querySelectorAll('div[style*="display: flex"]');
                dateInputGroups.forEach(dateGroup => {
                    dateGroup.style.width = '100%';
                });
            });
        });
    }
});
