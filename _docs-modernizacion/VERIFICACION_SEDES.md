# Verificación de Entidad "SEDE / SUCURSAL" — TallerSuite (erptaller)

> **Tipo:** Investigación de solo lectura
> **Fecha:** 2026-06-26
> **Pregunta:** ¿El sistema modela una entidad **Sede/Sucursal/Branch** (local físico por encima del almacén), o solo maneja **almacenes sueltos** sin agrupación por sede?
> **Método:** Inspección estática de migraciones (`database/migrations/tenant/`), modelos (`app/Models/**`) y vistas. Sin ejecución de comandos que cambien estado.

---

## VEREDICTO (una línea)

🔴 **NO existe entidad Sede. El sistema solo maneja almacenes planos (`warehouses`), sin ninguna agrupación por sede/sucursal. El multi-sede hay que CONSTRUIRLO, no solo completarlo.**

**Base de la afirmación:** la búsqueda de los términos `sede`, `sucursal`, `branch`, `establecimiento`/`establishment` en `database/migrations/tenant/` y en `app/Models/` arrojó **0 coincidencias reales**. Las únicas apariciones en el repo (`establishment` en `Greenter/example/factura.php`, `branch` en `lang/` y `composer.lock`) son ajenas al dominio del negocio.

---

## Tabla Resumen — ¿Cada área está atada a una Sede?

