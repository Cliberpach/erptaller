# Checklist de Upgrade — Laravel 10 → 11 → 12 / PHP 8.1 → 8.3/8.4

> **Proyecto:** TallerSuite (erptaller) · **Hoy:** Laravel `v10.48.14`, PHP `^8.1` (entorno local detectado: **PHP 8.3.30**)
> **Fecha de análisis:** 2026-06-26 · **Tipo:** solo lectura (no se modificó `composer.json` ni dependencias)
> **Fuente de versiones:** `composer.lock` (constraints reales de cada paquete).

---

## 1. Resumen — ¿Directo o con bloqueantes?

**El upgrade es VIABLE y NO tiene bloqueantes "duros"** (ningún paquete está abandonado ni sin versión compatible). Sí hay **4 paquetes que requieren salto de versión MAYOR** para entrar en Laravel 11, porque su línea actual tope en Laravel 10:

- `laravel/jetstream` (v4 → **v5**)
- `laravel/sanctum` (v3 → **v4**)
- `spatie/laravel-permission` (v5 → **v6**)
- `yajra/laravel-datatables` (v10 → **v11** para L11, **v12** para L12)

Estos 4 son **"actualizar obligatorio"**, no bloqueantes definitivos (todos tienen release compatible). El resto ya soporta L11 con la versión instalada.

**Cambios estructurales (Kernel/bootstrap/providers):** son **OPCIONALES**. Laravel 11/12 mantiene compatibilidad con el esqueleto de L10 (las clases `Http/Kernel`, `Console/Kernel`, `Exceptions/Handler` siguen existiendo en el framework). Recomendación: **subir el framework SIN reestructurar primero**, y modernizar el esqueleto después como tarea aparte.

**PHP:** efectivamente ya se exige **≥ 8.2** (lo imponen `spatie/laravel-multitenancy` y `luecano/numero-a-letras`, ambos `php ^8.2`). PHP 8.3/8.4 es compatible con L11/L12. No se hallaron usos que rompan en 8.3/8.4 (0 parámetros nullable implícitos, sin `utf8_encode`/`create_function`/`each()`).

---

## 2. Tabla de compatibilidad de paquetes

Leyenda: **OK** = la versión instalada ya soporta el objetivo · **ACTUALIZAR** = requiere subir versión · **BLOQUEANTE** = sin versión compatible.

| Paquete | Versión actual | Constraint actual (lock) | ¿Soporta L11? | ¿Soporta L12? | Veredicto |
|---|---|---|---|---|---|
| `laravel/framework` | v10.48.14 | `php ^8.1` | — | — | Subir a 11 y luego 12 |
| `laravel/jetstream` | **v4.3.1** | `illuminate/support ^10.17` | ❌ (solo L10) | ❌ | **ACTUALIZAR → v5** (v5 soporta L11+L12) |
| `laravel/fortify` | v1.21.3 | `illuminate/support ^10\|^11` | ✅ | ⚠️ revisar | ACTUALIZAR a 1.x reciente para L12 |
| `laravel/sanctum` | **v3.3.3** | `illuminate/contracts ^9.21\|^10` | ❌ | ❌ | **ACTUALIZAR → v4** (v4 soporta L11+L12) |
| `livewire/livewire` | v3.5.1 | `illuminate/support ^10\|^11` | ✅ | ⚠️ usar 3.5.x reciente | ACTUALIZAR patch para L12 |
| `spatie/laravel-multitenancy` | 3.2.0 | `illuminate/support ^10\|^11`, `php ^8.2` | ✅ | ❌ (L12 = v4) | OK para L11; **ACTUALIZAR → v4** para L12 |
| `spatie/laravel-permission` | **5.11.1** | `illuminate/contracts …^10` | ❌ (solo L10) | ❌ | **ACTUALIZAR → v6** (v6 soporta L11+L12) |
| `yajra/laravel-datatables(-oracle)` | **v10.0.0 / v10.11.4** | `illuminate/support ^9\|^10` | ❌ | ❌ | **ACTUALIZAR → v11 (L11) / v12 (L12)** |
| `barryvdh/laravel-dompdf` | v3.0.0 | `illuminate/support ^9\|^10\|^11` | ✅ | ⚠️ (L12 = v3.1) | OK L11; ACTUALIZAR → v3.1 para L12 |
| `maatwebsite/excel` | 3.1.61 | `illuminate/support …^11` | ✅ | ⚠️ usar 3.1.6x reciente | OK L11; revisar release L12 |
| `tightenco/ziggy` | v2.6.0 | `laravel/framework >=9.0` | ✅ | ✅ | OK (nota: paquete migró a `tighten/ziggy`) |
| `pusher/pusher-php-server` | 7.2.7 | `php ^7.3\|^8.0` | ✅ (agnóstico) | ✅ | OK |
| `greenter/lite` | v5.1.0 | `php >=7.4` (sin Illuminate) | ✅ (agnóstico) | ✅ | OK — no depende del framework |
| `luecano/numero-a-letras` | v4.0.0 | `php ^8.2` (sin Illuminate) | ✅ | ✅ | OK |

