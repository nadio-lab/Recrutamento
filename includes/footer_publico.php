<?php // Footer reutilizável para páginas públicas ?>
<!-- FOOTER -->
<footer class="site-footer mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <div style="font-family:'Clash Display','DM Sans',sans-serif;font-size:1.4rem;font-weight:700;color:#fff;margin-bottom:.5rem;">
          <?= h($cfg['site_nome']??'Emprega') ?><span style="color:var(--ace);">.</span>
        </div>
        <p style="font-size:.85rem;color:rgba(255,255,255,.6);margin-bottom:.75rem;"><?= h($cfg['site_slogan']??'') ?></p>
        <div style="font-size:.8rem;color:rgba(255,255,255,.45);">
          <?= h($cfg['site_email']??'') ?><br><?= h($cfg['site_telefone']??'') ?>
        </div>
      </div>
      <div class="col-md-2">
        <div class="footer-titulo">Candidatos</div>
        <a href="<?= url('index.php') ?>"                        class="d-block mb-1">Pesquisar Vagas</a>
        <a href="<?= url('registar.php?tipo=candidato') ?>"      class="d-block mb-1">Criar Conta</a>
        <a href="<?= url('candidato/candidaturas.php') ?>"       class="d-block mb-1">Minhas Candidaturas</a>
      </div>
      <div class="col-md-2">
        <div class="footer-titulo">Empresas</div>
        <a href="<?= url('registar.php?tipo=empresa') ?>"        class="d-block mb-1">Publicar Vaga</a>
        <a href="<?= url('empresas.php') ?>"                     class="d-block mb-1">Ver Empresas</a>
        <a href="<?= url('empresa/index.php') ?>"                class="d-block mb-1">Painel Empresa</a>
      </div>
      <div class="col-md-4">
        <div class="footer-titulo">Categorias em Destaque</div>
        <?php
        try {
          $cats_footer = DB::all("SELECT id,nome FROM categorias WHERE ativo=1 ORDER BY total_vagas DESC LIMIT 5");
          foreach ($cats_footer as $c):
        ?>
        <a href="<?= url('index.php?cat='.$c['id']) ?>" class="d-block mb-1"><?= h($c['nome']) ?></a>
        <?php endforeach; } catch(Exception $e){} ?>
      </div>
    </div>
    <div class="footer-bottom d-flex justify-content-between flex-wrap gap-2">
      <span>© <?= date('Y') ?> <?= h($cfg['site_nome']??'Emprega') ?> · <?= h($cfg['site_pais']??'Angola') ?></span>
      <span style="opacity:.6;">Feito com ❤ para o mercado angolano</span>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<script>
feather.replace({'stroke-width':1.75});

// Menu mobile toggle
const btnMenu = document.getElementById('menuMob');
const menuLinks = document.getElementById('menuMobLinks');
if (btnMenu && menuLinks) {
  btnMenu.addEventListener('click', () => menuLinks.classList.toggle('d-none'));
}

// Auto-dismiss alertas
document.querySelectorAll('.alert-success,.alert-warning').forEach(el => {
  setTimeout(() => { if (typeof bootstrap !== 'undefined') { try { bootstrap.Alert.getOrCreateInstance(el).close(); } catch(e){} } }, 5000);
});
</script>
</body>
</html>
