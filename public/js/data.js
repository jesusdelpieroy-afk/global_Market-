/* Global Market — datos de la tienda.
   Ahora vienen de MySQL: Laravel los entrega en window.GM_BOOT (ver TiendaController@index).
   Solo el carrito del cliente se guarda en su navegador (localStorage). */
const GM = (() => {
  const CART_KEY = "gm_cart_v1";
  const boot = window.GM_BOOT || { base: "/", data: { config: {}, categories: [], products: [], promos: [] } };

  function clone(o) { return JSON.parse(JSON.stringify(o)); }
  function load() { return clone(boot.data); }
  // Ruta absoluta a un archivo de public/ (funciona aunque el proyecto esté en una subcarpeta)
  function asset(path) { return boot.base + path; }
  function loadCart() {
    try { return JSON.parse(localStorage.getItem(CART_KEY)) || {}; } catch (e) { return {}; }
  }
  function saveCart(cart) {
    try { localStorage.setItem(CART_KEY, JSON.stringify(cart)); } catch (e) {}
  }

  function money(n, cfg) { return (cfg ? cfg.currency : "S/") + " " + Number(n).toFixed(2); }
  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  }
  function initials(name) {
    return String(name || "GM").trim().split(/\s+/).slice(0, 2).map(w => w[0]).join("").toUpperCase() || "GM";
  }

  function today() {
    const d = new Date();
    return d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0") + "-" + String(d.getDate()).padStart(2, "0");
  }
  function formatDate(iso) {
    if (!iso) return "";
    const [y, m, d] = iso.split("-");
    return d + "/" + m + "/" + y;
  }

  // Promociones vigentes: activas y que no hayan vencido
  function activePromos(data) {
    const t = today();
    return data.promos.filter(x => x.active && (!x.until || x.until >= t));
  }
  // Precio final de un producto con la mejor promoción que le corresponda
  function priceOf(p, data) {
    let best = 0;
    activePromos(data).forEach(x => {
      if (x.all || (x.productIds || []).includes(p.id)) best = Math.max(best, Number(x.percent) || 0);
    });
    return { percent: best, original: p.price, final: Math.round(p.price * (100 - best)) / 100 };
  }

  // Foto de portada: la que subió el encargado o la original del local
  function heroImg(data, n) {
    return data.config["heroImage" + n] || asset(n === 1 ? "img/local-1.jpg" : "img/local-2.jpg");
  }

  return { load, loadCart, saveCart, money, esc, initials, today, formatDate, activePromos, priceOf, heroImg, asset, boot };
})();
