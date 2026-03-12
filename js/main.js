/* BookMyLibrary – main.js */

document.addEventListener('DOMContentLoaded', function () {

    // ── Sidebar toggle (mobile) ──────────────────────────────────────────
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            document.body.classList.toggle('sidebar-open');
        });

        // Close sidebar when clicking the overlay
        document.addEventListener('click', function (e) {
            if (
                sidebar.classList.contains('open') &&
                !sidebar.contains(e.target) &&
                e.target !== toggle &&
                !toggle.contains(e.target)
            ) {
                sidebar.classList.remove('open');
                document.body.classList.remove('sidebar-open');
            }
        });
    }

    // ── Delete confirmations ─────────────────────────────────────────────
    document.querySelectorAll('.confirm-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // ── Return confirmations ─────────────────────────────────────────────
    document.querySelectorAll('.confirm-return').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const title = this.dataset.title || 'this book';
            const fine  = parseFloat(this.dataset.fine || 0);
            let msg = `Confirm return of "${title}"?`;
            if (fine > 0) {
                msg += `\n\nA fine of $${fine.toFixed(2)} will be collected (overdue).`;
            }
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // ── Auto-dismiss alerts after 5 s ───────────────────────────────────
    document.querySelectorAll('.alert.alert-success, .alert.alert-danger').forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // ── Set default return date = issue_date + 14 days ──────────────────
    const issueDateInput  = document.querySelector('input[name="issue_date"]');
    const returnDateInput = document.querySelector('input[name="return_date"]');

    if (issueDateInput && returnDateInput) {
        issueDateInput.addEventListener('change', function () {
            if (this.value) {
                const d = new Date(this.value);
                d.setDate(d.getDate() + 14);
                returnDateInput.min   = this.value;
                returnDateInput.value = d.toISOString().split('T')[0];
            }
        });
    }

    // ── Basic client-side form validation ───────────────────────────────
    document.querySelectorAll('form[novalidate]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            let valid = true;

            form.querySelectorAll('[required]').forEach(function (field) {
                field.classList.remove('is-invalid');
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    valid = false;
                }
            });

            // Email validation
            form.querySelectorAll('input[type="email"]').forEach(function (field) {
                if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                    field.classList.add('is-invalid');
                    valid = false;
                }
            });

            if (!valid) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
            }
        });

        // Remove invalid class on input
        form.querySelectorAll('.form-control, .form-select').forEach(function (field) {
            field.addEventListener('input', function () {
                this.classList.remove('is-invalid');
            });
        });
    });
});

// ── Chart.js helpers ─────────────────────────────────────────────────────
const chartColors = [
    '#3b82f6','#22c55e','#f59e0b','#ef4444','#14b8a6',
    '#a855f7','#f97316','#06b6d4','#84cc16','#ec4899'
];

function initCategoryChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Number of Books',
                data: data,
                backgroundColor: chartColors.slice(0, labels.length),
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} book(s)`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

function initStatusChart(canvasId, issued, returned, overdue) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Issued', 'Returned', 'Overdue'],
            datasets: [{
                data: [issued, returned, overdue],
                backgroundColor: ['#f59e0b', '#22c55e', '#ef4444'],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 16,
                        usePointStyle: true,
                        pointStyleWidth: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed}`
                    }
                }
            }
        }
    });
}
