# Niveles de dependencia (guía 11, Paso 3.2)

| Clase | Tabla | Claves foráneas | Apunta a | Regla de borrado | Nivel |
|-------|-------|-----------------|----------|------------------|-------|
| Rol | roles | — | — | — | 1 |
| Categoria | categorias | — | — | — | 1 |
| Cliente | clientes | — | — | — | 1 |
| Ajuste | ajustes | — | — | — | 1 |
| PreguntaFrecuente | preguntas_frecuentes | — | — | — | 1 |
| Usuario (User) | users | rol_id | roles | restrict | 2 |
| Producto | productos | categoria_id | categorias | restrict | 2 |
| Promocion | promociones | — | — | — | 1 |
| (pivote) | producto_promocion | producto_id, promocion_id | productos, promociones | cascade | 3 |
| Venta | ventas | cliente_id, user_id | clientes, users | nullOnDelete (0..1) | 3 |
| DetalleVenta | detalle_ventas | venta_id, producto_id | ventas, productos | cascade (composición) / restrict | 4 |

Orden real de ejecución (por la fecha del nombre del archivo): roles → categorias → clientes → add_rol_y_datos_to_users →
productos → promociones → producto_promocion → ventas → detalle_ventas → ajustes → preguntas_frecuentes.
