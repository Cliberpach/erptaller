# Auditoría Técnica — TallerSuite / erptaller

> **Tipo:** Auditoría total de solo lectura
> **Fecha de auditoría:** 2026-06-26
> **Alcance:** Repositorio Laravel multi-tenant (ERP para talleres: automotriz/moto/bici, productos + servicios)
> **Método:** Inspección estática del código (rutas, controladores, modelos, migraciones, config, `composer.lock`, `package.json`). No se ejecutó ningún comando que altere estado.
> **Naturaleza de los datos:** Todos los datos y rutas citados se extraen del código fuente. Donde algo no es determinable estáticamente, se indica explícitamente.

---

## 0. Resumen Ejecutivo

**Qué es.** ERP SaaS multi-tenant para talleres, construido sobre **Laravel 10** con un modelo de **una base de datos por tenant** (paquete `spatie/laravel-multitenancy`) y resolución de tenant **por dominio/subdominio**. Vende tanto **productos** (inventario con kardex) como **servicios de taller**, e incluye **facturación electrónica SUNAT** (Perú) vía **Greenter**. La arquitectura NO es una SPA Livewire: es **clásica controlador + Blade + DataTables/jQuery/AJAX**, pese a tener Livewire 3 instalado.

**Origen / linaje.** El proyecto es un *fork* evolucionado de un ERP anterior: el `README.md` lo titula **"ErpDEportivoApp"**, la base de datos por defecto es `db_erpdeportivo` ([.env.example:14](.env.example#L14)) y `composer.json` conserva metadatos genéricos `laravel/laravel`. El **módulo Taller (WorkShop)** es lo más reciente: sus migraciones están fechadas el **2025-07-01** ([database/migrations/tenant/](database/migrations/tenant/)). Esto confirma que el ERP base (cajas, ventas, inventario, reservas/"campos") fue adaptado a vertical de taller añadiendo órdenes de trabajo, cotizaciones, vehículos, servicios y citas.

**Versión y obsolescencia.**
- **Laravel `v10.48.14`** — Laravel 10 está fuera de soporte (EOL); las versiones vivas a la fecha son 11 y 12.
- **PHP requerido `^8.1`** (`composer.lock` → `platform.php: ^8.1`; el `README` dice 8.2). El entorno local detectado ejecuta **PHP 8.3.30**. PHP 8.1 está en fin de vida.
- **Livewire `v3.5.1`** instalado pero **prácticamente sin usar** (solo 5 clases `extends Component`, todas en `app/View/Components`; 0 componentes Livewire de página).
- Stack JS heredado: jQuery + DataTables (Yajra) + SweetAlert2 + toastr + tom-select + Flowbite, sin build de componentes reactivos.

**5 frentes prioritarios para iniciar la modernización:**
1. **Actualizar runtime y framework:** PHP 8.1→8.3/8.4 y Laravel 10→11→12 (saltos encadenados). Es la base de todo lo demás.
2. **Endurecer facturación SUNAT:** hoy `Greenter/config.php` apunta a **BETA** con certificado y credenciales SOL de prueba *hardcodeadas* (`MODDATOS`). No es apto para producción tal cual.
3. **Riesgo de seguridad en multi-tenancy:** consultas cross-database construidas por **interpolación de strings** del nombre de BD del tenant ([CompanyController](app/Http/Controllers/LandLord/CompanyController.php)) — vector de inyección y fragilidad.
4. **Deuda de frontend:** decidir entre consolidar Livewire 3 o mantener el patrón Blade+jQuery; hoy conviven dos layouts (`layouts/` y `layouts_old/`) y librerías duplicadas.
5. **Limpieza de deuda técnica:** archivos basura (migración `... copy.php`), rutas de prueba (`/test-broadcast`, `/test-socket`), `dd()`/`console.log` residuales y vistas/carpetas muertas.

> **Veredicto general:** funcionalmente **amplio y mayormente completo** (módulo de taller incluido), pero **tecnológicamente desactualizado** (framework y PHP EOL) y con **riesgos de seguridad concretos** en facturación y en el aprovisionamiento de tenants. La modernización es viable de forma incremental.

---

## 1. Stack y Versiones

### 1.1 Lenguaje y framework
| Componente | Requerido (declarado) | Resuelto (`composer.lock`) | Notas |
|---|---|---|---|
| PHP | `^8.1` ([composer.json:8](composer.json#L8)); README dice 8.2 | plataforma `^8.1` | Entorno local detectado: **PHP 8.3.30** (`php -v`). PHP 8.1 EOL. |
| Laravel Framework | `^10.10` | **`v10.48.14`** | Laravel 10 EOL. Objetivo: 11 → 12. |
| Composer | — (README: 2.5+) | local **2.9.7** | OK. |

### 1.2 Paquetes Composer (versión real desde `composer.lock`)
**Relevantes / de negocio:**
| Paquete | Versión | Propósito |
|---|---|---|
| `spatie/laravel-multitenancy` | **3.2.0** | Multi-tenancy (BD por tenant, finder por dominio). |
| `spatie/laravel-permission` | **5.11.1** | Roles y permisos (Spatie). |
| `greenter/lite` | **v5.1.0** | Facturación electrónica SUNAT (Perú). |
| `laravel/jetstream` | **v4.3.1** | Scaffolding de auth/UI. |
| `laravel/fortify` | **v1.21.3** | Backend de autenticación (login/2FA). |
| `laravel/sanctum` | **v3.3.3** | Tokens/sesión API. |
| `barryvdh/laravel-dompdf` | **v3.0.0** | Generación de PDF (comprobantes, reportes, OT). |
| `maatwebsite/excel` | **3.1.61** | Importación/exportación Excel. |
| `yajra/laravel-datatables` | **v10.0.0** | DataTables server-side (núcleo de los listados). |
| `luecano/numero-a-letras` | `^4.0` | Importe en letras para comprobantes. |
| `pusher/pusher-php-server` | `^7.2` | Broadcasting (notificaciones en tiempo real). |
| `tightenco/ziggy` | `^2.6` | Rutas Laravel en JS. |
| `livewire/livewire` | **v3.5.1** | Instalado; uso marginal (ver §7). |
| `laravel-lang/common` + `symfony/translation` | `^6.1` / `^6.4` | Traducciones. |

**Dev:** `laravel/pint`, `laravel/sail`, `phpunit/phpunit ^10.1`, `mockery`, `nunomaduro/collision ^7.0`, `fakerphp/faker`, `spatie/laravel-ignition ^2.0`.

> Nota: `composer.json` declara `allow-plugins` para `pestphp/pest-plugin`, pero **Pest no está en `require-dev`**; el testing real es PHPUnit ([phpunit.xml](phpunit.xml)).

### 1.3 Frontend (`package.json`)
- **Bundler:** **Vite 4** (`laravel-vite-plugin ^0.8.0`) — no Mix. ([vite.config.js](vite.config.js))
- **CSS:** **Tailwind 3.1** + `@tailwindcss/forms` + `@tailwindcss/typography`; **Flowbite 2.5** y `@material-tailwind/html`. (No Bootstrap como framework principal, aunque hay clases tipo `fw-bold` sueltas en vistas.)
- **JS / librerías:** `axios`, `sweetalert2`, `toastr`, `tom-select`, `highcharts 12`, `filepond` (+plugins), `@toast-ui/calendar` (citas), `lightgallery`, `hover.css`.
- **Tiempo real:** `laravel-echo` + `pusher-js` + `@soketi/soketi` (servidor WebSocket self-hosted; ver `soketi.json` / `soketi.prod.json`).
- **Entrypoints Vite:** `resources/css/app.css`, `resources/js/app.js`, libs (`filepond`, `calendar`, `lightgalery`), `resources/js/notifications/main.js`.

### 1.4 Base de datos y conexión
- Motor: **MySQL** (`DB_CONNECTION=mysql`, [.env.example:11-16](.env.example#L11)).
- **Conexión por defecto = `tenant`** ([config/database.php:18](config/database.php#L18)) — relevante: el contexto natural de la app es el del tenant, no el central.
- Conexiones definidas: `tenant` y `landlord` (ambas MySQL), más `mysql` por defecto del framework. ([config/database.php](config/database.php))

### 1.5 Qué está desactualizado y saltos de modernización
| Elemento | Estado actual | Objetivo recomendado | Salto |
|---|---|---|---|
| PHP | `^8.1` (local 8.3.30) | 8.3 / 8.4 | Fijar `^8.3` y validar dependencias. |
| Laravel | 10.48 (EOL) | 11 → 12 | 2 saltos mayores; revisar `app/Http/Kernel.php` vs nuevo `bootstrap/app.php`. |
| Jetstream | 4.3 | acorde a Laravel 11/12 | Sube con el framework. |
| Sanctum | 3.3 | 4.x | Cambios menores de config. |
| Livewire | 3.5 (infrautilizado) | decisión arquitectónica | Consolidar o retirar. |
| Vite / plugin | Vite 4 / plugin 0.8 | Vite 5/6 / plugin 1.x | Salto de build. |
| `yajra/datatables` 10 | OK con L10 | versión compatible L11/12 | Acompaña al framework. |

---

## 2. Arquitectura Multi-Tenant

**Paquete:** `spatie/laravel-multitenancy 3.2.0`.

**Estrategia:** **una base de datos por tenant** + **una BD central (landlord)**.
- **Tenant finder:** `DomainTenantFinder` ([config/multitenancy.php:22](config/multitenancy.php#L22)) → el tenant se resuelve por el **host/dominio** de la petición.
- **Switch de BD:** `SwitchTenantDatabaseTask` activo ([config/multitenancy.php:40](config/multitenancy.php#L40)); conexiones nombradas `tenant` y `landlord` ([:62-67](config/multitenancy.php#L62)).
- **Jobs tenant-aware por defecto:** **desactivado** (`queues_are_tenant_aware_by_default => false`, [:56](config/multitenancy.php#L56)). A revisar si se encolan trabajos que tocan datos de tenant.

**Enrutado central vs tenant** ([RouteServiceProvider](app/Providers/RouteServiceProvider.php#L31-L53)):
- El **dominio raíz** (`config('app.url')` host) carga `routes/landlord.php` (panel de super-admin: empresas, planes).
- El resto carga `routes/web.php` con middleware `tenant.web` (cuando `multitenancy.enabled`), que a su vez incluye los sub-routers de cada módulo (`require routes/tenant/*/web.php`).

**Creación de tenants** ([app/Models/Tenant.php](app/Models/Tenant.php)):
1. Al **crear** un `Tenant` se ejecuta `createDatabase()`: calcula el nombre `tenancy_<domain>` (puntos→guion bajo, minúsculas) y lanza `CREATE DATABASE` por la conexión `tenant`.
2. Al **crearse** dispara `runMigrationsSeeders()`: `Artisan::call('tenants:artisan', ['migrate --path=database/migrations/tenant --database=tenant --seed --force', '--tenant' => id])`.

**Flujo de alta desde el panel landlord** ([LandLord/CompanyController@store](app/Http/Controllers/LandLord/CompanyController.php#L160-L231)):
- `Tenant::create()` con `domain = <subdominio>.<host>`; luego se crea la `Company`, se guarda el **certificado SUNAT** en `storage/<domain>_<id>/certificate/`, se asignan módulos/planes y se siembran datos del tenant cambiando de conexión en caliente (`DB::purge('tenant') / reconnect / setDefaultConnection('tenant')`, [:232-244](app/Http/Controllers/LandLord/CompanyController.php#L232)).

> ⚠️ **Riesgo:** varias consultas del panel landlord construyen el nombre de la BD del tenant por **interpolación directa** en SQL (p. ej. `DB::table("$tenant_data->database.modules")`, [CompanyController:105-117 y 455-461](app/Http/Controllers/LandLord/CompanyController.php#L455)). Aunque el nombre proviene de la propia BD, es un patrón frágil y potencialmente inyectable; debería encapsularse.

---

## 3. Módulos — Estado (Hecho / Parcial / Stub)

Evidencia cruzada: ruta declarada + controlador + carpeta de vistas. Conteo base: **54 controladores Tenant**, **6 Landlord**, **439 vistas Blade**.

### 3.1 Lo propio de TALLER (no existía en el ERP base "deportivo")
| Submódulo | Estado | Evidencia |
|---|---|---|
| **Órdenes de Trabajo (OT)** | **COMPLETO** | CRUD + PDF + `finish` + facturación (`invoiceCreate/invoiceStore`) + alertas. [routes/tenant/taller/web.php:16-32](routes/tenant/taller/web.php#L16); [WorkOrderController](app/Http/Controllers/Tenant/WorkShop/WorkOrderController.php); vistas `workshop/work_orders/{forms,tabs,reports,tables,modals,lists}`. Tablas `work_orders`, `_technicians`, `_images`, `_products`, `_services`, `_inventory`. |
| **Cotizaciones de servicio** | **COMPLETO** | CRUD + PDF + **conversión a OT** (`convert-order`). [taller/web.php:34-46](routes/tenant/taller/web.php#L34); vistas `workshop/quotes/*`. Tablas `quotes`, `quotes_products`, `quotes_services`. |
| **Servicios** | **COMPLETO** | CRUD + búsqueda (`searchService`). `services` (migración 2023-10-11). |
| **Vehículos** | **COMPLETO** | CRUD + búsqueda por placa (`searchPlate`, `searchVehicle`). Tabla `vehicles`. |
| **Citas / Agenda** | **PARCIAL** | Index + `getEvents` + store/update/destroy con calendario (`@toast-ui/calendar`), pero **sin `edit`/`getEvent`** dedicados y con rutas comentadas ([taller/web.php:109-117](routes/tenant/taller/web.php#L109)). Tabla `appointments`. |
| **Maestros de taller** (Marcas, Modelos, Años, Colores) | **COMPLETO** | CRUD completos; modelos/años con relación (`getYearsModel`). |
| **Técnicos/mecánicos** | **PARCIAL (vía colaboradores)** | No hay módulo "mecánicos" propio; se modelan como `work_orders_technicians` ligados a colaboradores (Mantenimiento → Colaboradores). |

### 3.2 Módulos heredados del ERP base
| Módulo | Estado | Evidencia |
|---|---|---|
| **Ventas / Comprobantes** | **COMPLETO** | Emisión, `send_sunat`, PDF (`pdf_voucher` con tamaño), descarga XML/CDR, clientes, métodos y condiciones de pago. [routes/tenant/sales/web.php]. Protegido por `validar.plan:ventas`. |
| **Inventario** | **COMPLETO** | Categorías, marcas, productos (import/export Excel), inventario, **Kardex** y **Kardex valorizado**, notas de ingreso/salida. [routes/tenant/inventory/web.php]. Función SQL `calcular_stock` y SP `sp_kardex`. |
| **Cajas** | **COMPLETO** | Caja, apertura/cierre (`PettyCashBook`), consolidado, egresos, comprobantes de pago. Middleware `verificar.caja`. [routes/tenant/cash/web.php]. |
| **Cuentas (CxC / CxP)** | **COMPLETO** | Cuentas por cobrar (cliente) y por pagar (proveedor) con pagos, PDF y Excel. Tablas `customer_accounts(_details)`, `supplier_accounts(_details)`. |
| **Compras** | **COMPLETO** | Proveedores (consulta RUC), documentos de compra. Protegido por `validar.plan:compras`. Nota: coexisten `PurchaseController` (vistas placeholder: orden de compra/gasto diverso) y `Purchase/PurchaseDocumentoController` (flujo real). |
| **Reportes** | **COMPLETO** | Ventas, campos, contable, comprobantes de reservas (con envío SUNAT, XML/CDR). [reportes en routes/web.php:129-159](routes/web.php#L129). |
| **Mantenimiento** | **COMPLETO** | Empresa (+numeraciones, certificado), cargos, colaboradores, usuarios, cuentas bancarias, configuración, roles, horario de atención. [routes/tenant/mantenimiento/web.php]. |
| **Consultas** | **PARCIAL** | Vehículos (lista/Excel/PDF) y alertas/notificaciones funcionando; "consultas créditos" y "consultas reservas" presentes pero con varias rutas/exportaciones comentadas ([routes/web.php:71-86](routes/web.php#L71)). |
| **Dashboard** | **PARCIAL** | Index + `getData` + stock mínimo + Excel; es un panel acotado (Highcharts) más que un cuadro de mando completo. [routes/tenant/dashboard/web.php]. |
| **Reservas / "Campos"** | **PARCIAL/HEREDADO** | Lógica de reservas y "campos" (`fields`/`bookings`/`schedules`) heredada del ERP base; emite comprobantes. Probablemente **no aplica** al vertical taller — candidato a retirar o reutilizar para *citas*. [routes/web.php:64-101](routes/web.php#L64). |
| **Notificaciones (tiempo real)** | **COMPLETO** | CRUD de notificaciones + broadcasting (Echo/Soketi), alertas de OT. |

### 3.3 Landlord (super-admin)
| Submódulo | Estado | Evidencia |
|---|---|---|
| Empresas/Tenants | **COMPLETO** | Alta/edición/baja de tenant, reset de clave, gestión de numeración. [routes/landlord.php:21-32](routes/landlord.php#L21). |
| Planes | **COMPLETO** | CRUD de planes. |
| Módulos/Roles/Usuarios landlord | **PARCIAL** | Existen controladores (`ModuleController`, `RoleController`, `UserController`) pero **sin rutas activas** en `landlord.php` (solo empresa y plan). |

---

## 4. Base de Datos

**108 migraciones** en total: **25 landlord** + **83 tenant**, separadas en `database/migrations/{landlord,tenant}/` (+ una suelta `2024_01_21_085606_schedule.php`).

### 4.1 BD Central (landlord) — tablas clave
| Tabla | Propósito |
|---|---|
| `tenants` | Registro de inquilinos (nombre, dominio, `database`). |
| `companies` / `company_invoice` | Empresa por tenant y datos de facturación. |
| `plans` | Planes de suscripción (gating de módulos). |
| `modules` / `module_children` / `module_grand_children` (+ `_companies`) | Árbol de módulos y su asignación por empresa. |
| `types_identity_documents`, `customers` | Catálogos centrales compartidos. |
| `colors`, `brandsv`, `models`, `years` | Catálogo **vehicular central** (marcas/modelos/años/colores). |
| `general_tables(_categories/_details)` | Tablas paramétricas genéricas. |
| `users`, `permission_tables`, `sessions`, `jobs`, `failed_jobs`, `personal_access_tokens` | Auth/infra del panel central. |

### 4.2 BD por Tenant — tablas clave
| Dominio | Tablas | Propósito |
|---|---|---|
| **Taller** | `vehicles`, `services`, `quotes(+_products,+_services)`, `work_orders(+_technicians,+_images,+_products,+_services,+_inventory)`, `appointments` | Núcleo del vertical taller (OT, cotizaciones, citas). Fechadas **2025-07-01**. |
| **Ventas/Facturación** | `sales_documents(+_details,+_services,+_bookings)`, `billing_companies`, `document_types`, `document_serializations`, `credit_notes(+_details)`, `payment_methods`, `payment_conditions` | Comprobantes (productos y servicios), notas de crédito, series. |
| **Inventario** | `products`, `categories`, `brands`, `warehouses`, `warehouse_products`, `kardex` (+ función `calcular_stock`, SP `sp_kardex`), `notes_income(+_detail)`, `notes_release(+_detail)` | Stock multi-almacén y kardex. |
| **Cuentas** | `customer_accounts(+_details)`, `supplier_accounts(+_details)`, `payment_method_account`, `credits` | CxC / CxP y créditos. |
| **Compras** | `purchase_documents(+_detail)`, `suppliers`, `proof_payments` | Compras y comprobantes de pago. |
| **Caja** | `petty_cashes`, `petty_cash_books`, `shifts`, `exit_money(+_detail)`, `bank_accounts`, `banks`, `bank_companies` | Cajas, aperturas/cierres, egresos. |
| **Reservas/Campos (heredado)** | `fields`, `type_fields`, `schedules`, `reservation_documents(+_detail)`, `bookings`, `booking_detail`, `bookings_schedules` | Módulo de reservas del ERP base. |
| **RRHH/Org** | `positions`, `collaborators`, `users`, `companies`, `configuration` | Personal y configuración del tenant. |
| **Alertas** | `alerts`, `alert_user` | Notificaciones/alertas (OT). |
| **Geo** | `departments`, `provinces`, `districts` | Ubigeo (Perú). |

> 🔎 **Hallazgos en migraciones:**
> - Archivo basura: **`2025_07_01_102554_create_payment_method_account_table copy.php`** (sufijo " copy" → duplicado accidental, romperá `migrate` por nombre/clase).
> - Lógica de BD en migraciones: `create_calcular_stock_function.php` y `create_sp_kardex_procedure.php` definen **función y procedimiento almacenado** MySQL → dependencia de motor (dificulta portabilidad/tests con SQLite).
> - Numeración con fechas futuras coherente con el calendario del proyecto (taller `2025-07`, `jobs` central `2026-01`).

---

## 5. Facturación Electrónica / SUNAT (Greenter)

**Librería:** `greenter/lite v5.1.0` + carpeta propia `Greenter/`.

**Configuración actual** ([Greenter/config.php](Greenter/config.php)):
```php
$see->setCertificate(file_get_contents(__DIR__.'\certificate\certificate.pem'));
$see->setService(SunatEndpoints::FE_BETA);              // ⚠ AMBIENTE BETA
$see->setClaveSOL('20000000001', 'MODDATOS', 'moddatos'); // ⚠ credenciales de PRUEBA hardcodeadas
```
- **Ambiente:** **BETA** (homologación), no producción.
- **Certificado:** hay 3 PEM en `Greenter/certificate/` (`certificate.pem`, `certificate_maqui.pem`, `certificate_test.pem`) — certificados de prueba versionados en el repo. En producción, cada tenant guarda su certificado en `storage/<tenant>/certificate/` (ver §2).
- **Comprobantes soportados** (evidencia en código):
  - **Factura / Boleta** (`Greenter\Model\Sale\Invoice`) y **Notas** (`Greenter\Model\Sale\Note` → notas de crédito; tablas `credit_notes`). [InvoiceController](app/Http/Controllers/Tenant/InvoiceController.php).
  - Ejemplos de **Guía de remisión** en `Greenter/example/guia.php` (ejemplo, no necesariamente integrado en UI).
  - Envío vía `send_sunat`, descarga de **XML** y **CDR**, **QR** (`simplesoftwareio/simple-qrcode`), importe en letras (`luecano/numero-a-letras`). Rutas en ventas y en reportes de reservas.
- **Ruta de origen de la emisión:** ventas (`SaleController@send_sunat`), OT (`WorkOrderController@invoiceStore`) y comprobantes de reservas.

> ⚠️ **Para producción:** mover endpoint a `FE_PRODUCCION`, parametrizar `ClaveSOL`/RUC por tenant (hoy fijo a `20000000001/MODDATOS`), no versionar certificados reales y resolver el certificado por tenant en `config.php` (actualmente usa una ruta fija de Windows con `\`, poco portable).

---

## 6. Seguridad y Roles

- **Autenticación:** Jetstream + **Fortify** (login, registro, 2FA) + **Sanctum**; middleware `auth:sanctum` + `config('jetstream.auth_session')` + `verified` en `routes/web.php` (tenant) y `auth:web` en `landlord.php`. Tablas `two_factor_*` presentes → **2FA disponible**.
- **Autorización:** **`spatie/laravel-permission 5.11`**.
  - Roles/permisos **por tenant**: modelos `App\Models\Tenant\TenantRole` y `TenantPermission` extienden los de Spatie pero forzando `connection = 'tenant'` y tablas `roles`/`permissions` ([TenantRole.php](app/Models/Tenant/TenantRole.php)). Hay además `RoleController` tenant y landlord.
  - **Teams = false** ([config/permission.php:119](config/permission.php#L119)) → la separación entre tenants **no** se apoya en la feature de equipos de Spatie, sino en el **aislamiento físico de BD** (cada tenant tiene su propia tabla `roles`/`permissions`).
- **Granularidad:** combinación de dos capas:
  1. **Por plan/módulo:** middleware `PlanMiddleware` (`validar.plan:ventas|compras|inventario`) restringe módulos según el plan del tenant.
  2. **Por permiso (pantalla/acción):** roles/permisos Spatie a nivel de tenant + el árbol `modules/module_children/module_grand_children` que modela el menú y los accesos. La asignación de módulos se hace al crear la empresa (panel landlord).
- **Middleware de negocio propio:** `VerificarCajaAbierta` (`verificar.caja`) bloquea operaciones si no hay caja abierta.

> ⚠️ Observaciones: rutas de **prueba sin protección** en `routes/web.php` (`/test-broadcast`, `/test-socket`, [:190-201](routes/web.php#L190)) y `user/tenant` + `user/create` **fuera del grupo autenticado** ([routes/web.php:50-51](routes/web.php#L50)). `APP_DEBUG=true` en `.env.example`. Revisar exposición.

---

## 7. Deuda Técnica y Riesgos

**Frontend / arquitectura de vistas**
- **Livewire 3 infrautilizado:** solo 5 clases `extends Component`, todas en `app/View/Components` (layouts, modal, card, toggle); **0 componentes Livewire de página**. `vite.config.js` referencia `app/Livewire/**` que **no existe**. La app es 100% controlador→Blade→AJAX/DataTables. → Decidir: adoptar Livewire de verdad o retirarlo.
- **Doble layout:** `resources/views/layouts/` **y** `resources/views/layouts_old/` (app, guest, header, template, body, js). El "old" es código muerto candidato a eliminar.
- **jQuery + DataTables + Alpine** conviviendo: ~7 vistas con jQuery directo, 11 con `x-data` (Alpine), abundante JS embebido en Blade. Mantenimiento costoso y estilos mezclados (Tailwind + clases tipo Bootstrap `fw-bold`).
- `console.log` residuales (~14 en `resources/js`).

**Backend**
- **SQL por interpolación de strings** para cross-database en el panel landlord (`"$db.tabla"`) — riesgo de inyección y acoplamiento fuerte al nombre de BD ([CompanyController](app/Http/Controllers/LandLord/CompanyController.php#L455)).
- `dd()`/`dump` residuales (~4 en `app/`).
- **Bloque de salida de Greenter pegado como comentario** dentro de `InvoiceController` (volcado de un objeto `BillResult` real) — ruido y posible filtración de datos en el repo.
- Métodos `public static` en controladores (`InvoiceController::send_sunat`) — patrón poco idiomático para Laravel.
- Duplicidad de responsabilidades en **Compras** (`PurchaseController` placeholder vs `Purchase/PurchaseDocumentoController` real).
- **Rutas comentadas** en varios routers (consultas, citas, cajas en `web.php`) — funcionalidad a medias o abandonada.

**Datos / migraciones**
- Migración **`... copy.php`** (rompe `migrate`).
- Funciones y SP de MySQL en migraciones → acoplamiento al motor.

**Configuración / despliegue**
- Greenter en **BETA** con credenciales y certificados de prueba en el repo (§5).
- `README.md` desactualizado (sigue siendo el genérico de Laravel + "ErpDEportivoApp"; no documenta TallerSuite ni el deploy).
- Metadatos `composer.json` genéricos (`laravel/laravel`, "skeleton application").

**Riesgos para modernizar**
1. Saltos Laravel 10→11→12 con Jetstream/Fortify/Livewire acoplados.
2. SP/funciones MySQL dificultan tests aislados.
3. Mucho JS inline en Blade ⇒ refactor de UI laborioso.
4. Aislamiento de tenant depende de operaciones manuales de conexión (`DB::setDefaultConnection` en caliente) — frágil ante cambios del framework.

---

## 8. Despliegue

- **No hay Docker:** no existen `Dockerfile` ni `docker-compose.yml` en el repo (sí está `laravel/sail` como dev-dependency, pero sin artefactos de Sail). El `README` apunta a **XAMPP** → despliegue clásico tipo LAMP/WAMP.
- **Build de assets:** Vite (`npm run build`).
- **WebSockets:** **Soketi** self-hosted, con configs versionadas `soketi.json` (dev) y `soketi.prod.json` (prod) → se ejecuta como proceso aparte; broadcasting vía Pusher protocol + Laravel Echo.
- **Colas:** `.env.example` trae `QUEUE_CONNECTION=sync`; existe tabla `jobs` (central) → preparado para colas pero por defecto síncrono.
- **CI:** había un workflow en `.github/workflows/` (`ci-109162.yml`) que aparece **eliminado** en el estado de git actual; no hay pipeline activo confirmable.
- **Provisión de tenant:** automatizada en código (crea BD + corre migraciones+seeders vía `tenants:artisan`), no por script de infraestructura.
- **Método de deploy probable:** `git pull` + `composer install` + `npm run build` + `php artisan migrate` (central) sobre un servidor con MySQL y un proceso Soketi. **No determinable con certeza** (sin scripts de deploy ni documentación en el repo).

---

## Anexo — Inventario de evidencia inspeccionada
- Configuración: [composer.json](composer.json), `composer.lock`, [package.json](package.json), [.env.example](.env.example), [vite.config.js](vite.config.js), `tailwind.config.js`, [config/multitenancy.php](config/multitenancy.php), [config/permission.php](config/permission.php), [config/database.php](config/database.php).
- Rutas: [routes/web.php](routes/web.php), [routes/landlord.php](routes/landlord.php), `routes/tenant/{taller,sales,inventory,cash,accounts,queries,mantenimiento,dashboard}/web.php`.
- Código: [app/Models/Tenant.php](app/Models/Tenant.php), [app/Providers/RouteServiceProvider.php](app/Providers/RouteServiceProvider.php), [app/Http/Controllers/LandLord/CompanyController.php](app/Http/Controllers/LandLord/CompanyController.php), [app/Http/Controllers/Tenant/InvoiceController.php](app/Http/Controllers/Tenant/InvoiceController.php), `app/Http/Controllers/Tenant/**` (54), `app/Models/Tenant/**`.
- Facturación: [Greenter/config.php](Greenter/config.php), `Greenter/{certificate,example,Utils,views}`.
- BD: `database/migrations/{landlord,tenant}/` (108 archivos).
- Vistas: `resources/views/**` (439 Blade), incluido `workshop/**` y `layouts_old/**`.

*Fin del informe. Auditoría de solo lectura; no se modificó ningún archivo del proyecto salvo la creación de este documento.*
