const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const sidebarToggle = document.getElementById('sidebarToggle');

const updateSidebarState = () => {
    if (window.innerWidth < 992) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('expanded');
    }
};

updateSidebarState();
window.addEventListener('resize', updateSidebarState);

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
});

const activeItem = document.querySelector('.sidebar .sidebar-item.active');

if (activeItem) {
    // On remonte au parent .collapse (ex: #outilsMenu)
    const collapseParent = activeItem.closest('.collapse');

    if (collapseParent) {
        // Récupère l'instance Bootstrap et ouvre le menu
        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseParent, { toggle: false });
        bsCollapse.show();

        // Met à jour l'attribut aria-expanded du header
        const header = document.querySelector(`[data-bs-target="#${collapseParent.id}"]`);
        if (header) {
            header.setAttribute('aria-expanded', 'true');
        }

    }
}