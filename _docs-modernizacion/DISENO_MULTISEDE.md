# Diseño Multi-Sede — TallerSuite (Fase 3)

> **Proyecto:** TallerSuite (erptaller) · **Sobre:** Laravel 12 (baseline verde)
> **Contexto:** la entidad "Sede" NO existe hoy (confirmado en VERIFICACION_SEDES.md). Se CONSTRUYE.
> **Ventaja:** libertad total de `migrate:fresh` (sin datos reales) → metemos `sede_id` en el diseño base de las tablas, no con migraciones de alteración.
> **Rama:** fase-3-multisede

---

## 0. Decisiones de negocio (tomadas con el cliente)

| Tema | Decisión |
|---|---|
| **Jerarquía** | Sede principal (matriz) + sedes adicionales. La principal puede tener privilegios (almacenes globales tipo préstamos-terceros). |
| **Almacenes** | Cada sede tiene sus PROPIOS almacenes (Principal, Mermas, Regalos...). Stock por almacén; almacén pertenece a una sede. |
| **Usuarios** | Muchos-a-muchos con sedes (pivote `sede_user`). Cada usuario tiene una sede por defecto + puede cambiar de "sede activa" con selector. |
| **Sede activa** | Entra a una sede por defecto, cambia con selector si necesita. La sede activa filtra stock, caja, ventas, series. |
| **Cajas** | Cada sede tiene sus propias cajas (apertura/cierre por sede). |
| **SUNAT** | Mismo establecimiento. SERIES distintas por sede (B001, B002, F001, F002...). Cada sede su correlativo independiente. |

---

## 1. Modelo de datos

### 1.1 Tabla nueva: `sedes` (tenant)
```
sedes
- id
- nombre                (ej. "TIENDA PRINCIPAL", "SUCURSAL SAN RAMON")
- codigo                (corto, ej. "PRIN", "SR" — para UI/reportes)
- es_principal          (boolean — solo una true por tenant)
- direccion, telefono, ubigeo (datos de la sede)
- estado               (activo/inactivo)
- timestamps
```
> La sede vive en la BD del TENANT (cada empresa tiene sus sedes), no en la central.

### 1.2 Tabla pivote nueva: `sede_user` (tenant)
```
sede_user
- id
- sede_id      (FK sedes)
- user_id      (FK users)
- es_default   (boolean — la sede por defecto de ese usuario)
- timestamps
- UNIQUE(sede_id, user_id)
```

### 1.3 Columna nueva `sede_id` en tablas existentes
Tablas que pasan a tener `sede_id` (FK a sedes):

| Tabla | Por qué |
|---|---|
| `warehouses` (almacenes) | Cada almacén pertenece a una sede. |
| `petty_cashes` (cajas) | Cada caja pertenece a una sede. |
| `document_serializations` (series) | Cada serie pertenece a una sede (B001=principal, B002=sede2...). |
| `sales_documents` (comprobantes) | Cada venta se emite desde una sede (desnormalizado). |
| `work_orders` (órdenes de trabajo) | Cada OT pertenece a una sede. |
| `quotes` (cotizaciones) | Cada cotización pertenece a una sede. |
| `purchase_documents` (compras) | Cada compra ingresa a una sede. |
| `notes_income` / `notes_release` | Movimientos de inventario por sede. |
| Otros documentos transaccionales | Todo documento "nace" en una sede. |

> **Principio (tu ADN de CalzadoPro):** DESNORMALIZAR en documentos — guardar `sede_id` (y opcionalmente nombre de sede) en cada documento para que sea autónomo y los reportes filtren por SQL sin JOINs frágiles.

### 1.4 Lo que NO lleva `sede_id` (datos compartidos del tenant)
- Catálogo de productos, categorías, marcas, modelos → compartidos por todo el tenant (el producto existe para todas las sedes; lo que cambia es el STOCK por almacén/sede).
- Clientes, proveedores → compartidos.
- Tipos de pago, bancos → ver decisión §1.5.

### 1.5 Punto a decidir más adelante (no bloquea el arranque)
- **Tipos de pago y cuentas bancarias:** ¿globales del tenant o por sede? (El cliente mencionó quererlos por sede, pero conviene confirmar si una cuenta bancaria puede ser compartida). Se diseña con `sede_id` nullable: null = global, con valor = exclusiva de esa sede. **Decidir al implementar ese módulo.**

---

## 2. Concepto de "Sede Activa"

- Al loguear, el usuario entra en su **sede por defecto** (`sede_user.es_default`).
- Un **selector de sede** (en el header, estilo CalzadoPro) permite cambiar entre las sedes donde el usuario está habilitado.
- La sede activa se guarda en **sesión** (`session('sede_activa_id')`).
- **Todo se filtra por la sede activa:** stock disponible, cajas, series de comprobante, listados de ventas/OT, dashboards.
- **Trait reutilizable** `HasSedeActiva` (tu ADN: una sola fuente de verdad) que expone `sedeActivaId()`, `cambiarSede()`, y el scope de filtrado. Lo usan todos los componentes/controladores que filtran por sede.

