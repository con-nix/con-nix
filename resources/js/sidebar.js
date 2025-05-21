document.addEventListener("DOMContentLoaded", function () {
    // Set initial collapsed state from localStorage or default to false
    let sidebarCollapsed = localStorage.getItem("sidebar-collapsed") === "true";

    // Apply initial state to the body
    document.body.classList.toggle("sidebar-collapsed", sidebarCollapsed);

    // Function to toggle sidebar
    window.toggleSidebar = function () {
        sidebarCollapsed = !sidebarCollapsed;
        document.body.classList.toggle("sidebar-collapsed", sidebarCollapsed);
        localStorage.setItem("sidebar-collapsed", sidebarCollapsed);
    };

    // Add keyboard shortcut for toggling sidebar (Ctrl + .)
    document.addEventListener("keydown", function (event) {
        // Check if Ctrl (or Cmd on Mac) + period was pressed
        if ((event.ctrlKey || event.metaKey) && event.key === ".") {
            // Prevent default behavior (like browser shortcuts)
            event.preventDefault();

            // Toggle sidebar
            window.toggleSidebar();
        }
    });
});
