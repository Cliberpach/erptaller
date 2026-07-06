# Plan: Bloquear edición insegura de Órdenes de Trabajo ya facturadas

> **Estado: IMPLEMENTADO.** Ver sección final "Estado de implementación" para
> el detalle exacto de qué se aplicó y qué se dejó fuera de alcance a
> propósito.

## Problema

Hoy no existe ningún guard que impida editar una Orden de Trabajo (OT) después
de que ya se generó un comprobante (Sale) a partir de ella, total o
parcialmente. Esto rompe la integridad del flujo de facturación.

## Comportamiento actual (verificado en código)

### 1. `WorkOrderValidation::validationUpdate()` no chequea facturación

Archivo: `app/Http/Services/Tenant/WorkShop/WorkOrders/WorkOrderValidation.php`

Solo bloquea si:
- `$order->status === 'ANULADO'`
- `$order->status === 'EXPIRADO'`
- La cuenta cliente (`customer_accounts`) no está en estado `PENDIENTE`

**No revisa `status_invoice`** (`FACTURADO` / `FACTURADO PARCIAL`) en ningún
punto. Una OT facturada pasa la validación de edición sin problema.

### 2. `WorkOrderService::update()` borra y re-crea el detalle completo

Archivo: `app/Http/Services/Tenant/WorkShop/WorkOrders/WorkOrderService.php`

Al editar, el flujo es:

1. Revierte stock de la lista VIEJA (si `validation_stock` estaba activo).
2. `deleteDetailProduct($id)` / `deleteDetailService($id)` — **borrado físico**
   (`->delete()`, sin soft-delete) de TODAS las filas en
   `work_orders_products` / `work_orders_services`, incluyendo las que tenían
   `invoiced = true`, `invoiced_sale_id`, `invoiced_sale_serie`.
3. `insertWorkOrderDetail()` inserta filas nuevas desde la lista que mandó el
   usuario, con `invoiced = false` por defecto (sin historial).
4. Recalcula el total de la OT y llama a
   `CustomerAccountService::updateFromWorkOrder()`, que reescribe
   `amount` / `balance` / `status` de la cuenta cliente según el total NUEVO.

### 3. Consecuencia: el guard "una línea no se factura dos veces" se resetea

`WorkOrderValidation::validationInvoice()` decide si una línea ya fue
facturada consultando:

```php
WorkOrderProduct::where('work_order_id', $work_order_id)
    ->where('product_id', $item->id)
    ->where('invoiced', true)
    ->exists();
```

Como el edit borra y re-crea las filas con `invoiced = false`, este chequeo
deja de detectar nada. Resultado: **se puede volver a facturar la misma
línea que ya se facturó antes** (doble cobro), sin ningún error ni aviso.

### 4. El comprobante ya emitido queda intacto (correcto), pero desincronizado

`sales_documents` / `sales_documents_details` **no se tocan** — son una copia
independiente creada una sola vez en `SaleService::storeFromOrder()`. Eso es
correcto: un comprobante aceptado por SUNAT no debe cambiar nunca.

El problema es que todo lo demás (OT, cuenta cliente, `status_invoice`) sigue
cambiando libremente, quedando sin ninguna relación real con lo que el
comprobante ya emitido dice. Ejemplo:

| | Antes de editar | Después de editar |
|---|---|---|
| Comprobante (fijo) | 3 productos, S/. 100 | 3 productos, S/. 100 |
| OT | 3 productos, S/. 100, `status_invoice=FACTURADO` | 5 productos, S/. 150, `status_invoice=FACTURADO` (sin cambiar, ya obsoleto) |
| Cuenta cliente | `amount=100` | `amount=150` (sin relación con lo realmente facturado) |

### 5. Facturación parcial es una funcionalidad real, no un bug

La facturación **no es "una sola vez por OT"** — es una sola vez **por línea**
(producto/servicio individual). Una OT puede facturarse en varios
comprobantes a lo largo del tiempo (`FACTURADO PARCIAL` → `FACTURADO`),
siempre que cada línea concreta no se facture dos veces. Esto ya está
soportado y se usa en producción.

**Por eso la solución NO puede ser "bloquear toda edición si ya está
facturada"** — eso rompería el caso legítimo de: facturar 2 de 5 productos
hoy, seguir trabajando la OT, agregar/editar líneas nuevas, facturar el
resto después.

## Solución recomendada: cantidad facturada acumulada (patrón estándar de ERP)

Los sistemas de facturación serios (SAP, Odoo, NetSuite, QuickBooks) no
manejan esto con un flag booleano `invoiced = true/false` por línea, ni
borrando/recreando filas en cada edit. Usan un **contador de cantidad ya
facturada** en la MISMA fila, que solo sube, nunca se resetea.

### Cambio de modelo de datos