---

## 3. Series por sede (SUNAT)

- `document_serializations` pasa a tener `sede_id`.
- Cada sede tiene su correlativo por tipo de comprobante: principal → B001/F001/T001, sede 2 → B002/F002/T002, etc.
- Al emitir un comprobante, se toma la serie de la **sede activa**.
- Mismo establecimiento SUNAT para todas (no se declara cada sede como anexo): el XML Greenter usa el establecimiento de la empresa; lo que cambia es la serie.
- **Cuidado:** el correlativo es por (sede, tipo de documento). El bloqueo/incremento debe ser atómico por esa combinación.

---

## 4. Usuarios por defecto del tenant (3 roles)

Al crear un tenant (en `insertDataTenant`), sembrar 3 usuarios con sus roles — todos asignados a la sede principal por defecto:

| Usuario | Rol | Acceso |
|---|---|---|
| `admin@tallersuite.com` | Administrador | Todo. Ve todas las sedes. Ve precios y costos. |
| (ventas) | Ventas | Maneja todo lo operativo (ventas, cobros, productos). VE precios. |
| (técnico) | Técnico | Solo órdenes de servicio: crea/edita/modifica OT. NO ve precios (ni de OT ni de productos). |

> El **técnico-sin-precios** es análogo al "Costo solo administrador" de CalzadoPro (`puedeVerCostos()`), pero aplicado a PRECIOS. Sale como permiso granular (estilo módulo Préstamos), no hardcodeado.
> Definir emails/passwords por defecto de ventas y técnico al implementar.

---

## 5. Plan de implementación por ETAPAS

Cada etapa = un bloque de trabajo con su prompt, probado antes de pasar a la siguiente. Con `migrate:fresh` entre etapas (sin datos reales).

### Etapa 1 — Cimiento: entidad Sede
- Migración `sedes` + `sede_user`.
- Modelo `Sede` + relaciones (Sede↔User M:N, Sede↔Almacenes 1:N).
- Seeder: al crear tenant, crear la sede principal + asignar el admin a ella.
- CRUD de Sedes en Mantenimiento (listado + crear/editar, estilo SISCOMFAC).
- **Probar:** crear una sede adicional desde la UI.

### Etapa 2 — Sede activa + selector
- Trait `HasSedeActiva` + sesión.
- Selector de sede en el header (solo sedes del usuario).
- Middleware que setea la sede activa por request.
- **Probar:** cambiar de sede y que persista.

### Etapa 3 — Almacenes y stock por sede
- `sede_id` en `warehouses`. Al crear sede → crear sus almacenes base (Principal, Mermas, Regalos).
- Stock/consultas filtran por almacenes de la sede activa.
- **Probar:** stock separado entre dos sedes.

### Etapa 4 — Cajas por sede
- `sede_id` en `petty_cashes`. Apertura/cierre por sede activa.
- **Probar:** caja independiente por sede.

### Etapa 5 — Series por sede (SUNAT)
- `sede_id` en `document_serializations`. Series B001/B002 por sede.
- Emisión toma la serie de la sede activa.
- **Probar:** emitir desde dos sedes con series distintas.

### Etapa 6 — Documentos transaccionales con sede
- `sede_id` (desnormalizado) en ventas, OT, cotizaciones, compras, notas.
- Listados/reportes/dashboard filtran por sede activa.
- **Probar:** documentos separados por sede en reportes.

### Etapa 7 — Roles y usuarios por defecto
- Los 3 usuarios (admin/ventas/técnico) en el provisioning del tenant.
- Permiso granular "ver precios" (técnico NO).
- **Probar:** loguear como técnico y confirmar que no ve precios.

---

## 6. Riesgos y cuidados

- **`migrate:fresh` borra todo** — perfecto ahora (sin datos), pero a partir del primer cliente real ya no se podrá. El esquema de sedes debe quedar BIEN antes de que entre data real.
- **El provisioning de tenant** (`insertDataTenant`) hay que ampliarlo para sembrar sede principal + sus almacenes + los 3 usuarios. Es el punto que ya tocamos (y arreglamos) en el baseline.
- **No romper lo que funciona:** cada `sede_id` nuevo debe tener un default razonable durante la transición, para que el código existente no explote antes de adaptarlo.
- **Filtrado por sede:** asegurarse de que NINGUNA consulta de stock/caja/ventas quede sin filtrar por sede activa (fuga entre sedes).
- **Trait reutilizable** para la sede activa — una sola fuente de verdad, no repetir la lógica en cada pantalla.

---

*Diseño de referencia para Fase 3 (multi-sede). Decisiones de negocio confirmadas con el cliente. Implementación por etapas, cada una probada antes de la siguiente, con migrate:fresh disponible mientras no haya datos reales.*
