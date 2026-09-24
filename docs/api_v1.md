# API v1 — simac_webservice

Contrato completo para sistemas externos (ERP, integraciones).  
**No usa sesión de usuario.** Autenticación por API key.

> Archivo de ejemplo listo para copiar al ERP: [`docs/ejemplo_cliente_erp.php`](ejemplo_cliente_erp.php)

---

## 1. Visión general

```
┌─────────────────────┐   pull cada 2 min    ┌──────────────────────────────┐
│  NUBE simacweb.app  │ ───────────────────► │  WEBSERVICE (XAMPP local)    │
│  (clínica SIMAC)    │  token empresas.*    │  public/uploads/.../lotes/    │
└─────────────────────┘                      └──────────────┬───────────────┘
                                                            │  X-API-Key
                                                            ▼
                                               ┌──────────────────────────────┐
                                               │  ERP / sistema externo       │
                                               │  llama GET /api/v1/lotes     │
                                               └──────────────────────────────┘
```

**Dos flujos distintos:**

| Flujo | Quién | Cómo |
|-------|-------|------|
| Nube → webservice | Windows Task `SIMAC_TraerLotes` (cada 2 min) | Descarga lotes `enviado_local`, los deja `disponible` |
| **ERP → webservice** | **Tu sistema** | **API `/api/v1` con `X-API-Key`** |

El ERP **nunca** habla con la nube; solo consume la API del webservice.

---

## 2. Autenticación

Envíe en **cada request** uno de:

| Header | Ejemplo |
|--------|---------|
| `X-API-Key` | `X-API-Key: erp_1013_...` |
| `Authorization` | `Authorization: Bearer erp_1013_...` |

Fallback (solo si el servidor no reenvía headers): `?api_key=...`

La key resuelve el **`company_code`** del tenant (tabla local `api_tokens`).  
Toda respuesta se filtra por ese tenant: una key de `1013` **no ve** lotes de otra clínica.

**Crear key:**
```sql
INSERT INTO api_tokens (company_code, token, nombre) VALUES ('1013', 'erp_1013_...', 'ERP Facturación');
```

**Desactivar:**
```sql
UPDATE api_tokens SET activo = 0 WHERE token = 'erp_1013_...';
```

---

## 3. Estados de un lote (local)

| Estado | Significado | Quién lo cambia |
|--------|-------------|-----------------|
| `descargado` | Bajado de la nube y en disco (confirmación pendiente o legado) | Descarga |
| `disponible` | **Listo para que el ERP lo consuma** | Cron / botón "Traer" |
| `consumido` | El ERP lo procesó; no reenviar | `POST /lotes/{codigo}/consumir` |

**Flujo completo:**

```
nube: generado → enviado_local ──pull──► nube: recibido_local
                                          local: descargado → disponible
                                                    │
                                          ERP: POST /consumir
                                                    ▼
                                          local: consumido
```

---

## 4. Endpoints

**Base (XAMPP):** `http://localhost/simac_webservice/api/v1/...`  
**Fallback sin rewrite:** `http://localhost/simac_webservice/index.php?page=api_v1&path=lotes`

Todas las respuestas son JSON con forma:

```json
{ "ok": true,  "data": { ... } }
{ "ok": false, "error": "mensaje" }
```

---

### GET /salud

Ping + tenant autenticado. Útil para verificar conexión y key.

```bash
curl -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/salud"
```

**200 OK:**
```json
{
    "ok": true,
    "data": {
        "servicio": "simac_webservice",
        "api": "v1",
        "empresa": "1013",
        "time": "2026-09-24T18:49:14+02:00"
    }
}
```

---

### GET /lotes

Lista lotes locales. Query opcional: `estado=disponible|descargado|consumido` (vacío = todos).

```bash
curl -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/lotes?estado=disponible"
```

