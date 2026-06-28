# Diseño — Permisos por rol (conectar Spatie al menú y a las rutas)

> **Solo papel.** El diagnóstico confirmó: el menú ([nav.blade.php](resources/views/layouts/body/aside/nav.blade.php)) pinta el árbol `modules/module_children` **sin filtrar por rol**, y los **48 permisos Spatie** (bien estructurados por dominio) **no los lee nadie** — ni el menú ni las rutas (sin middleware `permission:`). Dos sistemas paralelos desconectados. Este plan los cablea.

Estado actual: `admin` = 48 permisos (todos), `ventas`/`tecnico` = 0 (las asignaciones manuales previas se borraron en el último `migrate:fresh`; se re-asignan por la pantalla RoleController, que ya funciona).

---

## 1. Mapeo nodo del menú → permiso

Los `route_name` del árbol **NO** coinciden con los nombres de permiso (ej. nodo `cajas.caja` ↔ permiso `cajas.ver`). El mapeo debe ser **explícito**.

| Módulo | Nodo (route_name) | Permiso que lo gobierna |
|---|---|---|
| Dashboard | dashboard.dashboard.index | `dashboard.ver` |
| **Cajas** | cajas.caja | `cajas.ver` |
| | movimientos_caja.apertura_cierre | `cajas.abrir_cerrar` |
| | cajas.egreso | `cajas.egresos` |
| **Taller** | taller.cotizaciones.index | `taller.cotizaciones.ver` |
| | taller.ordenes_trabajo.index | `taller.ot.ver` |
| | taller.servicios.index | `taller.servicios.gestionar` |
| | taller.colores/years/marcas/modelos.index | `taller.maestros.gestionar` |
| | taller.vehiculos.index | `taller.vehiculos.gestionar` |
| | taller.citas.index | `taller.citas.gestionar` |
| **Ventas** | ventas.comprobante_venta | `ventas.ver` |
| | ventas.cliente | `ventas.clientes.gestionar` |
| | ventas.metodo_pago | ⚠️ **HUECO** (no hay permiso) |
| | ventas.condiciones_pago.index | ⚠️ **HUECO** |
| **Cuentas** | cuentas.cliente.index | `cuentas.cxc.ver` |
| | cuentas.proveedor.index | `cuentas.cxp.ver` |
| **Inventario** | inventarios.productos.* (Categorías/Marca/Producto) | `inventario.productos.gestionar` |
| | inventarios.almacenes.index | ⚠️ **HUECO** (no hay `inventario.almacenes`) |
| | inventory.kardex.index | `inventario.kardex.ver` |
| | inventarios.inventario | ⚠️ **HUECO** (no hay `inventario.ver` general) |
| | inventarios.kardex_valorizado | `inventario.ver_costos` |
| | inventarios.nota_ingreso / nota_salida | `inventario.notas.gestionar` |
| **Compras** | compras.proveedor / documento_compra.index | `compras.ver` |
| **Reportes** | reportes.reporte_venta | `reportes.ventas` |
| | reportes.reporte_contable | `reportes.contable` |
| **Mantenimiento** | mantenimientos.empresa | `mantenimiento.empresa.gestionar` |
| | mantenimientos.cuentas.index | ⚠️ **HUECO** (cuentas bancarias) |
| | mantenimientos.plan | ⚠️ **HUECO** (`planes` es nivel landlord) |
| | mantenimientos.cargos.index | `mantenimiento.cargos.gestionar` |
| | mantenimientos.colaboradores.index | `mantenimiento.colaboradores.gestionar` |
| | mantenimientos.horario | ⚠️ **HUECO** |
| | mantenimientos.configuracion | `mantenimiento.configuracion.gestionar` |
| | mantenimientos.sedes.index | `mantenimiento.sedes.gestionar` |
| **Seguridad** | mantenimientos.usuario.index | `mantenimiento.usuarios.gestionar` |
| | mantenimientos.rol | `mantenimiento.roles.gestionar` |
| **Consultas** | consultas.creditos / vehiculos.index / notificaciones.index | `consultas.ver` |

