/**
 * Veltrix Core Frontend Runtime
 * Familiar to Build. Modern by Design.
 */
window.Veltrix = (function () {
    const self = {};

    self.init = function () {
        self.initTheme();
        self.initRowSelection();
    };

    self.initTheme = function () {
        const savedTheme = localStorage.getItem('vx-theme') || 'system';
        self.setTheme(savedTheme, false);
    };

    self.setTheme = function (theme, save = true) {
        let activeTheme = theme;
        if (theme === 'system') {
            activeTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        document.documentElement.setAttribute('data-theme', activeTheme);
        if (save) {
            localStorage.setItem('vx-theme', theme);
        }
    };

    self.toggleTheme = function () {
        const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        self.setTheme(next, true);
    };

    self.getCSRFToken = function () {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    self.toast = function (message, type = 'success') {
        let container = document.querySelector('.vx-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'vx-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `vx-toast vx-toast-${type}`;
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    };

    self.handleSwitch = function (input) {
        const id = input.getAttribute('data-id');
        const column = input.getAttribute('data-column');
        const value = input.checked ? 1 : 0;
        const model = input.getAttribute('data-model') || '';

        fetch('/admin/_handle_action_', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': self.getCSRFToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                _action: 'switch',
                _id: id,
                _model: model,
                column: column,
                value: value,
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status) {
                self.toast(data.message || 'Updated successfully', 'success');
            } else {
                input.checked = !input.checked;
                self.toast(data.message || 'Update failed', 'error');
            }
        })
        .catch(err => {
            input.checked = !input.checked;
            self.toast('Network error occurred', 'error');
        });
    };

    self.handleDelete = function (url) {
        if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
            return;
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': self.getCSRFToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ _method: 'DELETE' })
        })
        .then(res => {
            if (res.ok) {
                self.toast('Record deleted successfully', 'success');
                setTimeout(() => window.location.reload(), 500);
            } else {
                self.toast('Failed to delete record', 'error');
            }
        })
        .catch(() => self.toast('Network error', 'error'));
    };

    self.initRowSelection = function () {
        const selectAll = document.querySelector('.vx-select-all');
        if (!selectAll) return;

        selectAll.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.vx-row-select');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });
    };

    self.handleBatchDelete = function (baseUrl) {
        const selected = Array.from(document.querySelectorAll('.vx-row-select:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select records to delete.');
            return;
        }

        if (!confirm(`Are you sure you want to delete ${selected.length} selected record(s)?`)) {
            return;
        }

        fetch(`${baseUrl}/${selected.join(',')}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': self.getCSRFToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ _method: 'DELETE', ids: selected })
        })
        .then(res => {
            if (res.ok) {
                self.toast('Records deleted successfully', 'success');
                setTimeout(() => window.location.reload(), 500);
            } else {
                self.toast('Failed to delete records', 'error');
            }
        })
        .catch(() => self.toast('Network error', 'error'));
    };

    document.addEventListener('DOMContentLoaded', self.init);

    return self;
})();
