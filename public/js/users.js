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

