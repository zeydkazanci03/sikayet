document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // Initialize DataTables
    if (document.querySelector('table')) {
        new DataTable('table', {
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json'
            }
        });
    }

    // Initialize Select2
    $('.select2').select2();

    // Confirm delete
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Auto-hide alerts
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            new bootstrap.Alert(alert).close();
        }, 5000);
    });
});