**200 OK:**
```json
{
    "ok": true,
    "data": {
        "lotes": [
            {
                "codigo": "20260923204831_1013_0001_L1",
                "estado": "disponible",
                "ruta_local": "C:\\xampp\\htdocs\\simac_webservice\\public\\uploads\\empresas\\1013\\archivos\\lotes_egresos\\20260923204831_1013_0001_L1\\",
                "total_admisiones": 3,
                "total_documentos": 3,
                "descargado_en": "2026-09-24 12:20:22",
                "disponible_en": "2026-09-24 12:20:22",
                "consumido_en": null,
                "updated_at": "2026-09-24 12:21:11"
            }
        ],
        "total": 1
    }
}
```

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `codigo` | string | Identificador único del lote |
| `estado` | string | `descargado` \| `disponible` \| `consumido` |
| `ruta_local` | string | Ruta absoluta en disco (para leer archivos directamente) |
| `total_admisiones` | int\|null | Admisiones del manifiesto |
| `total_documentos` | int\|null | Documentos del manifiesto |
| `en_disco` | bool | (solo en algunos casos) si la carpeta existe |
| `descargado_en` | string\|null | Cuándo se bajó de la nube |
| `disponible_en` | string\|null | Cuándo pasó a `disponible` |
| `consumido_en` | string\|null | Cuándo lo consumió el ERP |

---

### GET /lotes/{codigo}

Detalle + manifiesto (empresa, fechas, admisiones con correlativo/paciente).

```bash
curl -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/lotes/20260923204831_1013_0001_L1"
```

**200 OK:**
```json
{
    "ok": true,
    "data": {
        "codigo": "20260923204831_1013_0001_L1",
        "company_code": "1013",
        "estado": "disponible",
        "ruta_local": "C:\\...\\lotes_egresos\\20260923204831_1013_0001_L1\\",
        "ruta_relativa": "public/uploads/empresas/1013/archivos/lotes_egresos/20260923204831_1013_0001_L1/",
        "descargado_en": "2026-09-24 12:20:22",
        "disponible_en": "2026-09-24 12:20:22",
        "consumido_en": null,
        "manifiesto": {
            "empresa": "Centro Clínico la Isabelica, C.A.",
            "fecha_hora": "2026-09-23 20:48:31",
            "total_admisiones": 3,
            "total_documentos": 3,
            "admisiones": [
                {
                    "id": 11,
                    "correlativo": 5,
                    "fecha": "2026-09-15",
                    "paciente_nombre": null,
                    "responsable_tipo": "paciente",
                    "total_documentos": 0
                },
                {
                    "id": 8,
                    "correlativo": 2,
                    "fecha": "2026-08-27",
                    "paciente_nombre": null,
                    "responsable_tipo": "aseguradora",
                    "total_documentos": 3
                }
            ]
        }
    }
}
```

> **Nota:** `paciente_nombre` puede ser `null` en lotes antiguos; los lotes nuevos lo traen poblado. El campo `id` es el ID interno de la admisión; `correlativo` es el número visible en SIMAC (`admision_8` en disco = `correlativo` 2).

---

### GET /lotes/{codigo}/archivos

Árbol de archivos del lote. Query opcional: `sub=admision_8` para entrar a una subcarpeta.

```bash
curl -H "X-API-Key: TU_KEY" ".../lotes/COD/archivos"
curl -H "X-API-Key: TU_KEY" ".../lotes/COD/archivos?sub=admision_8"
```

**200 OK:**
```json
{
    "ok": true,
    "data": {
        "codigo": "20260923204831_1013_0001_L1",
        "sub": "",
        "ruta_local": "C:\\...\\lotes_egresos\\20260923204831_1013_0001_L1\\",
        "items": [
            {
                "nombre": "admision_8",
                "tipo": "dir",
                "ruta": "admision_8",
                "ruta_local": "C:\\...\\lotes_egresos\\...\\admision_8\\",
                "tam": null,
                "mtime": "2026-09-23T23:38:29+02:00",
                "admision_id": 8
            },
            {
                "nombre": "consolidado_20260923204831_1013_0001_L1.json",
                "tipo": "file",
                "ruta": "consolidado_20260923204831_1013_0001_L1.json",
                "ruta_local": "C:\\...\\consolidado_20260923204831_1013_0001_L1.json",
                "tam": 32134,
                "mtime": "2026-09-23T23:38:29+02:00"
            },
            {
                "nombre": "manifiesto_20260923204831_1013_0001_L1.json",
                "tipo": "file",
                "ruta": "manifiesto_20260923204831_1013_0001_L1.json",
                "ruta_local": "C:\\...\\manifiesto_20260923204831_1013_0001_L1.json",
                "tam": 3768,
                "mtime": "2026-09-23T23:38:29+02:00"
            }
        ]
    }
}
```

