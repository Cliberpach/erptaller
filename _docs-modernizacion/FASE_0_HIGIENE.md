# Fase 0 — Higiene y Preparación (pre-upgrade Laravel)

> **Rama:** `fase-0-higiene` · **Fecha:** 2026-06-26 · **Entorno:** local (no producción)
> **Naturaleza:** limpieza de código muerto + verificación pre-upgrade. Cambios quirúrgicos y reversibles.
> **Regla aplicada:** si algo no estaba 100% claro o la evidencia contradecía el supuesto, NO se tocó y se documentó aquí.

---

## Resumen de decisiones

| # | Punto | Decisión | Motivo |
|---|---|---|---|
| A.1 | `resources/views/layouts_old/` | ✅ **BORRADO** | 0 referencias en todo el proyecto. |
| A.2 | Migración `...payment_method_account_table copy.php` | 🔴 **NO se tocó** | NO es duplicado: es la única migración que crea una tabla en uso. |
| A.3 | Rutas de prueba `/test-broadcast` y `/test-socket` | ✅ **BORRADAS** | 0 referencias; código de prueba. |
| B.4 | Propiedades dinámicas en `CompanyController` | 🟡 **NO se tocó** | Ya estaban declaradas; no había deprecación. |
| C.5 | `simple-qrcode` en `InvoiceController` | 🔵 **Solo reportado** | Import muerto inofensivo; se barrerá en Fase 4 (SUNAT). |

**Cambios reales de código aplicados: 2** (A.1 y A.3), ambos de la misma naturaleza ("borrado de código muerto"), confirmados seguros con grep (0 referencias). Commit conjunto.

---

## BLOQUE A — Borrado de código muerto

### A.1 — `resources/views/layouts_old/` → BORRADO ✅

**Evidencia previa (grep):**
- `grep "layouts_old"` en `resources/` → **0 coincidencias**.
- `grep "layouts_old"` en `app/` → **0 coincidencias**.
- Ninguna vista hacía `@extends`/`@include`/`@component` apuntando a `layouts_old`.

**Archivos eliminados (9):**
```
resources/views/layouts_old/app.blade.php
resources/views/layouts_old/guest.blade.php
resources/views/layouts_old/header.blade.php
resources/views/layouts_old/js.blade.php
resources/views/layouts_old/template.blade.php
resources/views/layouts_old/body/aside.blade.php
resources/views/layouts_old/body/body.blade.php
resources/views/layouts_old/body/footer.blade.php
resources/views/layouts_old/body/navbar.blade.php
```
**Reversible:** sí (git, rama dedicada). El layout vivo es `resources/views/layouts/` (sin sufijo `_old`), que NO se tocó.

---

### A.2 — Migración `2025_07_01_102554_create_payment_method_account_table copy.php` → NO SE TOCÓ 🔴

> **El supuesto inicial ("es un duplicado de un original") era INCORRECTO.** La evidencia lo desmiente:

1. **No existe un archivo original sin " copy".** `find database/migrations -iname "*payment_method*"` devuelve solo:
   - `2023_09_30_020404_create_payment_methods_table.php` (otra tabla distinta: `payment_methods`).
   - `2025_07_01_102554_create_payment_method_account_table copy.php` (este).
2. **Es la ÚNICA migración** que crea la tabla `payment_method_accounts` (pivote método de pago ↔ cuenta bancaria). `grep "payment_method_account"` en `database/migrations/` → solo este archivo.
3. **La tabla se usa activamente:**
   - Modelo: `app/Models/Tenant/Sale/PaymentMethodAccount.php`
   - Controladores: `Sales/PaymentMethodController.php`, `Maintenance/BankAccountController.php`
   - Vistas: `resources/views/sales/payment_method/{assign-accounts,index}.blade.php`
   - Rutas: `assignAccountsCreate` / `assignAccountsStore` en `routes/tenant/sales/web.php`

**Conclusión:** borrarlo eliminaría la creación de una tabla real y rompería la funcionalidad de "asignar cuentas a métodos de pago".

**Defecto real (menor):** el nombre del archivo contiene un espacio + " copy". Laravel lo ejecuta igual (carga cualquier `.php` del directorio; la migración usa clase **anónima**, sin colisión de nombre de clase).

**Por qué NO se renombró:**
- Las migraciones se registran en la tabla `migrations` **por nombre de archivo**. Renombrar en entornos ya migrados (tenants existentes) provocaría que Laravel lo considere una migración nueva y la **re-ejecute** → fallaría al intentar crear una tabla ya existente.
- Un renombrado seguro exige coordinar con el registro de `migrations` de cada BD de tenant: fuera del alcance de "higiene" y no incluido en los archivos permitidos.

**Pendiente futuro:** evaluar el renombrado (quitar " copy") como tarea controlada junto con la revisión del estado de `migrations` por tenant, no en este commit.

---

### A.3 — Rutas de prueba en `routes/web.php` → BORRADAS ✅

