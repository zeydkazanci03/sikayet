document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Helpful/Not helpful voting
    document.querySelectorAll('[data-helpful]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const complaintId = this.dataset.complaintId;
            const isHelpful = this.dataset.helpful === 'true';
            const url = isHelpful
                ? `/sikayetler/${complaintId}/helpful`
                : `/sikayetler/${complaintId}/not-helpful`;

            fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(() => location.reload());
        });
    });
});
