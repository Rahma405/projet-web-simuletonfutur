// Front Office JS — QuizForge
// Validation is handled inline. This adds the progress bar and delete confirm.

document.addEventListener('DOMContentLoaded', function () {
  // Delete confirmation
  document.querySelectorAll('.btn-delete').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      if (!confirm('Confirm deletion?')) e.preventDefault();
    });
  });

  // Password toggle
  document.querySelectorAll('[data-toggle-password]').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const input = document.getElementById(btn.dataset.togglePassword);
      const icon  = btn.querySelector('i');
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
      if (icon) { icon.classList.toggle('fa-eye'); icon.classList.toggle('fa-eye-slash'); }
    });
  });

  // Progress bar for quiz play page
  const form = document.getElementById('quizForm');
  if (!form) return;
  const cards = document.querySelectorAll('[id^="qcard-"]');
  const total = cards.length;
  if (!total) return;

  const wrap = document.getElementById('progressWrap');
  if (wrap) {
    wrap.innerHTML =
      '<div class="play-progress mb-3">' +
        '<div class="play-progress__bar"><div class="play-progress__fill" id="pFill"></div></div>' +
        '<span id="pLabel" style="white-space:nowrap;font-size:.82rem;color:#65748f">0 / ' + total + ' answered</span>' +
      '</div>';
  }

  function updateProgress() {
    let answered = 0;
    cards.forEach(function(card) {
      card.querySelectorAll('input[type="radio"]').forEach(function(r) { if (r.checked) answered++; });
    });
    const pct = Math.round((answered / total) * 100);
    const fill  = document.getElementById('pFill');
    const label = document.getElementById('pLabel');
    if (fill)  fill.style.width = pct + '%';
    if (label) label.textContent = answered + ' / ' + total + ' answered';
  }

  document.querySelectorAll('.answer-option input[type="radio"]').forEach(function(r) {
    r.addEventListener('change', updateProgress);
  });

  updateProgress();
});