| Área | ¿Atada a Sede? | Evidencia (archivo:línea) |
|---|---|---|
| **Almacén** (`warehouses`) | **NO** | Solo `id`, `descripcion`, `estado`. Sin `sede_id` ni `company_id`. [create_warehouses_table.php:14-19](database/migrations/tenant/2024_10_27_113119_create_warehouses_table.php#L14) |
| **Stock** (`warehouse_products`) | **NO** (por almacén, sin sede) | PK compuesta `warehouse_id`+`product_id`, columna `stock`. El stock cuelga del almacén; el almacén no cuelga de ninguna sede. [create_warehouse_products_table.php:15-26](database/migrations/tenant/2024_10_27_113417_create_warehouse_products_table.php#L15) |
| **Caja** (`petty_cashes`) | **NO** | `name`, `type`(CAJA/FICTICIO), `status`, auditoría. Sin `sede_id`. [create_petty_cashes_table.php:14-27](database/migrations/tenant/2023_09_29_010753_create_petty_cashes_table.php#L14) |
| **Ventas** (`sales_documents`) | **NO** (se ata a CAJA, no a sede) | Referencia a `petty_cash_id`/`petty_cash_book_id`, `user_recorder_id`, `serie`/`correlative`, `vehicle_id`. Sin `sede_id`. [create_sales_documents_table.php:34-66](database/migrations/tenant/2024_10_27_165737_create_sales_documents_table.php#L34) |
| **Numeración** (`document_serializations`) | **NO** (por **empresa**, global del tenant) | La serie se ata a `company_id` → `companies`, no a sede. [create_document_serializations_table.php:16-20](database/migrations/tenant/2023_10_06_211207_create_document_serializations_table.php#L16) |
| **Usuarios** (`users`) | **NO** | Solo `collaborator_id`. Sin `sede_id` → el sistema **no piensa "por sede"**. [create_users_table.php:14-28](database/migrations/tenant/2014_10_12_000000_create_users_table.php#L14) |
| **Colaboradores** (`collaborators`) | **NO** | `position_id`, datos personales/salario. Sin `sede_id`. [create_collaborators_table.php:14-44](database/migrations/tenant/2014_10_11_000001_create_collaborators_table.php#L14) |
| **Configuración** (`configuration`) | **NO** (global del tenant) | Tabla clave-valor (`description`,`property`,`symbol`). Sin `sede_id`; los parámetros son globales. [create_configuration_table.php:14-20](database/migrations/tenant/2024_12_03_150602_create_configuration_table.php#L14) |
| **Selector de Sede en UI** | **NO existe** | Sin selector de sede/almacén activo en `resources/views/layouts/`. El selector de `warehouse` solo aparece **dentro de documentos** (cotización/OT/venta/compra) para elegir de qué almacén sale el stock, no como contexto global. |

---

## Detalle por punto investigado

### 1. Entidad Sede
**No existe** ninguna tabla ni modelo `sede(s)`, `branch(es)`, `sucursal(es)`, `establecimiento`, `location`, `store` ni `local` en el dominio del negocio. Confirmado por grep sobre migraciones y modelos (0 coincidencias reales).

### 2. Almacenes y su relación
- **Columnas de `warehouses`:** `id`, `descripcion` (string 120), `estado` (enum `ACTIVO`/`ANULADO`), `created_at`, `updated_at`. **Eso es todo.**
- **NO** tiene `sede_id`/`branch_id`/`sucursal_id`, ni siquiera `company_id`: el almacén **cuelga directo de la base de datos del tenant** (el aislamiento por empresa es físico, una BD por tenant), sin sede intermedia.
- **Tipos de almacén:** **NO existen.** No hay columna `tipo`/`type` ni enum (principal, mermas, regalos, tránsito…). Todos los almacenes son homogéneos; solo se distinguen por `descripcion` libre y `estado`.
- **Modelo `Warehouse`** ([app/Models/Tenant/Warehouse.php](app/Models/Tenant/Warehouse.php)): trivial (`$table='warehouses'`, `$guarded=['']`), sin relaciones definidas → no hay `belongsTo(Sede)`.

### 3. Stock
- `warehouse_products` modela stock **por almacén** (`warehouse_id` + `product_id` + `stock`, PK compuesta).
- Como el almacén no está atado a sede, **el stock tampoco**. La segmentación máxima existente es "almacén", no "sede".

### 4. Cajas
- `petty_cashes` no tiene `sede_id` ni `branch_id`. Las cajas **no distinguen sede**; son cajas planas del tenant con `type` (CAJA/FICTICIO) y `status`.

### 5. Ventas y documentos
- `sales_documents` **no** referencia sede. La venta se ancla a una **caja** (`petty_cash_id`) y a su **movimiento** (`petty_cash_book_id`), al usuario registrador y a la serie/correlativo.
- `document_serializations` ata la serie a **`company_id`** (empresa), no a sede. Dado que el tenant opera con una empresa (`companies` tiene `ruc`/`business_name` **únicos**), **la numeración de comprobantes es efectivamente global del tenant, no por sede**.

### 6. Usuarios / Colaboradores
- `users` solo enlaza con `collaborator_id`; `collaborators` solo con `position_id`. **Ningún `sede_id`** en ninguna de las dos.
- **Implicación clave:** el modelo de datos **no contempla** "este usuario pertenece a la sede X". Hoy todo usuario ve/opera sobre todo el tenant.

### 7. Configuración
- `configuration` es una tabla **clave-valor global** (`description`, `property`, `symbol`). Los parámetros (p. ej. IGV, símbolos, propiedades) son **del tenant completo**, no parametrizables por sede.
- Catálogos relacionados (métodos de pago, cuentas bancarias) tampoco tienen `sede_id` (no aparece en sus migraciones), por lo que son globales del tenant.
- Nota: el IGV vive además en `companies.igv` ([create_companies_table.php:45](database/migrations/tenant/2023_09_30_020405_create_companies_table.php#L45)) → a nivel empresa, no sede.

### 8. Menú / UI
- **No hay selector de sede activa** en el layout/navbar ([resources/views/layouts/](resources/views/layouts/)). El sistema asume **una sola ubicación**.
- Los `select` de almacén encontrados en vistas (`workshop/quotes`, `workshop/work_orders`, `sales/sale_document`, `purchases/...`) son selectores **a nivel de línea de documento** (de qué almacén descontar/ingresar stock), no un contexto de sede global.

### Sobre `companies` como posible proxy de sede
La tabla `companies` (dentro de la BD del tenant) guarda **una empresa** con dirección fiscal única, `lat`/`lng`, RUC y razón social **únicos** ([create_companies_table.php:17-32](database/migrations/tenant/2023_09_30_020405_create_companies_table.php#L17)). No está diseñada como colección de locales: representa **la** empresa del tenant, no un conjunto de sedes. Por tanto **no funciona como sustituto de "Sede"**.

---

## IMPLICANCIA: qué se necesita para introducir Multi-Sede

Al **no existir** la entidad Sede, el multi-sede es **construcción nueva**, no un ajuste menor.

### A. Tabla nueva
- **`sedes`** (en BD tenant): `id`, `company_id`, `nombre`, `codigo_establecimiento` (relevante para SUNAT), `direccion`, `ubigeo`, `telefono`, `estado`, timestamps.

### B. Tablas que necesitarían un `sede_id` nuevo (FK)
| Tabla | Motivo |
|---|---|
| `warehouses` | Agrupar almacenes bajo una sede (cambio estructural base). |
| `petty_cashes` | Que cada caja pertenezca a una sede. |
| `document_serializations` | Numeración/series **por sede** (clave para SUNAT: el código de establecimiento forma parte de la serie). |
| `sales_documents` | Atribuir cada comprobante a su sede de emisión (directo o vía caja). |
| `users` y/o `collaborators` | Pertenencia del usuario a una o varias sedes (define qué ve/opera). |
| `notes_income` / `notes_release` | Movimientos de inventario contextualizados por sede. |
| `appointments` (citas) y `work_orders` (OT) | Si las sedes atienden agendas/talleres distintos. |
| `configuration` (o tabla pivote) | Si algún parámetro debe variar por sede; hoy es global. |

### C. Módulos / áreas afectadas al introducir multi-sede
1. **Inventario / Kardex:** stock, kardex y kardex valorizado pasan a consolidarse y filtrarse por sede; SP `sp_kardex` y función `calcular_stock` deberían considerar la sede.
2. **Cajas:** apertura/cierre, egresos y consolidados por sede; middleware `verificar.caja` ligado a la sede activa.
3. **Ventas / Facturación SUNAT:** series por sede (código de establecimiento), reportes de ventas segmentados.
4. **Usuarios / Permisos:** alcance de usuario por sede; posible "sede activa" en sesión + filtros automáticos (global scopes).
5. **Compras:** notas de ingreso/salida y documentos de compra por sede/almacén.
6. **Reportes y Dashboard:** todos los agregados (ventas, stock mínimo, contable) requerirían dimensión "sede".
7. **UI / Navegación:** nuevo **selector de sede activa** en el navbar y CRUD de sedes en Mantenimiento.
8. **Aprovisionamiento:** seeders/altas deberían crear al menos una "Sede principal" por defecto y migrar los almacenes/cajas existentes a ella.

> **Conclusión operativa:** introducir multi-sede es un cambio **transversal** (modelo de datos + lógica de stock/caja/series + permisos + UI), no una feature aislada. El punto de partida obligatorio es crear la entidad `sedes` y decidir la estrategia de pertenencia (usuario↔sede) y de **series por establecimiento** para no romper la facturación electrónica.

---

## Anexo — Evidencia inspeccionada
- Migraciones: `warehouses`, `warehouse_products`, `petty_cashes`, `sales_documents`, `document_serializations`, `users`, `collaborators`, `configuration`, `companies` (todas en `database/migrations/tenant/`).
- Modelos: [Warehouse.php](app/Models/Tenant/Warehouse.php), [Configuration.php](app/Models/Tenant/Configuration.php).
- Búsquedas: grep de `sede|sucursal|branch|establecimiento` y `sede_id|branch_id|sucursal_id|...` sobre migraciones de tenant y `app/Models/` → sin coincidencias de dominio.
- UI: `resources/views/layouts/` (sin selector de sede); selectores de almacén solo a nivel de documento.

*Informe de solo lectura. No se modificó ningún archivo del proyecto salvo la creación de este documento.*
