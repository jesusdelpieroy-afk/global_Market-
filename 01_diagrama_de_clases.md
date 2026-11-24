# Diagrama de clases corregido (guía 11, Paso 3)

Revisado con la tabla de observaciones: multiplicidades `0..*` en el lado «muchos», dinero en `Decimal`,
fecha de venta `DateTime`, nombres en singular y sin tildes, métodos en camelCase.

```mermaid
classDiagram
  class Rol { +nombre: String +descripcion: String }
  class Usuario { +name: String +email: String +password: String +telefono: String +iniciarSesion() bool }
  class Categoria { +nombre: String +descripcion: String }
  class Producto { +nombre: String +descripcion: String +precio: Decimal +stock: int +imagen: String +actualizarStock() void }
  class Promocion { +titulo: String +porcentaje: int +aplicaTodos: boolean +vence: Date +activa: boolean }
  class Cliente { +nombre: String +dni: String +telefono: String }
  class Venta { +fecha: DateTime +estado: String +total: Decimal +igv: Decimal +calcularTotal() Decimal }
  class DetalleVenta { +cantidad: int +precioUnitario: Decimal +subtotal: Decimal +calcularSubtotal() Decimal +validarStock() boolean }
  Rol "1" --> "0..*" Usuario : asigna
  Categoria "1" --> "0..*" Producto : clasifica
  Cliente "0..1" --> "0..*" Venta : realiza
  Usuario "0..1" --> "0..*" Venta : atiende
  Venta "1" *-- "1..*" DetalleVenta : contiene
  Producto "1" --> "0..*" DetalleVenta : se vende en
  Producto "*" -- "*" Promocion : tiene descuento
```

Tablas de apoyo sin relaciones: `ajustes` (datos de la tienda y portada, una sola fila) y `preguntas_frecuentes`.
