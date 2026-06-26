# Multi-Sede — Series, CAPA C: emisión atómica del correlativo por sede

> **Estado:** diseño en papel. CERO código de producción. Plano para implementar después con foco.
> Arregla además el bug fiscal pre-existente del `COUNT(*)+1` (no atómico + reutiliza al anular).

---

## 1. Mapeo de la asignación de correlativo (todos los puntos)

### Implementación real (única)
**[CorrelativeService::getCorrelative()](app/Http/Services/Tenant/Sale/Sale/CorrelativeService.php#L22)** — `static`:
```php
$cant = SELECT count(*) from sales_documents where type_sale_id = ?;          // ← COUNT
$ds   = DocumentSerialization::where('company_id',1)->where('document_type_id',$type)->first();  // ← company_id=1
$correlative = ($cant === 0) ? $ds->start_number : $cant + 1;                  // ← COUNT+1
$serie       = $ds->serie;
return { correlative, serie };
```
**3 defectos:** (a) `COUNT(*)+1` no atómico → 2 emisiones simultáneas = mismo número; (b) al **anular** baja el count → **reutiliza** número; (c) `company_id=1` hardcodeado (ignora la sede).

### Call sites (quién asigna correlativo)
| # | Lugar | Documento(s) | ¿En transacción? | Estado |
|---|---|---|---|---|
| a | [SaleService:54](app/Http/Services/Tenant/Sale/Sale/SaleService.php#L54) + [SaleDto:141](app/Http/Services/Tenant/Sale/Sale/SaleDto.php#L141) → `$this->s_correlative->getCorrelative($type_sale_id)` | **Boleta, Factura, Nota de Venta, Ticket** (variantes de `type_sale` de la venta) | **SÍ** — [SaleController@store:238](app/Http/Controllers/Tenant/SaleController.php#L238) `beginTransaction` | ✅ real y funcionando |
| b | [ConsultasCreditosController:205](app/Http/Controllers/Tenant/Consultas/ConsultasCreditosController.php#L205) → `SaleController::getCorrelative('3'\|'1')` | Boleta/Factura desde créditos | SÍ — :174 | ⚠️ **referencia ROTA** (ver abajo) |
| c | [ReportFieldController:210](app/Http/Controllers/Tenant/Reports/ReportFieldController.php#L210) → `SaleController::getCorrelative($document_invoice)` | Documento de reserva | SÍ — :203 | ⚠️ **referencia ROTA** |

> ⚠️ **`SaleController::getCorrelative` NO EXISTE** (SaleController no define ese método ni importa CorrelativeService). Los call sites (b) y (c) llaman un método inexistente → fallarían en runtime. Son features incompletas/muertas. **A verificar/reparar** al implementar Capa C: repuntar a `CorrelativeService` o completarlas.

### Nota de Crédito / Nota de Débito / Guía
- **NC**: solo se **lee** en dashboard (`credit_notes`). NO se encontró creación con serie+correlativo.
- **ND / Guía**: **sin código de creación** (no implementado).
- [InvoiceController](app/Http/Controllers/Tenant/InvoiceController.php) arma el XML SUNAT (Greenter `Note` para NC/ND) leyendo `serie`/`correlativo` **del documento ya guardado** ([:120-121](app/Http/Controllers/Tenant/InvoiceController.php#L120)) — no asigna número.
- → Sus series (FC/FD/T) existen (Capa A) pero **están sin uso**. Nada que arreglar hoy; cuando se implementen, usarán el mismo `getCorrelative(sede,tipo)`.

### Conclusión punto 2
**El fix es UN solo lugar:** `CorrelativeService::getCorrelative`. Toda la emisión real (la venta, que cubre boleta/factura/nota venta/ticket) pasa por ahí. NC/ND/guía no emiten todavía. Los 2 call sites rotos se repuntan a la misma función.

---

## 3. Diseño de la solución atómica

### Pseudocódigo del nuevo `getCorrelative`
```
getCorrelative(sede_id, document_type_id):        # ya NO usa company_id ni COUNT
    # Corre DENTRO de la transacción del documento (SaleController@store ya la abre).
    # Lock pesimista de la fila (sede, tipo) hasta el commit:
    ds = SELECT id, serie, current_number, start_number
         FROM document_serializations
         WHERE sede_id = :sede_id AND document_type_id = :document_type_id
         FOR UPDATE

    if ds is null:
        throw "La sede activa no tiene serie para este tipo de documento."

    # Monotónico: nunca decrementa → anular NO reutiliza. Respeta start_number.
    next = max(ds.current_number + 1, ds.start_number)

    UPDATE document_serializations SET current_number = :next WHERE id = ds.id

    return { serie: ds.serie, correlative: next }
```

**Claves:**
- `FOR UPDATE` serializa emisiones concurrentes de la **misma** (sede, tipo): la 2ª espera el commit de la 1ª y toma `next+1`. Distinto sede/tipo = fila distinta = **no se bloquean** (aislamiento por sede).
- `current_number` se incrementa y persiste **en la misma transacción** del documento → si el documento hace rollback, el número también (no se "quema").
- `serie` sale de la fila de la **sede activa** (no `company_id=1`).

### ¿De dónde sale `sede_id`?
Las series cuelgan de la **sede activa** (no del almacén). Opciones:
- **(Recomendada)** El controller de emisión resuelve `sedeActivaId()` (vía `HasSedeActiva` — SaleController ya lo usa desde 3b) y lo **pasa** a `getCorrelative($sede_id, $type)`. Explícito, testeable, sin dependencia oculta de Auth dentro del service.
- Alternativa: `CorrelativeService` usa `HasSedeActiva` (Auth/Session globales). Menos explícito.

`getCorrelative` deja de ser `static` (o recibe `sede_id` como parámetro).

### Transacciones (confirmado)
- Venta: `SaleController@store:238` `beginTransaction` ✅ (envuelve `getCorrelative` vía SaleService).
- ConsultasCreditos (:174) y ReportField (:203): también en transacción ✅ (aunque su ref a getCorrelative está rota — se repunta).
- → El `FOR UPDATE` es viable en todos los puntos reales/previstos. **No hay que envolver nada nuevo.**

---

## 4. Inicialización de `current_number`

- **Tenants nuevos (sin emisión):** arranca en `0` (ya, Capa A). `next = max(0+1, start_number)`.
- **Datos existentes (producción futura con ventas ya emitidas):** migración única que setea, por cada serie:
  ```sql
  UPDATE document_serializations ds
  SET current_number = (
      SELECT IFNULL(MAX(s.correlative), 0)
      FROM sales_documents s
      WHERE s.serie = ds.serie        -- serie identifica (sede,tipo): desnormalizada en el doc
  );
  ```
  Keyear por `serie` (string único por sede+tipo) es robusto. Así el próximo número = MAX_emitido+1 → **no repite** números ya usados.
- Demo actual: sin emisión → todos en 0. La migración de init es no-op acá.

---

## 5. Riesgos y orden de aplicación

### Orden
1. **Venta primero** (boleta/factura/nota venta/ticket): ya en transacción, es el path real. Cambiar `CorrelativeService::getCorrelative` + pasar `sede_id` desde SaleController/SaleService.
2. **Reparar los 2 call sites rotos** (ConsultasCreditos, ReportField) → repuntar a la nueva firma (y verificar si esas features están vivas).
3. **NC/ND/guía**: cuando se implementen, usan la misma función. No ahora.

### "Anular reutiliza número" — RESUELTO
`current_number` es **monotónico** (solo sube, nunca baja). Anular un documento **no toca** `current_number` → el siguiente número es `current+1`, jamás el del anulado. El bug del `COUNT(*)` (que sí bajaba al anular) desaparece.

### Cómo probar
- **Atomicidad (concurrencia):** dos transacciones paralelas sobre la misma (sede, tipo):
  - T1: `BEGIN; getCorrelative(...)` (toma el lock, no commitea aún).
  - T2: `BEGIN; getCorrelative(...)` → **bloquea** esperando a T1.
  - T1 `COMMIT` → T2 desbloquea y toma el **siguiente** número. Resultado: números **distintos**, sin duplicado.
- **Anular no reutiliza:** emitir 1,2,3 (current=3); anular el 2; emitir de nuevo → sale **4** (no 2).
- **Aislamiento por sede:** emitir en sede 1 y sede 2 en paralelo → filas distintas, sin bloqueo mutuo, numeración independiente.
- **Rollback no quema número:** abrir transacción, getCorrelative (current pasa a N), forzar rollback → `current_number` vuelve a N-1 (no se perdió el número).

---

## Verificaciones (cerradas — pre-implementación)

### 1. `type_sale_id` == `document_type_id` → ✅ COINCIDEN
Cadena confirmada:
- [ValidationsService:72](app/Http/Services/Tenant/Sale/Sale/ValidationsService.php#L72) `$type_sale = GeneralTableDetail::findOrFail($data['type_sale'])` → :122 `'type_sale_id' => $type_sale->id`. Es decir **`type_sale_id` = `GeneralTableDetail.id`**.
- Capa A ([SerieService](app/Http/Services/Tenant/Maintenance/SerieService.php)) sembró `document_type_id = $tipo->id` (el mismo `GeneralTableDetail.id`).
- `getCorrelative` matchea `where('document_type_id', $type_sale)` → **busca por el id correcto**. El diseño atómico `getCorrelative(sede, document_type_id)` es válido.
- ⚠️ Sub-hallazgo: [ValidationsService:87,92](app/Http/Services/Tenant/Sale/Sale/ValidationsService.php#L87) comparan `$type_sale->id == '3'`/`'1'` (códigos SUNAT), pero los ids reales son 65/66/67 → esa validación RUC/DNI está rota en el esquema actual (id ≠ código). **Tangencial a Capa C**, fichado aparte.

### 2. Call sites rotos → VIVOS (ruteados) pero ROTOS → hay que ARREGLARLOS
- `SaleController::getCorrelative` **no existe** (sin método propio, sin trait, sin `__callStatic`; `Controller` base tampoco lo tiene). Llamarlo lanza *Call to undefined method*.
- **Ambos tienen ruta** (no son código muerto):
  - `ConsultasCreditosController::generarDocumento` → `POST consultas/creditos/generar-documento` ([web.php:80](routes/web.php#L80)).
  - `ReportFieldController::generateDocumentStore` → `POST reporte-campo/generarDocumento/store` ([web.php:146](routes/web.php#L146)).
- → Están **wired/alcanzables desde la UI** pero **crashean** al llegar a la línea del correlativo. Probablemente referencias quedadas de un viejo `SaleController::getCorrelative` que se movió a `CorrelativeService`.
- **Acción Capa C:** **REPUNTAR** ambos a la nueva firma `getCorrelative(sede_id, document_type_id)`. ⚠️ Además **corregir el argumento**: ConsultasCreditos pasa `'3'`/`'1'` (código SUNAT) y ReportField pasa `document_invoice` — deben pasar el **`GeneralTableDetail.id`** (no el código), o el match fallaría igual.

### 3. Filtro `company_id=1` en series → 1 SOLO lugar
- Único filtro de **lookup** de serie por company: [CorrelativeService:33](app/Http/Services/Tenant/Sale/Sale/CorrelativeService.php#L33) `DocumentSerialization::where('company_id',1)->where('document_type_id',$type)`. → se reemplaza por `where('sede_id', $sede)->where('document_type_id',$type)`.
- No hay otros lookups de serie por `company_id`. (Nota: `CompanyController::storeNumeration` **setea** `company_id=1` al crear una serie — escritura legada, ya superada por `generarSeriesSede`; no es un filtro de emisión. Limpiar junto con la deprecación de `company_id`.)

**Diseño cerrado.** Próxima acción: implementar Capa C **por la venta** (con OK), luego repuntar los 2 call sites, luego NC/ND/guía cuando se implementen.

---

*Diseño de solo lectura. No se modificó código de producción. Implementar con OK por documento, empezando por la venta.*
