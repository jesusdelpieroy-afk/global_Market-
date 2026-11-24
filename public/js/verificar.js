/* Global Market — verificación de códigos en tienda (panel del encargado).
   Lee el QR del cliente con la cámara (BarcodeDetector: Chrome/Edge/Android) y abre el pedido.
   Si el navegador no lo soporta, el encargado puede escribir el código o usar la cámara del
   celular (el QR abre directamente la página de verificación). */
(() => {
  const form = document.getElementById("formCodigo");
  const btn = document.getElementById("btnScan");
  const box = document.getElementById("scanBox");
  const video = document.getElementById("scanVideo");
  const msg = document.getElementById("scanMsg");
  if (!form || !btn) return;

  let stream = null, timer = 0, detector = null;

  function say(t) { msg.textContent = t; msg.hidden = !t; }
  function stop() {
    clearInterval(timer);
    if (stream) stream.getTracks().forEach(t => t.stop());
    stream = null; box.hidden = true; btn.innerHTML = '<i class="bi bi-qr-code-scan me-1"></i>Escanear QR con la cámara';
  }
  // El QR contiene la URL de verificación; también se acepta el código suelto
  function codeFrom(text) {
    const m = String(text).match(/GM-[A-Z0-9]{4}-[A-Z0-9]{4}/i);
    return m ? m[0].toUpperCase() : null;
  }

  btn.addEventListener("click", async () => {
    if (stream) { stop(); say(""); return; }
    if (!("BarcodeDetector" in window) || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      say("Este navegador no puede leer QR desde aquí. Abre la cámara de tu celular y apunta al QR del cliente, o escribe el código a mano.");
      return;
    }
    try {
      detector = new BarcodeDetector({ formats: ["qr_code"] });
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: "environment" } } });
      video.srcObject = stream; await video.play();
      box.hidden = false; say("Apunta al QR del cliente…"); btn.innerHTML = '<i class="bi bi-x-lg me-1"></i>Cerrar cámara';
      timer = setInterval(async () => {
        try {
          const found = await detector.detect(video);
          const code = found.length ? codeFrom(found[0].rawValue) : null;
          if (code) { stop(); window.location.href = form.dataset.base + "?codigo=" + encodeURIComponent(code); }
        } catch (e) { /* un cuadro sin QR: se sigue intentando */ }
      }, 300);
    } catch (e) {
      stop(); say("No se pudo abrir la cámara. Permite el acceso o escribe el código a mano.");
    }
  });

  // Normaliza lo que se escribe (mayúsculas) y acepta pegar la URL completa del QR
  const input = document.getElementById("codigo");
  form.addEventListener("submit", () => { const c = codeFrom(input.value); input.value = (c || input.value).trim().toUpperCase(); });
})();
