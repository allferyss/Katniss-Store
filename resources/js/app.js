// JavaScript seperlunya: menu mobile, konfirmasi aksi berbahaya, dan tutup notifikasi.

document.addEventListener('DOMContentLoaded', () => {
    // Menu mobile
    document.querySelectorAll('[data-menu-toggle]').forEach((button) => {
        const target = document.getElementById(button.getAttribute('aria-controls'));
        if (!target) return;
        button.addEventListener('click', () => {
            const open = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', String(!open));
            target.classList.toggle('hidden', open);
        });
    });

    // Konfirmasi sebelum kirim form: <form data-confirm="Yakin?">
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // Tutup notifikasi
    document.querySelectorAll('[data-dismiss]').forEach((button) => {
        button.addEventListener('click', () => button.closest('[role="alert"], [role="status"]')?.remove());
    });

    // Cegah klik ganda pada tombol kirim (mengurangi risiko request ganda)
    document.querySelectorAll('form[data-once]').forEach((form) => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('button[type="submit"]').forEach((b) => {
                setTimeout(() => { b.disabled = true; }, 0);
            });
        });
    });
});