### Huecos (nodos sin permiso) — 7
`ventas.metodo_pago`, `ventas.condiciones_pago`, `inventarios.almacenes`, `inventarios.inventario`, `mantenimientos.cuentas`, `mantenimientos.plan`, `mantenimientos.horario`.
**Solución:** crear ~5 permisos nuevos (`ventas.config.gestionar` para métodos/condiciones; `inventario.almacenes.gestionar`; `inventario.ver`; `mantenimiento.cuentas_bancarias.gestionar`; `mantenimiento.horario.gestionar`) **o** mapearlos a un permiso paraguas existente. `mantenimientos.plan` es landlord (no debería estar en el menú tenant — candidato a sacar). → **PermissionSeeder** suma estos permisos.

### Dónde se guarda el mapeo (recomendado)
**Columna `permission` (nullable) en `module_children` y `module_grand_children`** (y opcional en `modules` para el padre). Ventajas: vive con el nodo (una sola fuente), data-driven, el menú ya itera esas tablas, y el provisioning ya las copia. Se siembra en **ModuleSeeder** (un campo más por nodo).
- Alternativa descartada: un `config/permissions_map.php` (route_name → permiso) → estructura paralela a mantener, se desincroniza del árbol.

---

## 2. Filtrado del menú

[nav.blade.php](resources/views/layouts/body/aside/nav.blade.php#L105): envolver cada nivel en `@can`:
```blade
@foreach ($modules as $module)
    @canany($module->children->pluck('permission')->filter()->all())   {{-- el módulo se ve si ve ALGÚN hijo --}}
        <li> ... 
        @foreach ($module->children as $child)
            @can($child->permission ?? '__none__')
                ... (igual para grandchildren)
            @endcan
        @endforeach
    @endcanany
@endforeach
```
- **Módulo padre**: se muestra solo si el usuario puede ver al menos un hijo (`@canany`), así no quedan módulos vacíos.
- **Admin**: ver punto 5 (Gate::before → `@can` siempre true).

---

## 3. Protección de rutas (lo crítico — hoy NO existe)

Hoy ninguna ruta tiene middleware de permiso → **ventas entra por URL** aunque se oculte el menú. Hay que proteger **por grupo** (no ruta por ruta).

### Registro (falta)
En `app/Http/Kernel.php` (`$middlewareAliases`): agregar los de Spatie —
```php
'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
```
(hoy solo existe el `'can'` nativo, sin uso).

### Mapeo grupo de rutas → permiso (8 archivos tenant + web.php)
| Archivo | Grupo(s) | Middleware |
|---|---|---|
| `cash/web.php` (41) | cajas / movimientos / egresos | `permission:cajas.ver` (+ específicos por acción si se quiere granular) |
| `taller/web.php` (118) | cotizaciones, OT, servicios, maestros, vehículos, citas | `permission:taller.*` por subgrupo |
| `sales/web.php` (60) | ventas | `permission:ventas.ver` |
| `inventory/web.php` (101) | productos, almacenes, kardex, notas | `permission:inventario.*` por subgrupo |
| `accounts/web.php` (29) | cuentas cxc/cxp | `permission:cuentas.cxc.ver` / `cxp.ver` |
| `mantenimiento/web.php` (97) | empresa, cargos, colaboradores, config, sedes, **usuarios, roles** | `permission:mantenimiento.*` por subgrupo |
| `dashboard/web.php` (15) | dashboard | `permission:dashboard.ver` |
| `queries/web.php` (21) | consultas | `permission:consultas.ver` |
| `web.php` (202) | reportes, reservas, sede/cambiar, etc. | `permission:reportes.*` / `reservas.*` (sede/cambiar queda libre) |

**Tamaño:** ~9 archivos, ~30-40 grupos a anotar. Es **por grupo** (una línea `->middleware('permission:X')` por grupo), no por ruta. Medio.
**Granularidad:** para el menú alcanza el permiso de visibilidad (`*.ver`/`gestionar`). Las acciones finas (`ventas.anular`, `ot.facturar`) se aplican en botones/rutas puntuales en una iteración posterior — **no bloqueante**.

---

## 4. Riesgo, tamaño y default seguro

### Archivos que toca el cambio completo
- 1 migración (columna `permission` en module_children/grand_children).
- `ModuleSeeder` (asignar permiso por nodo) + `PermissionSeeder` (~5 permisos faltantes).
- `nav.blade.php` (filtro `@can`).
- `app/Http/Kernel.php` (registrar middleware) + ~9 archivos de rutas (middleware por grupo).
- `AuthServiceProvider` (Gate::before admin).
- Re-provisión del demo (copia el árbol con permisos).
→ **~14 archivos**. Tamaño **medio**, bajo riesgo lógico (es cableado), pero **alto cuidado**: una ruta sin proteger = hueco de seguridad.

### Default seguro (clave)
- **Menú**: nodo **sin permiso mapeado** → se **OCULTA** a no-admin (fail-closed). El admin lo ve igual (Gate::before). Así nada queda accesible "por olvido" en el menú.
- **Rutas**: un grupo **sin middleware** = **abierto a todo autenticado** (comportamiento actual) → es el **hueco real**. Mitigación: checklist de que **todos** los grupos quedan con `permission:`; lo que deba ser libre (ej. `sede/cambiar`, logout) se deja explícito. Idealmente, un test que recorra las rutas y verifique que ninguna del set sensible quede sin middleware.

### Cómo se prueba
Por cada rol (admin, ventas, tecnico):
1. **Menú**: loguea → ve solo sus módulos (admin todo).
2. **URL directa**: pega la URL de un módulo ajeno (ej. ventas → `mantenimientos.rol`) → **403/redirect** (no entra).
3. **Lo suyo funciona**: ventas entra a Cajas/Ventas/Taller; tecnico a OT; etc.
4. Multi-sede sigue: ventas ve Cajas pero solo las de su sede (eso ya lo da `sedesDisponibles`, no el permiso).

### Orden de implementación (por capas, commit por bloque)
1. **P1 — Permisos faltantes** (PermissionSeeder, ~5) + asignar permiso a cada nodo (migración columna + ModuleSeeder) + re-provisión. *(Estructura, no filtra aún.)*
2. **P2 — Admin bypass** (Gate::before) + **menú** (`@can` en nav.blade). *(Ya se ve filtrado; rutas aún abiertas.)*
3. **P3 — Rutas** (registrar middleware Spatie + `permission:` por grupo). *(El candado real.)*
→ Rutas **al final** y como bloque propio: es lo más delicado (puede dejar a alguien afuera de algo que necesita). Menú primero permite ver el mapeo en acción sin bloquear.

---

## 5. Casos especiales

- **admin / full_access**: `Gate::before(fn($user) => $user->hasRole('admin') ? true : null)` en `AuthServiceProvider`. Una línea: el admin pasa **todo** `@can` y todo `permission:`. (admin ya tiene los 48, pero `Gate::before` lo blinda aunque falte alguno nuevo.) Retornar `null` (no `false`) para no romper a los demás.
- **Módulo Seguridad** (Usuarios/Roles, recién movidos): incluido en el mapeo (`mantenimiento.usuarios.gestionar` / `mantenimiento.roles.gestionar`). Ya que tocamos menú+seeder, se mapea en la misma pasada.
- **Frontera permiso ↔ sede** (importante): son **ortogonales**.
  - **Permiso (Spatie)** = QUÉ puede hacer (`cajas.ver`). Gobierna menú + acceso a la ruta.
  - **Sede (`sedesDisponibles`)** = QUÉ datos ve (las cajas de SUS sedes). Ya implementado (Cajas Capa B, almacenes, series).
  - → Un vendedor con `cajas.ver` ve el menú Cajas y entra a la ruta; el filtro por sede limita CUÁLES cajas. **No se mezclan**: el permiso no sabe de sede, la sede no sabe de permiso. Cada uno su capa.

---

## Resumen
Cablear = (a) cada nodo del menú lleva su permiso (columna + seeder), (b) `nav.blade` filtra con `@can`, (c) `Gate::before` para admin, (d) `permission:` en todos los grupos de rutas. ~14 archivos, medio, bajo riesgo lógico / alto cuidado en rutas. Default: menú fail-closed, rutas con checklist de cobertura total. Orden: permisos+mapeo → menú+admin → rutas. La sede es capa aparte (ya hecha), no se toca.

*Documento de solo lectura. Cero código aplicado.*
