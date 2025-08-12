(function(){
  const toggle = document.getElementById('menu-toggle');
  const panel = document.getElementById('menu-panel');
  if (!toggle || !panel) return;

  function hide(){ panel.hidden = true; toggle.setAttribute('aria-expanded', 'false'); }
  function show(){ panel.hidden = false; toggle.setAttribute('aria-expanded', 'true'); }

  toggle.addEventListener('click', () => {
    if (panel.hidden) show(); else hide();
  });

  document.addEventListener('click', (e) => {
    if (!panel.hidden && !panel.contains(e.target) && !toggle.contains(e.target)) hide();
  });
})();