    <div class="text-center py-4 mt-4" style="color:#8899bb;font-size:.75rem;border-top:1px solid #e0e4ef;">
      &copy; <?= date('Y') ?> Gestion Portfolio &mdash; ESPRIT &middot; UP Web &middot; MVC + PDO + POO
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-delete').forEach(btn => {
  btn.addEventListener('click', e => {
    if (!confirm('Confirmer la suppression ?')) e.preventDefault();
  });
});
</script>
</body></html>