Cada item incluye `ruta_local` **absoluta** para que el ERP recoja la data sin adivinar carpetas.

**Archivos típicos de un lote:**

```
<codigo_lote>/
├── consolidado_<codigo>.json   ← payload completo de todas las admisiones
├── manifiesto_<codigo>.json    ← índice: empresa, fechas, admisiones
└── admision_<id>/
    └── <adjunto...>.pdf|jpg|...
```

---

### POST /lotes/{codigo}/consumir

Marca el lote como **`consumido`**. Idempotente si ya estaba consumido (`ya_consumido: true`).

```bash
curl -X POST -H "X-API-Key: TU_KEY" -H "Content-Type: application/json" \
  "http://localhost/simac_webservice/api/v1/lotes/COD/consumir"
```

**200 OK (primera vez):**
```json
{ "ok": true, "data": { "codigo": "COD", "estado": "consumido", "consumido_en": "2026-09-24 19:00:00" } }
```

**200 OK (repetido):**
```json
{ "ok": true, "data": { "codigo": "COD", "estado": "consumido", "ya_consumido": true, "consumido_en": "..." } }
```

---

## 5. Manejo de errores

| HTTP | Cuerpo | Causa |
|------|--------|-------|
| 401 | `{ "ok": false, "error": "Falta API key..." }` | No envió header |
| 401 | `{ "ok": false, "error": "API key inválida o inactiva." }` | Key incorrecta o `activo = 0` |
| 400 | `{ "ok": false, "error": "Código de lote inválido." }` | Caracteres no permitidos en el código |
| 404 | `{ "ok": false, "error": "Lote no existe..." }` | Código inexistente |
| 405 | `{ "ok": false, "error": "Método no permitido..." }` | GET vs POST invertidos |
| 409 | `{ "ok": false, "error": "Estado actual 'X' no permite consumir..." }` | Estado inesperado |

Siempre revise tanto el código HTTP como el campo `ok`.

---

## 6. Ejemplo listo para el ERP (PHP + cURL)

Archivo completo y ejecutable: **[`docs/ejemplo_cliente_erp.php`](ejemplo_cliente_erp.php)**

```php
<?php
// Configuración
$BASE = 'http://localhost/simac_webservice/api/v1';
$KEY  = 'erp_1013_1c4e76d7b8ff65a8bdb086181a1bcd2a7e51c922';

function api(string $method, string $path, string $base, string $key): array {
    $ch = curl_init(rtrim($base, '/') . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => [
            'X-API-Key: ' . $key,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($body === false) {
        return ['ok' => false, 'error' => 'cURL: ' . $err];
    }
    $json = json_decode($body, true);
    if (!is_array($json)) {
        return ['ok' => false, 'error' => 'Respuesta no JSON (HTTP ' . $code . ')', 'raw' => $body];
    }
    $json['_http'] = $code;
    return $json;
}

// 1) Verificar conexión
$salud = api('GET', '/salud', $BASE, $KEY);
if (empty($salud['ok'])) {
    die('API no disponible: ' . ($salud['error'] ?? '?') . "\n");
}

// 2) Listar lotes pendientes
$lista = api('GET', '/lotes?estado=disponible', $BASE, $KEY);
$lotes = $lista['data']['lotes'] ?? [];

// 3) Por cada lote: detalle, archivos, y marcar consumido
foreach ($lotes as $l) {
    $codigo = $l['codigo'];

    $detalle = api('GET', '/lotes/' . $codigo, $BASE, $KEY);
    $archivos = api('GET', '/lotes/' . $codigo . '/archivos', $BASE, $KEY);

    // ... aquí el ERP procesa: lee $l['ruta_local'] o usa $archivos['data']['items']

    $consumir = api('POST', '/lotes/' . $codigo . '/consumir', $BASE, $KEY);
    echo $codigo . ' → ' . ($consumir['data']['estado'] ?? 'error') . "\n";
}
```

**Flujo mínimo que debe seguir el ERP:**

