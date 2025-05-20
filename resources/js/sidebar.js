document.addEventListener('DOMContentLoaded', function() {
    // Set initial collapsed state from localStorage or default to false
    let sidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    
    // Apply initial state to the body
    document.body.classList.toggle('sidebar-collapsed', sidebarCollapsed);
    
    // Update icon visibility based on initial state
    updateIconVisibility(sidebarCollapsed);
    
    // Function to toggle sidebar
    window.toggleSidebar = function() {
        sidebarCollapsed = !sidebarCollapsed;
        document.body.classList.toggle('sidebar-collapsed', sidebarCollapsed);
        localStorage.setItem('sidebar-collapsed', sidebarCollapsed);
        
        // Update icon visibility when toggling
        updateIconVisibility(sidebarCollapsed);
    };
    
    // Helper function to update icon visibility
    function updateIconVisibility(collapsed) {
        const collapseIcon = document.querySelector('.sidebar-collapse-icon');
        const expandIcon = document.querySelector('.sidebar-expand-icon');
        
        if (collapseIcon && expandIcon) {
            if (collapsed) {
                collapseIcon.classList.add('hidden');
                expandIcon.classList.remove('hidden');
            } else {
                collapseIcon.classList.remove('hidden');
                expandIcon.classList.add('hidden');
            }
        }
    }
    
    // Add keyboard shortcut for toggling sidebar (Ctrl + .)
    document.addEventListener('keydown', function(event) {
        // Check if Ctrl (or Cmd on Mac) + period was pressed
        if ((event.ctrlKey || event.metaKey) && event.key === '.') {
            // Prevent default behavior (like browser shortcuts)
            event.preventDefault();
            
            // Toggle sidebar
            window.toggleSidebar();
        }
    });
});