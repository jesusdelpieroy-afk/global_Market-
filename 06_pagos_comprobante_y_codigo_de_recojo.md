# Pagos con comprobante y código de recojo

## Cómo funciona

1. **Cliente**: arma su carrito → *Pagar y subir comprobante* → ve los métodos de pago (QR de Yape/Plin, cuentas BCP…) → paga desde su app → sube la foto del comprobante y escribe el **código de operación**.
2. **Sistema** (todo en una transacción): valida el comprobante, bloquea las filas de los productos, comprueba el stock, **descuenta el stock del catálogo recién ahora**, y crea el pedido en estado `confirmado` con un **código único** (`GM-XXXX-XXXX`).
   - Si el stock ya no alcanza, no se registra nada y se avisa al cliente qué producto ajustar.
   - Un mismo código de operación no se puede usar en dos pedidos.
   - El comprobante se guarda en `storage/app/private/comprobantes` (no es público; solo se ve con sesión de encargado).
3. **Cliente**: recibe su código + QR y un *pase* en `/pedido/{codigo}` (se puede imprimir o guardar como PDF).
4. **Encargado** (`/admin/verificar`): escribe el código o escanea el QR → ve si es válido, **qué productos y cantidades** entregar, el comprobante y el N.º de operación → pulsa *Marcar como entregado*. Después el código queda como «Ya entregado» y no sirve otra vez.
5. Si el comprobante es falso: en *Pedidos y ventas* → **Anular venta**; el stock se devuelve y el código deja de ser válido.

## Métodos de pago (admin)
`Configuraciones › Métodos de pago`: nombre, titular, número/cuenta, CCI, indicaciones, **foto del QR** y si está activo/oculto.

## Instalación sobre un proyecto existente
```
php artisan migrate          # crea metodos_pago y las columnas nuevas de ventas
php artisan storage:link     # (si no lo hiciste) para ver los QR de pago
```
Con MySQL directo: importar `database/sql/global_market_completo.sql` (borra y recrea todo) o solo las dos migraciones nuevas.

## Notas
- El lector de QR con cámara (`/admin/verificar`) usa `BarcodeDetector` (Chrome/Edge/Android) y necesita **HTTPS o localhost**. Si no está disponible, el QR también se puede leer con la cámara normal del celular (abre la página de verificación) o se escribe el código.
- El QR contiene la URL `/admin/verificar/{codigo}`: solo funciona con sesión del encargado; un cliente que lo escanee no ve nada.

## Actualización automática para los clientes
La tienda consulta `/catalogo.json` cada 8 segundos (y al volver a la pestaña). Si el encargado cambió algo —productos, precios, stock, promociones, portada, datos o métodos de pago— se actualiza la pantalla sin recargar, conservando el carrito, la búsqueda y la categoría. Si el stock bajó, las cantidades del carrito se ajustan solas.

## Stock
Solo cambia cuando (1) un cliente sube su comprobante o (2) el encargado registra/confirma/anula una venta. Ver el catálogo o agregar al carrito no toca el stock.

## Antifraude con los comprobantes
- Un **código de operación** no puede repetirse en otro pedido.
- La **misma imagen** (huella SHA-256) tampoco: si alguien sube un archivo idéntico a uno ya usado, se rechaza.
- Limitación: si alguien toma una captura nueva del mismo comprobante, el archivo cambia; ahí lo detiene el código de operación. Por eso el encargado debe comparar el comprobante con el abono real en Yape/BCP antes de entregar.
- Migración nueva: `php artisan migrate`.

## ¿No ves el botón de comprobante?
1. Reemplaza TODA la carpeta del proyecto con la del zip (no solo algunos archivos) y ejecuta `php artisan migrate`.
2. Recarga con Ctrl+F5 (los archivos de la tienda ya llevan versión para evitar la caché).
3. En la tienda, agrega un producto: aparece la barra negra **«Pagar y subir comprobante»** abajo (también dentro del carrito).

## QR de Yape incluido
El proyecto trae cargado el QR de Yape (`public/uploads/pagos/qr-yape.png`). Al correr `php artisan migrate` se crea el método «Yape» con ese QR (si aún no hay métodos). Agrega el número de celular y el titular en Configuraciones › Métodos de pago.