**Evidencia previa (grep):**
- `grep "test-broadcast|test-socket"` en `resources/` y `public/` → **0 referencias** (no las llama ninguna vista ni JS).
- En `web.php`, las clases importadas para esas rutas (`AlertCreated`, `TestSocketEvent`, `Alert`, `Auth`) se usaban **solo dentro del bloque de prueba**.

**Bloque eliminado (antiguas líneas 185-201):** 4 `use` + 2 rutas:
```php
use App\Events\AlertCreated;
use App\Events\TestSocketEvent;
use App\Models\Tenant\Alerts\Alert;
use Illuminate\Support\Facades\Auth;

Route::get('/test-broadcast', function () {
    $user = Auth::user();
    event(new AlertCreated(Alert::findOrFail(2), $user));
    return 'Evento enviado';
});

Route::get('/test-socket', function () {
    event(new TestSocketEvent());
    return 'OK';
});
```
**Alcance del cambio:** solo se quitaron esos `use` locales (no usados en otra parte del archivo) y las 2 rutas. Las **clases** `AlertCreated`, `TestSocketEvent`, `Alert` siguen existiendo en la app (no se tocaron). El grupo `Route::group(["prefix" => "utils"])` posterior quedó intacto.
**Reversible:** sí.

---

## BLOQUE B — Propiedades dinámicas → NO SE TOCÓ 🟡

> **El supuesto inicial ("son propiedades dinámicas que emiten deprecación en PHP 8.2+") era INCORRECTO.**

**Evidencia:** en `app/Http/Controllers/LandLord/CompanyController.php:37-40` las propiedades **ya están declaradas** explícitamente:
```php
private $modules;
private $children;
private $grand_children;
private $plan;
```
→ Al estar declaradas, **NO se crean propiedades dinámicas** y **NO existe** la deprecación de PHP 8.2+. (El reporte previo en `CHECKLIST_UPGRADE_LARAVEL.md` que las marcaba como dinámicas fue un **falso positivo**, causado por un `grep` mal escapado que no capturó estas líneas. Queda anotado para corregir esa fila del checklist.)

**Además, tipar estricto sería peligroso:** en las líneas 451-453 la asignación es condicional entre `Collection` y `array` vacío:
```php
$this->modules = count($module_array) > 0 ? Module::whereIn('id', $module_array)->get() : [];
```
Declarar `Collection $modules` provocaría un `TypeError` cuando se asigna `[]`. La declaración `private` **sin tipo** es la correcta y se deja como está.

**Acción:** ninguna. El objetivo (que las propiedades estén declaradas) ya estaba cumplido antes de esta fase.

---

## BLOQUE C — `simple-qrcode` → SOLO INVESTIGADO 🔵 (se barre en Fase 4)

**Hallazgos:**
- `simplesoftwareio/simple-qrcode` **NO está** en `composer.json` ni en `composer.lock`.
- Su única aparición es un `use` **sin uso** en `app/Http/Controllers/Tenant/InvoiceController.php:24`. **0 llamadas `QrCode::`** en el cuerpo del archivo → **import muerto** (un `use` no invocado no causa error en PHP).
- El generador de QR **real y presente** es **`bacon/bacon-qr-code v3.0.0`** (sí está en `composer.lock`), usado por `app/Http/Controllers/Tenant/QRController.php:7-8` (`BaconQrCode\Writer`, `GDLibRenderer`) en `generateQr()`.

**Opciones evaluadas:**
1. Quitar el `use` muerto de `InvoiceController:24` — riesgo cero, pero **NO se hace ahora** (decisión de no abrir un archivo de facturación en un commit de "higiene").
2. Dejarlo como está — inofensivo mientras no se invoque.
3. Declarar `simple-qrcode` en composer — **innecesario**: BaconQrCode ya cubre la generación de QR.

**Decisión:** **diferido a Fase 4 (SUNAT / facturación)**, cuando se trabaje dentro de `InvoiceController` con el contexto adecuado. Anotación para esa fase: eliminar la línea `use SimpleSoftwareIO\QrCode\Facades\QrCode;` (import muerto) y confirmar que todo el QR va por BaconQrCode.

---

## Anexo — Verificaciones (grep) que respaldan cada decisión
- A.1: `grep layouts_old resources/ app/` → 0.
- A.2: `find ... -iname "*payment_method*"` (sin original), `grep payment_method_accounts app/ resources/ routes/` (uso confirmado).
- A.3: `grep "test-broadcast|test-socket" resources/ public/` → 0; `grep Auth::/Alert:: routes/web.php` → solo dentro del bloque.
- B.4: lectura directa de `CompanyController.php:37-40` (declaradas) y `:451-453` (mezcla Collection/array).
- C.5: `grep simple-qrcode composer.json/lock` → 0; `grep "QrCode::" InvoiceController` → 0; `bacon/bacon-qr-code` presente en `composer.lock`.

---

## Resultado de la Fase 0
- **2 borrados** de código muerto aplicados (A.1, A.3).
- **2 supuestos corregidos** con evidencia (A.2 y B.4 NO se tocaron — y aquí queda el porqué, para no volver a tropezar).
- **1 hallazgo** diferido a Fase 4 (C.5, import muerto de simple-qrcode).
