/* Global Market — comportamiento del panel (menú lateral, menús flotantes y acceso) */
(() => {
  const $ = id => document.getElementById(id);
  const mobile = () => window.matchMedia("(max-width: 991.98px)").matches;

  // Menú lateral
  if ($("burger")) {
    const closeSidebar = () => document.body.classList.remove("sb-open");
    $("burger").addEventListener("click", () => document.body.classList.toggle(mobile() ? "sb-open" : "sb-collapsed"));
    $("sbOverlay").addEventListener("click", closeSidebar);
    $("sideNav").addEventListener("click", e => { if (e.target.closest("a") && mobile()) closeSidebar(); });
    document.addEventListener("keydown", e => { if (e.key === "Escape") { closeSidebar(); setUserMenu(false); setBell(false); } });
  }

  // Tarjeta del usuario (abajo del menú)
  function setUserMenu(open) {
    if (!$("userMenu")) return;
    $("userMenu").classList.toggle("open", open);
    $("userBtn").setAttribute("aria-expanded", String(open));
  }
  if ($("userBtn")) {
    $("userBtn").addEventListener("click", e => { e.stopPropagation(); setBell(false); setUserMenu(!$("userMenu").classList.contains("open")); });
  }

  // Campana de notificaciones (stock bajo)
  function setBell(open) {
    if (!$("bellMenu")) return;
    $("bellMenu").classList.toggle("open", open);
    $("bellBtn").setAttribute("aria-expanded", String(open));
  }
  if ($("bellBtn")) {
    $("bellBtn").addEventListener("click", e => { e.stopPropagation(); setUserMenu(false); setBell(!$("bellMenu").classList.contains("open")); });
  }
  document.addEventListener("click", e => {
    if (!e.target.closest(".gm-user-wrap")) setUserMenu(false);
    if (!e.target.closest("#bellMenu")) setBell(false);
  });

  // Mensajes de éxito: se cierran solos
  document.querySelectorAll("[data-autohide]").forEach(el => setTimeout(() => {
    if (window.bootstrap) bootstrap.Alert.getOrCreateInstance(el).close();
  }, 4000));

  // Pantalla de acceso: mostrar contraseña y escena 3D
  const show = $("showPass");
  if (show) show.addEventListener("change", () => { $("pass").type = show.checked ? "text" : "password"; });
  const stage = $("loginStage");
  if (stage && window.matchMedia("(hover: hover)").matches) {
    const scene = stage.firstElementChild;
    stage.addEventListener("mousemove", e => {
      const r = stage.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
      scene.style.setProperty("--ry", ((x - .5) * 28).toFixed(2) + "deg");
      scene.style.setProperty("--rx", ((.5 - y) * 28).toFixed(2) + "deg");
    });
    stage.addEventListener("mouseleave", () => { scene.style.setProperty("--rx", "0deg"); scene.style.setProperty("--ry", "0deg"); });
  }
})();
