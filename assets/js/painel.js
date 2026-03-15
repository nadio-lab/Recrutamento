/* painel.js — sidebar responsiva para todos os painéis */
(function(){
  const sidebar  = document.getElementById('pSidebar');
  const overlay  = document.getElementById('pOverlay');
  const toggle   = document.getElementById('pToggle');
  const closeBtn = document.getElementById('pClose');
  const BP = 992;

  function isDesk(){ return window.innerWidth >= BP; }

  function open(){
    if (!sidebar) return;
    sidebar.classList.add('open');
    if (overlay){ overlay.classList.add('active'); overlay.style.display='block'; }
    document.body.style.overflow = 'hidden';
  }
  function close(){
    if (!sidebar) return;
    sidebar.classList.remove('open');
    if (overlay){ overlay.classList.remove('active'); setTimeout(()=>{ if(overlay) overlay.style.display='none'; },240); }
    document.body.style.overflow = '';
  }
  function toggle_sb(){ sidebar && sidebar.classList.contains('open') ? close() : open(); }

  if (toggle)   toggle.addEventListener('click', toggle_sb);
  if (closeBtn) closeBtn.addEventListener('click', close);
  if (overlay)  overlay.addEventListener('click', close);

  document.addEventListener('keydown', e=>{ if(e.key==='Escape') close(); });

  let tx=0, ty=0;
  document.addEventListener('touchstart',e=>{ tx=e.touches[0].clientX; ty=e.touches[0].clientY; },{passive:true});
  document.addEventListener('touchend',e=>{
    const dx=e.changedTouches[0].clientX-tx, dy=Math.abs(e.changedTouches[0].clientY-ty);
    if (sidebar&&sidebar.classList.contains('open')&&dx<-60&&dy<80) close();
    if (!isDesk()&&tx<20&&dx>60&&dy<80&&sidebar&&!sidebar.classList.contains('open')) open();
  },{passive:true});

  let rt;
  window.addEventListener('resize',()=>{ clearTimeout(rt); rt=setTimeout(()=>{ if(isDesk()){ sidebar&&sidebar.classList.remove('open'); if(overlay){overlay.classList.remove('active');overlay.style.display='none';} document.body.style.overflow=''; }},120); });

  if (sidebar) sidebar.querySelectorAll('.nav-item').forEach(a=>{ a.addEventListener('click',()=>{ if(!isDesk()) setTimeout(close,80); }); });

  // Flash auto-dismiss
  document.querySelectorAll('.alert-success,.alert-warning').forEach(el=>{
    setTimeout(()=>{ if(typeof bootstrap!=='undefined'){const b=bootstrap.Alert.getOrCreateInstance(el); b&&b.close();} },5000);
  });

  // Tooltips
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el=>{ if(typeof bootstrap!=='undefined') new bootstrap.Tooltip(el); });
})();
