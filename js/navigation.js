document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link:not(:last-child)');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            this.classList.toggle('active');
            const submenu = this.nextElementSibling;
            if (submenu && submenu.classList.contains('submenu')) {
                submenu.classList.toggle('show');
            }
        });
    });
});