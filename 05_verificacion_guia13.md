# Verificación del sistema completo (guía 13 · Paso 16)

Prepara una base limpia con `php artisan migrate:fresh --seed`, inicia sesión en `/admin/login`
(`admin@globalmarket.com` / `123456`) y marca cada caso.

| N.º | Caso | Cómo probarlo | Resultado esperado | ✔ |
|-----|------|---------------|--------------------|---|
| 1 | Guardar vacío cualquier formulario | Roles, Usuarios, Categorías, Clientes, Productos: «Nuevo registro» → Guardar sin escribir | Mensajes en español con el nombre legible del campo («El campo nombre es obligatorio») | ☐ |
| 2 | Editar sin cambiar el nombre o DNI | Editar un rol, categoría, cliente o usuario y pulsar Guardar | Se guarda sin «ya está registrado» (`Rule::unique()->ignore()`) | ☐ |
| 3 | Editar un usuario sin contraseña | Usuarios → Editar → dejar la contraseña vacía → Guardar; cerrar sesión e ingresar con la contraseña de siempre | Se guarda y la contraseña sigue funcionando | ☐ |
| 4 | Reemplazar la imagen de un producto | Productos → Editar → elegir otra foto → Guardar; revisar `sistema/storage/app/public/productos` | Se ve la nueva y el archivo anterior desaparece | ☐ |
| 5 | Combinar búsqueda, categoría y stock bajo | Productos → escribir «set», elegir una categoría, marcar *Stock bajo* y pulsar la lupa | Listado filtrado y la URL muestra `?search=…&categoria_id=…&stock_bajo=1`; al recargar se mantiene | ☐ |
| 6 | Vender más de lo que hay en stock | Pedidos y ventas → Nueva venta → «Set de esponjas x6» (stock 2) × 5 | Mensaje de stock insuficiente; no se crea la venta y el stock no cambia | ☐ |
| 7 | Registrar una venta válida | Nueva venta con 2 productos distintos | Detalle con IGV (= total × 18 / 118) y total correctos; stock descontado; fecha en hora de Perú | ☐ |
| 8 | Anular la venta | Pedidos y ventas → icono de papelera (o «Anular venta» en el detalle) | Stock devuelto; la venta y sus detalles desaparecen | ☐ |
| 9 | Reglas de borrado | Eliminar: rol «Administrador» (tiene usuarios), categoría «Hogar» (tiene productos), cliente «Rosa Quispe Mamani» (tiene ventas), usuario «Jorge Castillo» (tiene ventas), producto «Cesto de ropa tejido» (está en una venta) | Mensaje rojo claro en cada caso; nunca el error 1451 | ☐ |
| 10 | Navegar por el menú | Abrir cada enlace del menú lateral | Cada enlace abre su módulo y queda marcado | ☐ |

Casos extra del proyecto:

| Caso | Resultado esperado |
|------|--------------------|
| Pedido desde la tienda (`/`) → «Enviar pedido por WhatsApp» | Aparece como venta *pendiente*; al confirmarla en el detalle se descuenta el stock |
| Contraseña distinta en «Confirmar contraseña» (Usuarios) | «La confirmación de contraseña no coincide.» |
| Subir un archivo que no es imagen o pesa más de 2 MB en Productos | Mensaje de validación en español |
| Repetir el mismo producto en dos filas de la venta | «No repita el mismo producto: aumente la cantidad.» |

## Diagnóstico rápido

| Si ves… | Revisa… |
|---------|---------|
| 419 Page Expired | Falta `@csrf` en el formulario |
| 405 Method Not Allowed | Falta `@method('PUT')` o `@method('DELETE')` |
| Add [campo] to fillable property | El `#[Fillable]` del modelo |
| Integrity constraint violation: 1451 | Falta la verificación con `exists()` en `destroy()` |
| Integrity constraint violation: 1452 | Falta la regla `exists:tabla,id` en la clave foránea |
| Imagen rota | `php artisan storage:link` |
| La imagen no llega al controlador | `enctype="multipart/form-data"` |
| Mensajes en inglés | `APP_LOCALE=es` y `php artisan config:clear` |
| Página lenta en un listado con relaciones | `with()` en el controlador (problema N+1) |
