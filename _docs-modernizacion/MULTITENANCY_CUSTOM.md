# Multi-tenancy — Inventario de personalización (cara a Spatie v4)

> **Contexto:** salto previsto `spatie/laravel-multitenancy` v3.2 → **v4** (requisito para Laravel 12).
> **Cambios de contrato en v4 a vigilar:** `TenantFinder::findForRequest(Request): ?IsTenant` y `SwitchTenantTask::makeCurrent(IsTenant $tenant)` (antes `Tenant $tenant`); el modelo base pasa a implementar `IsTenant`.
> **Fecha:** 2026-06-26 · **Tipo:** solo lectura · **Evidencia:** archivo:línea.

---

## VEREDICTO

🟢 **Multi-tenancy usa el paquete casi sin personalizar: hay 0 implementaciones propias de los dos contratos que cambian en v4 (TenantFinder y SwitchTenantTask).** El único punto custom es el modelo `App\Models\Tenant` (extiende el modelo base del paquete + 2 métodos de provisioning), que **no toca** las firmas modificadas. Las dos breaking-changes de firma que motivan el salto **no impactan código propio**.

---

## 1. Tenant Finder

- **Configurado:** `Spatie\Multitenancy\TenantFinder\DomainTenantFinder::class` ([config/multitenancy.php:22](config/multitenancy.php#L22)) → **finder del paquete tal cual**.
- **Clase propia:** **NO existe.** `grep "extends TenantFinder" / "findForRequest"` en `app/` → **0 coincidencias**.

➡️ El cambio de firma `findForRequest(Request): ?IsTenant` **no afecta** al proyecto (no hay finder propio que migrar).

---

## 2. Switch Tenant Tasks

- **Configurado:** una sola task, **del paquete**: `Spatie\Multitenancy\Tasks\SwitchTenantDatabaseTask::class` ([config/multitenancy.php:40](config/multitenancy.php#L40)).
- **Task propia:** **NO existe.** `grep "implements SwitchTenantTask"` en `app/` → **0 coincidencias**.
- **Actions configuradas** ([config/multitenancy.php:85-88](config/multitenancy.php#L85)): todas del paquete, sin sobreescribir con clases propias:
  - `MakeTenantCurrentAction`, `ForgetCurrentTenantAction`, `MakeQueueTenantAwareAction`, `MigrateTenantAction`.

➡️ El cambio de firma `makeCurrent(IsTenant $tenant)` **no afecta** al proyecto (no hay task propia que migrar).

> Nota: en `app/Jobs/CheckTenantAlertsJob.php:37,72` se llama a **`$tenant->makeCurrent()` / `$tenant->forgetCurrent()`** — son **métodos públicos del modelo** (API estable), **no** la implementación del contrato `SwitchTenantTask`. No se ven afectados por el cambio de firma.

---

## 3. Modelo Tenant

[app/Models/Tenant.php](app/Models/Tenant.php):
- **Declaración:** `class Tenant extends Spatie\Multitenancy\Models\Tenant` (alias `BaseTenant`) ([:10,:12](app/Models/Tenant.php#L12)). **Extiende** el modelo base (no implementa `IsTenant` directamente) → en v4 hereda automáticamente la conformidad con `IsTenant`.
- **Conexión:** `protected $connection = 'landlord'` ([:16](app/Models/Tenant.php#L16)) — **directo, sin el trait `UsesLandlordConnection`**.
- **Métodos custom (provisioning):**
  - `createDatabase($tenant)` ([:27-38](app/Models/Tenant.php#L27)): `CREATE DATABASE tenancy_<domain>` vía conexión `tenant`.
  - `runMigrationsSeeders($tenant)` ([:40-47](app/Models/Tenant.php#L40)): `Artisan::call('tenants:artisan', ['migrate --path=database/migrations/tenant --database=tenant --seed --force', '--tenant'=>id])`.
  - Hooks `static::creating` / `static::created` en `booted()` ([:20-24](app/Models/Tenant.php#L20)), con closures tipados `fn (Tenant $tenant) => ...`.
- **Traits del paquete:** `grep "UsesTenantConnection|UsesLandlordConnection"` en todo `app/` y `config/` → **0 coincidencias**. Los modelos de tenant resuelven la conexión por contexto (conexión por defecto = `tenant`), no con los traits del paquete.

➡️ Ninguno de los métodos custom toca los contratos que cambian en v4. Son lógica propia de creación de BD + migración.

---

## 4. Otras extensiones del paquete

Todos los enganches a `Spatie\Multitenancy\*` en `app/` (vía `grep`):

| Archivo:línea | Uso | ¿Contrato que cambia en v4? |
|---|---|---|
| [app/Models/Tenant.php:10](app/Models/Tenant.php#L10) | `extends` modelo base | No (herencia; conforma `IsTenant` automáticamente) |
| [app/Jobs/CheckTenantAlertsJob.php:15,37,72](app/Jobs/CheckTenantAlertsJob.php#L37) | `Tenant::findOrFail()`, `->makeCurrent()`, `->forgetCurrent()` | No (API pública del modelo, estable) |
| [app/Providers/AppServiceProvider.php:10,38](app/Providers/AppServiceProvider.php#L38) | `Tenant::current()?->id` (clave de caché) | No (estable) |
| [app/Http/Controllers/Tenant/CustomerController.php:18,372](app/Http/Controllers/Tenant/CustomerController.php#L372) | `Tenant::current()->database` | No (estable) |
| [app/Http/Controllers/Tenant/SupplierController.php:15](app/Http/Controllers/Tenant/SupplierController.php#L15) | import de `Tenant` | No (estable) |

- **TenancyServiceProvider propio:** **NO existe** (`app/Providers/` solo tiene los 7 estándar; ninguno de tenancy).
- **Listeners de `TenancyBootstrapped`/`TenancyEnded`:** **0 coincidencias** en `app/`.

> Observación menor (no es bloqueante de v4): `CheckTenantAlertsJob`, `AppServiceProvider`, `CustomerController` y `SupplierController` importan el **modelo base** `Spatie\Multitenancy\Models\Tenant` en vez de `App\Models\Tenant`. Funciona (los métodos usados están en el base), pero es una inconsistencia que conviene unificar como higiene.

---

## 5. Provisioning

Flujo de alta de tenant:
1. [LandLord/CompanyController.php:163](app/Http/Controllers/LandLord/CompanyController.php#L163): `Tenant::create([...])` (modelo propio).
2. Hooks del modelo ([Tenant.php:20-24](app/Models/Tenant.php#L20)): `creating` → `createDatabase`, `created` → `runMigrationsSeeders`.
3. `runMigrationsSeeders` invoca el **comando `tenants:artisan`** del paquete.
4. En el mismo request, [CompanyController.php:241-244](app/Http/Controllers/LandLord/CompanyController.php#L241): `DB::purge('tenant')` → `DB::reconnect('tenant')` → `DB::setDefaultConnection('tenant')` (API de Laravel, **no** del paquete).

➡️ La única dependencia del paquete en el provisioning es el **comando `tenants:artisan`** y, en config, la acción `migrate_tenant => MigrateTenantAction`. Hay que **verificar que el nombre/firma del comando `tenants:artisan` y la clase `MigrateTenantAction` no cambien en v4** (ver tabla). El resto (`DB::*`, `Artisan::call`, hooks Eloquent) es framework, ajeno al salto.

---

## Tabla — Puntos custom y esfuerzo para v4

| # | Punto custom | ¿Qué cambia en v4? | Esfuerzo |
|---|---|---|---|
| 1 | `App\Models\Tenant` extiende el modelo base | El base implementa `IsTenant`; al extenderlo, se hereda. Verificar que propiedades (`database`, `$connection`) y closures tipados `fn(Tenant $t)` sigan válidos. | **Trivial** |
| 2 | `Tenant.php::runMigrationsSeeders` usa `tenants:artisan` | Confirmar nombre/opciones del comando en v4 (suele mantenerse). | **Trivial-medio** (verificar) |
| 3 | `config/multitenancy.php` (finder, task, **actions**, queueable_to_job) | v4 puede renombrar/mover `*Action` y cambiar claves por defecto. Re-publicar config v4 y reconciliar referencias (`MakeTenantCurrentAction`, `MigrateTenantAction`, etc.). | **Medio** |
| 4 | Llamadas `makeCurrent()/forgetCurrent()/current()` (Job + 3 archivos) | API pública estable; sin cambios esperados. Solo revalidar. | **Trivial** |
| — | **TenantFinder propio** | — | **N/A (no existe)** |
| — | **SwitchTenantTask propia** | — | **N/A (no existe)** |

> Los dos breaking-changes de firma que motivan el salto (`findForRequest` y `makeCurrent` de los **contratos**) tienen **impacto cero** aquí, porque no hay implementaciones propias de esos contratos.

---

## Lista concreta de archivos a editar al saltar a Spatie v4

**Obligatorios (revisar/ajustar):**
1. [config/multitenancy.php](config/multitenancy.php) — re-publicar la config de v4 y reconciliar claves y referencias a `Actions` (punto de mayor probabilidad de cambio). **Medio.**
2. [app/Models/Tenant.php](app/Models/Tenant.php) — revalidar que `extends` del modelo base v4 compila y que `createDatabase`/`runMigrationsSeeders`/`booted()` siguen correctos (incl. el comando `tenants:artisan`). **Trivial-medio.**

**Verificación (probablemente sin edición):**
3. [app/Http/Controllers/LandLord/CompanyController.php](app/Http/Controllers/LandLord/CompanyController.php) — confirmar que el provisioning (`Tenant::create` + cambios de conexión) sigue funcionando con el modelo v4. **Trivial.**
4. [app/Jobs/CheckTenantAlertsJob.php](app/Jobs/CheckTenantAlertsJob.php) — revalidar `makeCurrent()/forgetCurrent()` en colas (revisar también `queues_are_tenant_aware_by_default`, hoy `false`). **Trivial.**

**Opcional (higiene, no requisito de v4):**
5. Unificar imports del modelo base `Spatie\Multitenancy\Models\Tenant` → `App\Models\Tenant` en `CheckTenantAlertsJob`, `AppServiceProvider`, `CustomerController`, `SupplierController`.

---

## Conclusión para el plan (Fase 1 / L11→L12)

El salto a Spatie v4 en este proyecto es **de bajo riesgo en cuanto a personalización**: **no hay clases propias que implementen los contratos modificados**. El trabajo real se concentra en **reconciliar `config/multitenancy.php`** con la config v4 y **revalidar el modelo `Tenant` y el provisioning** (`tenants:artisan`, cambios de conexión en caliente). Esfuerzo global estimado: **bajo-medio**, dominado por la verificación de config y el provisioning, no por reescritura de lógica.

*Informe de solo lectura. No se modificó ningún archivo del proyecto salvo la creación de este documento. Las afirmaciones sobre nombres/firmas exactos de clases en v4 (Actions, comando `tenants:artisan`) deben contrastarse con el upgrade guide oficial de Spatie v4 al ejecutar el salto.*