Reemplazar la columna `invoiced` (boolean) por `invoiced_quantity` (decimal,
default 0) en `work_orders_products` y `work_orders_services`. La columna
`invoiced_sale_id` deja de tener sentido como valor único (una línea puede
terminar repartida en varios comprobantes) — pasa a una tabla de detalle
`work_order_line_invoices` (o similar) con `(line_id, sale_id, quantity)` por
cada facturación parcial de esa línea, para mantener trazabilidad completa.

### Reglas del nuevo modelo

1. **Editar la OT nunca borra+recrea filas de detalle.** Cada línea
   (`product_id` / `service_id` dentro de esa OT) es una fila persistente que
   se actualiza `UPDATE quantity = ...` in-place. Se identifica por
   `(work_order_id, product_id)` — no se duplica.
2. **Subir cantidad**: siempre permitido (ej. 2 → 10). No afecta lo ya
   facturado, solo amplía el pendiente por facturar
   (`quantity - invoiced_quantity`).
3. **Bajar cantidad**: permitido solo si
   `nueva_quantity >= invoiced_quantity`. Si el usuario intenta bajar por
   debajo de lo ya facturado, se rechaza con mensaje claro (ej.
   `"NO SE PUEDE BAJAR A 1: YA SE FACTURARON 2 UNIDADES DE ESTE PRODUCTO"`).
4. **Facturar** (`invoiceStore()`): calcula el pendiente por línea
   (`quantity - invoiced_quantity`), factura hasta ese saldo, y al terminar
   hace `invoiced_quantity += cantidad_facturada_ahora` (acumula, no
   sobrescribe). Cuando `invoiced_quantity === quantity` en TODAS las líneas,
   `status_invoice = 'FACTURADO'`; si falta algo, `'FACTURADO PARCIAL'`.
5. **Stock**: se descuenta/revierte solo sobre el DELTA de `quantity` en un
   edit (si `quantity` sube, se descuenta la diferencia; si baja, se revierte
   la diferencia) — nunca se recalcula desde cero como hoy.
6. **Cuenta cliente**: el total de la OT siempre refleja `quantity` completa
   (lo pedido), independiente de cuánto se haya facturado — igual que hoy,
   pero ahora sin riesgo de perder el rastro de lo ya cobrado.

### Ejemplo con el caso Producto A (cant=2 → factura B001-1 → sube a cant=10)

| Paso | `quantity` | `invoiced_quantity` | Pendiente por facturar |
|---|---|---|---|
| OT creada | 2 | 0 | 2 |
| Facturada (B001-1) | 2 | 2 | 0 |
| Edit: sube a 10 | 10 | 2 (intacto) | 8 |
| Factura de nuevo (B001-2, hasta 8) | 10 | 10 | 0 |

Sin duplicar líneas, sin perder el rastro de B001-1, sin bloquear el edit —
simplemente el pendiente se recalcula solo.

## Trade-off entre las 3 opciones consideradas

| | Bloqueo total (`status_invoice` → no editar) | Guard fino binario (`invoiced=true` intocable) | Cantidad acumulada (`invoiced_quantity`) |
|---|---|---|---|
| Esfuerzo | Bajo | Medio-alto | Alto (migración de columna + tocar 4 capas) |
| Riesgo de romper flujo real | Alto — no deja agregar/ampliar nada en una OT parcial | Medio — permite agregar líneas nuevas, pero no ampliar cantidad de una línea ya facturada (caso real de este hilo) | Bajo — soporta subir cantidad de una línea ya facturada, el caso que realmente pasa en el taller |
| Resuelve "factura 2, después sube a 10" | No (bloquea todo el edit) | No (bloquea el aumento también, trata la línea como intocable) | Sí — es justo el caso que resuelve nativamente |
| Cierra el hueco de doble facturación | Sí | Sí | Sí |
| Patrón usado por ERPs reales | No | No | Sí |

**Recomendación**: implementar cantidad acumulada. Es más trabajo, pero es el
único de los tres que soporta el caso real (ampliar cantidad de una línea que
ya se facturó parcialmente) sin bloquear al usuario ni arriesgar
sobre-facturación.

## Alcance de archivos a tocar (implementación futura)

- **Migraciones** (`database/migrations/tenant/`):
  - `work_orders_products` / `work_orders_services`: agregar
    `invoiced_quantity` (decimal, default 0); eventualmente retirar
    `invoiced` / `invoiced_sale_id` / `invoiced_sale_serie` una vez migrado
    el dato existente.
  - Nueva tabla `work_order_line_invoices` (o similar):
    `work_order_product_id` / `work_order_service_id` (nullable, uno de los
    dos), `sale_id`, `quantity`, timestamps — historial de qué comprobante
    facturó cuánto de cada línea.