> ⚠️ **Hallazgo lateral (pre-existente, no del upgrade):** `InvoiceController` usa `SimpleSoftwareIO\QrCode\Facades\QrCode` ([app/Http/Controllers/Tenant/InvoiceController.php](app/Http/Controllers/Tenant/InvoiceController.php)) pero `simplesoftwareio/simple-qrcode` **NO está en `composer.lock`** (0 coincidencias). Verificar antes del upgrade: o falta declarar la dependencia, o ese código no se ejecuta. Conviene resolverlo aparte para no confundirlo con un fallo del upgrade.

> Las marcas "⚠️ revisar/usar reciente" para L12 dependen de releases posteriores a este análisis; **confirmar en Packagist** la versión exacta que añade soporte L12 al hacer el salto.

---

## 3. Cambios estructurales (Kernel, bootstrap, providers, excepciones)

**Estado actual = esqueleto clásico de Laravel 10.** No existe `bootstrap/providers.php` (confirmado: solo `bootstrap/app.php` + `bootstrap/cache/`). Todo lo siguiente **sigue funcionando en L11/L12 sin tocarlo**; migrarlo es modernización opcional:

| Elemento | Estado actual (evidencia) | Acción en L11/L12 |
|---|---|---|
| **HTTP Kernel** | [app/Http/Kernel.php](app/Http/Kernel.php): grupos `web`/`api`, global stack, `$middlewareAliases` (L10) **+** un bloque `$routeMiddleware` (nombre deprecado) con los aliases custom **`validar.plan`** y **`verificar.caja`** ([Kernel.php:73-77](app/Http/Kernel.php#L73)) | Funciona tal cual. Si se moderniza: mover middleware y los 2 aliases custom a `bootstrap/app.php` → `->withMiddleware(fn($m) => $m->alias([...]))`. **Unificar `$routeMiddleware` dentro de `$middlewareAliases`** (ya hoy es confuso tenerlos separados). |
| **Console Kernel** | [app/Console/Kernel.php](app/Console/Kernel.php): `schedule()` con **`alerts:dispatch`** (`->everyMinute()->withoutOverlapping()`) y `commands()` cargando `app/Console/Commands` + `routes/console.php` | Funciona tal cual. Comandos detectados: **`alerts:check`** ([CheckAdvanceAlerts.php:13](app/Console/Commands/CheckAdvanceAlerts.php#L13)) y **`alerts:dispatch`** ([DispatchAdvanceAlerts.php:13](app/Console/Commands/DispatchAdvanceAlerts.php#L13)). Si se moderniza: el schedule pasa a `routes/console.php` con `Schedule::command('alerts:dispatch')`. |
| **Exception Handler** | [app/Exceptions/Handler.php](app/Exceptions/Handler.php): mínimo — `$dontFlash` por defecto y `register()` con `reportable` vacío. **Sin lógica custom relevante.** | Funciona tal cual. Migración trivial si se adopta `->withExceptions()` en `bootstrap/app.php`. Bajo riesgo. |
| **Providers** | [config/app.php:160-179](config/app.php#L160): registrados vía `ServiceProvider::defaultProviders()->merge([...])`. Custom: `App\Providers\*` (7) + `Spatie\Permission`, `Barryvdh\DomPDF`, `Yajra\DataTables` | Funciona tal cual en L11/L12. Si se moderniza: crear `bootstrap/providers.php` y mover ahí solo los `App\Providers\*` (los de paquetes se auto-descubren). |

**Conclusión:** ninguna de estas migraciones es obligatoria para que arranque L11/L12. Tratarlas como fase posterior reduce el riesgo del salto.

---

## 4. Sintaxis / APIs deprecadas encontradas

Búsqueda dirigida en `app/`, `routes/`, `database/`. Resultado: **muy poca deuda de API**; lo encontrado **no rompe** en L11/L12 (son deprecaciones suaves o estilo antiguo soportado).

| Tipo | Severidad | Evidencia | Nota |
|---|---|---|---|
| `$middlewareAliases` **+** `$routeMiddleware` duplicados en el Kernel | Baja (confuso, no rompe) | [app/Http/Kernel.php:57 y 73](app/Http/Kernel.php#L73) | `$routeMiddleware` es el nombre viejo (pre-L9). Soportado por compat, pero conviene unificar. |
| `protected $casts = [...]` como **propiedad** (no método `casts()`) | Informativa (NO deprecado) | [app/Models/User.php:46](app/Models/User.php#L46), [app/Models/Tenant/Alerts/AlertUser.php:25](app/Models/Tenant/Alerts/AlertUser.php#L25) | La propiedad `$casts` **sigue siendo válida** en L11/L12. Migrar a `casts()` es opcional. Solo 2 modelos la usan. |
| **Propiedades dinámicas** en controlador (`$this->modules`, `$this->children`, `$this->grand_children`, `$this->plan` sin declarar) | Media (deprecación PHP 8.2+) | [app/Http/Controllers/LandLord/CompanyController.php:197-200](app/Http/Controllers/LandLord/CompanyController.php#L197) (y uso en `:331-350`) | Emite *"Creation of dynamic property"* desde PHP 8.2; **no es fatal** hasta una futura PHP mayor. Arreglar declarando las propiedades en la clase. |
| `$dates`, helpers retirados (`str_slug`, `array_get`, `snake_case`…), `Str::sortRecursive`, `utf8_encode`, `create_function`, `each()` | — | **0 coincidencias** | Limpio. |
| Parámetros **nullable implícitos** (`Tipo $x = null` sin `?`) — deprecación PHP 8.4 | — | **0 coincidencias** en `app/` | Limpio para PHP 8.4. |

> Nota: no se hizo un barrido exhaustivo de cada vista Blade; el foco fue código PHP (modelos/controladores/rutas/kernel), donde se concentran las rupturas de upgrade.

---

## 5. Config publicada a revisar tras subir versión

Archivos en `config/` que son **copias publicadas de paquetes** (pueden ganar claves nuevas u obsoletas al actualizar):

| Archivo | Al actualizar… | Prioridad de revisión |
|---|---|---|
| `config/sanctum.php` | Sanctum v3 → **v4** | **Alta** (cambian middlewares/claves). |
| `config/permission.php` | Permission v5 → **v6** | **Alta** (v6 reorganiza claves y caché). |
| `config/jetstream.php` | Jetstream v4 → **v5** | **Alta**. |
| `config/fortify.php` | Fortify reciente | Media. |
| `config/multitenancy.php` | Multitenancy v3 → **v4** (para L12) | **Alta** (ver §6). |
| `config/livewire.php` | Livewire 3.5.x | Media. |
| `config/datatables.php` | Yajra v10 → v11/v12 | Media. |
| `config/dompdf.php` | DomPDF v3 → v3.1 | Baja. |

**Método recomendado:** tras cada `composer update`, comparar (diff) tu config local contra el `config` del vendor nuevo (`php artisan vendor:publish ... --existing` en un entorno de prueba, **no** en producción) y reconciliar claves manualmente.

---

## 6. Riesgos específicos del Multi-Tenancy

`spatie/laravel-multitenancy 3.2.0` → **soporta L11** con la versión actual, pero **L12 requiere multitenancy v4** (salto mayor). Puntos críticos a vigilar:

1. **Provisioning con cambio de conexión en caliente.**
   - [app/Models/Tenant.php](app/Models/Tenant.php): `createDatabase()` hace `CREATE DATABASE` por la conexión `tenant`; `runMigrationsSeeders()` invoca `Artisan::call('tenants:artisan', ['migrate --path=database/migrations/tenant --database=tenant --seed --force', '--tenant'=>id])`.
   - [LandLord/CompanyController.php:232-244](app/Http/Controllers/LandLord/CompanyController.php#L232): `DB::purge('tenant')` → `DB::reconnect('tenant')` → `DB::setDefaultConnection('tenant')` **en mitad del request**.
   - **Riesgo:** el comando `tenants:artisan` y el contrato `SwitchTenantTask`/`MigrateTenantAction` pueden cambiar de firma entre v3 y v4. Hay que **revalidar el comando `tenants:artisan` y los `actions` configurados** ([config/multitenancy.php:84-89](config/multitenancy.php#L84)) tras subir el paquete.

2. **Estructura nueva de bootstrap y orden de boot.** Si en algún momento se adopta el esqueleto L11 (`bootstrap/app.php`), el momento en que se resuelve el tenant (DomainTenantFinder) y el registro de tasks debe seguir ocurriendo **antes** de que el request toque la BD. Mientras se mantenga el esqueleto L10 actual, este riesgo es menor.

3. **Jobs tenant-aware.** Hoy `queues_are_tenant_aware_by_default => false` ([config/multitenancy.php:56](config/multitenancy.php#L56)). Si v4 cambia defaults de awareness de colas, validar que los jobs (p.ej. el schedule `alerts:dispatch`) sigan ejecutándose en la BD correcta del tenant.

4. **Doble juego de migraciones.** El upgrade del framework puede regenerar migraciones base (users, jobs, cache, sessions) con nueva forma; existen **en landlord y en tenant** por separado. No aplicar migraciones nuevas del framework sobre las BD de tenant sin verificar que no choquen con las existentes.

5. **`config/database.php` con `default => tenant`** ([config/database.php:18](config/database.php#L18)): cualquier comando de upgrade que asuma la conexión `default` actuará sobre la conexión **tenant**, no la central. Ejecutar migraciones de upgrade **especificando `--database` explícitamente**.

> **Regla de oro multi-tenant:** hacer todo el upgrade en un entorno con **al menos 2 tenants de prueba** y verificar, tras cada salto, que (a) se crea un tenant nuevo correctamente, (b) `tenants:artisan migrate` corre en todas las BD, y (c) el aislamiento (login en tenant A no ve datos de B) sigue intacto.

---

## 7. Secuencia de saltos recomendada

**Estrategia: 10 → 11 (estabilizar) → 12.** No saltar directo a 12: los paquetes acoplados necesitan pasar por su versión "L11" primero y conviene validar el multi-tenancy en cada escalón.

### Paso 0 — Preparación (sin cambiar versiones)
- Fijar PHP del entorno en **8.3** (ya disponible localmente) y correr la app actual para detectar deprecaciones previas.
- Arreglar la deuda que NO depende del framework: propiedades dinámicas en `CompanyController` (§4) y resolver `simple-qrcode` (§2).
- Rama dedicada + backup de las BD landlord y de tenants de prueba.

### Paso 1 — Laravel 10 → 11
1. En `composer.json`, subir en bloque (no de a uno): `laravel/framework ^11.0` **junto con** los acoplados que topan en L10:
   - `laravel/jetstream ^5.0`, `laravel/sanctum ^4.0`, `spatie/laravel-permission ^6.0`, `yajra/laravel-datatables ^11.0`.
   - Subir también `fortify`, `livewire` (3.5.x), `dompdf`, `maatwebsite/excel` a su release que declare `^11`.
   - `spatie/laravel-multitenancy` puede quedarse en 3.2 (ya soporta L11).
2. `composer update` (en local/CI, **no** en prod).
3. **Mantener el esqueleto L10** (no migrar Kernel/bootstrap todavía).
4. Aplicar breaking changes de cada paquete:
   - **Permission v6:** revisar `config/permission.php` y caché de permisos.
   - **Sanctum v4:** revisar middleware/`config/sanctum.php`.
   - **Jetstream v5:** revisar vistas/acciones publicadas.
   - **Yajra v11:** revisar namespaces/served-side de DataTables.
5. Validar multi-tenancy (alta de tenant + `tenants:artisan migrate` + aislamiento).
6. **Estabilizar y desplegar L11** antes de seguir.

### Paso 2 — (Opcional) Modernizar esqueleto en L11
- Migrar a `bootstrap/app.php` (middleware + aliases custom `validar.plan`/`verificar.caja`), `bootstrap/providers.php`, schedule a `routes/console.php`, `$casts`→`casts()`. Hacerlo **ya en L11** para que el salto a 12 sea limpio. Tarea separada y reversible.

### Paso 3 — Laravel 11 → 12
1. Subir `laravel/framework ^12.0` + los que requieren release específica de L12:
   - `spatie/laravel-multitenancy ^4.0` (**salto mayor — revisar §6**), `yajra/... ^12.0`, `dompdf ^3.1`, y confirmar en Packagist las versiones L12 de `fortify`, `livewire`, `jetstream` (v5 ya cubre 11+12), `excel`.
2. `composer update`, aplicar breaking changes de multitenancy v4 (el de mayor riesgo).
3. Revalidar provisioning de tenants y aislamiento.
4. Validar en PHP **8.3** y luego probar en **8.4** (sin parámetros nullable implícitos pendientes; arreglar deprecaciones de propiedades dinámicas si se desea silencio total).

### Orden de subida de paquetes acoplados (resumen)
**framework** → (mismo `composer update`) **jetstream + fortify + sanctum + livewire** → **permission + yajra + dompdf + excel** → por último, en el salto a 12, **multitenancy v4**.

---

## Anexo — Evidencia inspeccionada
- Versiones/constraints: `composer.json`, `composer.lock` (extracción por paquete del bloque `require`).
- Estructura: [app/Http/Kernel.php](app/Http/Kernel.php), [app/Console/Kernel.php](app/Console/Kernel.php), [app/Exceptions/Handler.php](app/Exceptions/Handler.php), [config/app.php](config/app.php), `bootstrap/` (sin `providers.php`), `routes/console.php`, `app/Console/Commands/*`.
- Deprecaciones: grep de `$casts`, `$dates`, helpers retirados, propiedades dinámicas, parámetros nullable implícitos sobre `app/`, `routes/`, `database/`.
- Multi-tenancy: [app/Models/Tenant.php](app/Models/Tenant.php), [config/multitenancy.php](config/multitenancy.php), [app/Http/Controllers/LandLord/CompanyController.php](app/Http/Controllers/LandLord/CompanyController.php), [config/database.php](config/database.php).

*Checklist de solo lectura. No se modificó ningún archivo del proyecto salvo la creación de este documento. Las afirmaciones de soporte L11/L12 de cada paquete se basan en los constraints reales del `composer.lock`; donde el soporte L12 depende de releases posteriores a este análisis, se indica "verificar en Packagist".*
