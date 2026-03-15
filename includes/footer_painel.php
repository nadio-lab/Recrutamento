  </div><!-- /painel-content -->
  <footer class="text-center py-3 border-top bg-white" style="font-size:.75rem;color:#94a3b8;">
    © <?= date('Y') ?> <?= h($cfg['site_nome']??'Emprega') ?> — Painel de Gestão
  </footer>
</div><!-- /painel-main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<script src="<?= $rel ?>assets/js/painel.js"></script>
<script>feather.replace({'stroke-width':1.75});</script>
</body>
</html>