1. `GET /salud` → verificar que la API y la key funcionan.
2. `GET /lotes?estado=disponible` → obtener lotes pendientes.
3. `GET /lotes/{codigo}` → leer manifiesto (admisiones, correlativos, pacientes).
4. `GET /lotes/{codigo}/archivos` → obtener árbol y `ruta_local` de cada archivo.
5. Procesar los archivos (leer desde `ruta_local` o copiarlos).
6. `POST /lotes/{codigo}/consumir` → confirmar que ya no debe reenviarse.

---

## 7. Polling recomendado (cada 5 minutos)

```php
while (true) {
    $lista = api('GET', '/lotes?estado=disponible', $BASE, $KEY);
    foreach (($lista['data']['lotes'] ?? []) as $l) {
        procesarLote($l['codigo']);
    }
    sleep(300); // 5 minutos
}
```

O desde cron/Task Scheduler cada 5 minutos invocando un script PHP que ejecute el mismo paso 2–6.

---

## 8. Cobertura por empresa

Toda respuesta se filtra por el **`company_code` de la API key**. Una key de `1013` no ve lotes de otra clínica.

## 9. Relación con la nube

| Quién | Token | Hacia dónde |
|-------|-------|-------------|
| Webservice → nube | `empresas.simac_api_token` (o fallback config) | `https://simacweb.app` |
| ERP → webservice | `api_tokens.token` (`X-API-Key`) | esta API `/api/v1` |

---

## 10. Pull automático (Task Scheduler)

`sync\traer_lotes.bat` corre **cada 2 minutos** (tarea Windows `SIMAC_TraerLotes`):

1. Consulta lotes `enviado_local` en la nube  
2. Descarga JSON + adjuntos  
3. Confirma → nube `recibido_local`  
4. Local → estado **`disponible`**  
5. Si es lote **nuevo**, email al `empresas.email`  
6. Badge rojo en el sidebar “Lotes de Egreso” = cantidad `disponible`

Log: `storage\logs\traer_lotes.log`

Recrear la tarea (si hace falta):

```bat
schtasks /create /tn "SIMAC_TraerLotes" /tr "C:\xampp\htdocs\simac_webservice\sync\traer_lotes.bat" /sc minute /mo 2 /f
```

---

## 11. Preguntas frecuentes

**¿La respuesta no es JSON?**  
Apache no está reescribiendo `/api/v1/...`. Use el fallback:  
`index.php?page=api_v1&path=lotes`

**¿Recibo 401 aunque envío la key?**  
Verifique que no la esté enviando mal (espacios, comillas). Si usa `Authorization`, debe ser `Bearer <token>`.

**¿El ERP no ve archivos?**  
Confirme que `ruta_local` apunta a una carpeta existente; si el lote está `consumido`, los archivos siguen en disco pero no debe reprocesarlos.

**¿Cómo sé si un lote ya fue procesado?**  
El campo `consumido_en` no nulo en `GET /lotes` o el estado `consumido`.

**¿Se puede consumir dos veces?**  
Sí, es idempotente: devuelve `ya_consumido: true` sin error.

---

## 12. Prueba rápida con curl

```bash
# Salud
curl -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/salud"

# Lotes disponibles
curl -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/lotes?estado=disponible"

# Detalle
curl -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/lotes/20260923204831_1013_0001_L1"

# Archivos
curl -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/lotes/20260923204831_1013_0001_L1/archivos"

# Consumir
curl -X POST -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/lotes/20260923204831_1013_0001_L1/consumir"
```

---

## 13. Archivos relacionados

| Archivo | Contenido |
|---------|-----------|
| `app/Controllers/ApiController.php` | Implementación de la API |
| `app/Controllers/LotesEgresosController.php` | Descarga desde la nube y estados |
| `docs/ejemplo_cliente_erp.php` | Cliente PHP de ejemplo para el ERP |
| `docs/api_v1.md` | Este documento |
| `sync/traer_lotes.bat` | Pull automático (Task Scheduler) |
| `database/migrations/2026-09-24_api_tokens.sql` | Tabla `api_tokens` |
| `database/migrations/2026-09-24_lotes_locales.sql` | Tabla `lotes_locales` |