- `app/Http/Services/Tenant/WorkShop/WorkOrders/WorkOrderRepository.php` —
  `deleteDetailProduct` / `deleteDetailService` dejan de usarse en el update
  normal; reemplazar por upsert por `(work_order_id, product_id)`.
  `insertWorkOrderDetail` pasa a `upsertWorkOrderDetail`.
- `app/Http/Services/Tenant/WorkShop/WorkOrders/WorkOrderService.php` —
  `update()`: calcular delta de `quantity` por línea (para stock), en vez de
  revert-total + re-deduct-total.
- `app/Http/Services/Tenant/WorkShop/WorkOrders/WorkOrderValidation.php` —
  nueva regla: rechazar `nueva_quantity < invoiced_quantity` por línea.
- `app/Http/Services/Tenant/WorkShop/WorkOrders/WorkOrderService.php`
  (`invoiceStore()`) — facturar hasta el pendiente (`quantity -
  invoiced_quantity`), acumular `invoiced_quantity` en vez de flip booleano;
  registrar el detalle en `work_order_line_invoices`.
- `app/Http/Services/Tenant/Accounts/CustomerAccount/CustomerAccountService.php`
  — sin cambios de fondo (sigue sumando `quantity` total de la OT), confirmar
  que no depende de `invoiced`/`invoiced_quantity` en ningún cálculo.

## Estado de implementación

Implementado en:

- `database/migrations/tenant/2026_07_06_000003_add_invoiced_quantity_to_work_orders_detail.php`
  — columna `invoiced_quantity` + backfill de datos existentes.
- `app/Models/Tenant/WorkShop/WorkOrder/WorkOrderProduct.php` /
  `WorkOrderService.php` — `invoiced_quantity` en `$fillable`.
- `app/Http/Services/Tenant/WorkShop/WorkOrders/WorkOrderRepository.php`:
  - Nuevo `upsertWorkOrderDetail()` — usado por `update()`, actualiza líneas
    IN-PLACE (nunca borra+recrea), ajusta stock solo por el delta de
    cantidad, borra únicamente las líneas removidas por el usuario.
  - `setInvoicedWorkProducts()` / `setInvoicedWorkServices()` — acumulan
    `invoiced_quantity` en vez de pisar un booleano.
  - Removidos `deleteDetailProduct()`, `deleteDetailService()`,
    `isWorkProductInvoiced()`, `isWorkServiceInvoiced()` (sin uso tras el
    cambio).
- `app/Http/Services/Tenant/WorkShop/WorkOrders/WorkOrderValidation.php`:
  - Nueva `validationInvoicedLines()` (privada, llamada desde
    `validationUpdate()`) — rechaza bajar cantidad o quitar una línea con
    `invoiced_quantity > 0`.
  - `validationInvoice()` reescrita: compara contra el pendiente
    (`quantity - invoiced_quantity`) en vez de un flag `invoiced=true`
    todo-o-nada. De paso se corrigió un bug real que hacía fatal
    (`$work_order_id->id` sobre un int) si ese branch se llegaba a disparar.
- `app/Http/Services/Tenant/WorkShop/WorkOrders/WorkOrderService.php` —
  `update()` ya no revierte TODO el stock y borra+recrea; llama a
  `upsertWorkOrderDetail()`.

### Fuera de alcance (a propósito)

- **Tabla `work_order_line_invoices`** (historial de qué comprobante facturó
  cuánto de cada línea): NO se creó. `invoiced_sale_id` /
  `invoiced_sale_serie` se conservan como "última venta que tocó esta línea"
  (se sobrescriben en cada facturación parcial adicional), no como historial
  completo. Suficiente para cerrar los dos bugs reales reportados
  (sobre-facturación y pérdida de `invoiced_quantity` al editar); si más
  adelante se necesita trazabilidad completa de facturaciones parciales
  múltiples por línea, esa tabla es el siguiente paso natural.
- **UI del invoice-create de OT** (`sales.sale_document.create-ot`): sigue
  precargando la cantidad TOTAL de la línea (no la pendiente
  `quantity - invoiced_quantity`) cuando hay saldo parcial. El backend ya
  bloquea correctamente si se intenta facturar de más
  (`WorkOrderValidation::validationInvoice()`), pero el usuario tendría que
  editar manualmente la cantidad en el modal antes de enviar. Mejora de UX
  pendiente, no crítica para la seguridad del dato.
- **Precio de una línea ya facturada**: el fix bloquea bajar CANTIDAD por
  debajo de lo facturado, pero no restringe cambiar el PRECIO de una línea
  con `invoiced_quantity > 0`. No estaba en el alcance original de este
  plan (solo se discutió el caso de cantidad).

### Pendiente de correr

```bash
php artisan tenants:migrate
```

(migra `2026_07_06_000003_...` a todas las BDs tenant; sin `--fresh`, no
borra data).
