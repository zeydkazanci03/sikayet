document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            new bootstrap.Alert(alert).close();
        }, 5000);
    });
});
