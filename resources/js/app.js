import './bootstrap';


// Responsive workspace navigation and progressive file previews.
document.addEventListener('DOMContentLoaded', () => {
    const shell = document.getElementById('dashboardShell');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    if (shell && toggle) {
        const close = () => { shell.classList.remove('sidebar-open'); toggle.setAttribute('aria-expanded', 'false'); };
        toggle.addEventListener('click', () => {
            const opened = shell.classList.toggle('sidebar-open');
            toggle.setAttribute('aria-expanded', String(opened));
        });
        shell.addEventListener('click', event => { if (event.target === shell && shell.classList.contains('sidebar-open')) close(); });
        document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
        shell.querySelectorAll('.dashboard-sidebar a').forEach(link => link.addEventListener('click', close));
    }

    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', () => {
            const target = document.querySelector(input.dataset.preview);
            if (!target) return;
            target.replaceChildren();
            [...(input.files || [])].filter(file => file.type.startsWith('image/')).forEach(file => {
                const image = document.createElement('img');
                image.alt = file.name; image.className = 'img-fluid rounded';
                image.style.cssText = 'width:100%;height:112px;object-fit:cover';
                const url = URL.createObjectURL(file); image.src = url;
                image.addEventListener('load', () => URL.revokeObjectURL(url), { once: true });
                target.append(image);
            });
        });
    });
});
