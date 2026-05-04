document.querySelectorAll('.btn-delete').forEach((btn) => {
  btn.addEventListener('click', (event) => {
    if (!confirm('Confirmer la suppression ?')) {
      event.preventDefault();
    }
  });
});

document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
  btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.togglePassword);
    const icon = btn.querySelector('i');

    if (!input) return;

    input.type = input.type === 'password' ? 'text' : 'password';

    if (icon) {
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    }
  });
});

document.querySelectorAll('[data-sort-form]').forEach((form) => {
  const select = form.querySelector('[data-sort-select]');
  const target = document.querySelector('[data-sort-target]');

  if (!select || !target) return;

  let currentValue = select.value;

  select.addEventListener('change', () => {
    if (select.value === currentValue) return;

    target.classList.add('is-sorting');

    window.setTimeout(() => {
      form.requestSubmit();
    }, 220);
  });
});

const qrHost = document.querySelector('[data-qr-login]');

if (qrHost) {
  const qrToken = qrHost.dataset.qrToken;
  const statusNode = qrHost.querySelector('[data-qr-status]');
  const qrMode = qrHost.dataset.qrMode;

  if (qrMode === 'pending') {
    window.setInterval(() => {
      window.location.reload();
    }, 3500);
  }

  const startPolling = () => {
    if (!qrToken) return;

    window.setInterval(async () => {
      try {
        const response = await fetch(`qr_login_status.php?token=${encodeURIComponent(qrToken)}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await response.json();

        if (statusNode) {
          if (data.status === 'approved' && data.authenticated) {
            statusNode.textContent = 'Connexion validee. Redirection...';
          } else if (data.status === 'pending') {
            statusNode.textContent = 'En attente de validation sur le telephone';
          } else if (data.status === 'expired') {
            statusNode.textContent = 'QR code expire. Generez-en un nouveau.';
          }
        }

        if (data.authenticated && data.redirect) {
          window.location.href = data.redirect;
        }
      } catch (error) {
        if (statusNode) {
          statusNode.textContent = 'Verification QR indisponible pour le moment.';
        }
      }
    }, 3000);
  };

  startPolling();
}
