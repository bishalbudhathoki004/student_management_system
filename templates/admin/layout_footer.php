</main>
<script>
const menuButton = document.getElementById('menuButton');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');

function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
    menuButton.setAttribute('aria-expanded', 'false');
}

function openSidebar() {
    sidebar.classList.add('open');
    overlay.classList.add('show');
    menuButton.setAttribute('aria-expanded', 'true');
}

menuButton.addEventListener('click', function () {
    if (sidebar.classList.contains('open')) {
        closeSidebar();
    } else {
        openSidebar();
    }
});

overlay.addEventListener('click', closeSidebar);

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeSidebar();
    }
});
</script>
</body>
</html>
