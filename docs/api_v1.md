# API v1 — simac_webservice

Contrato para sistemas externos (ERP, integraciones).  
**No usa sesión de usuario.** Autenticación por API key.

## Autenticación

Envíe en cada request uno de:

| Header | Ejemplo |
|--------|---------|
| `X-API-Key` | `X-API-Key: erp_isabelica_...` |
| `Authorization` | `Authorization: Bearer erp_isabelica_...` |

Fallback (solo si el servidor no reenvía headers): `?api_key=...`

La key resuelve el **`company_code`** del tenant (tabla local `api_tokens`).

- Crear key: `INSERT INTO api_tokens (company_code, token, nombre) VALUES ('1013', '...', 'ERP Facturación');`
- Desactivar: `UPDATE api_tokens SET activo = 0 WHERE token = '...';`

## Estados de un lote (local)

| Estado | Significado |
|--------|-------------|
| `descargado` | Bajado de la nube y en disco (confirmación pendiente o legado) |
| `disponible` | Listo para que el ERP lo consuma |
| `consumido` | El ERP lo procesó; no reenviar |

Flujo: nube `enviado_local` → cron/botón local descarga → confirmación nube `recibido_local` + local **`disponible`** → ERP **`consumir`** → `consumido`.

---

## Endpoints

Base (XAMPP): `http://localhost/simac_webservice/api/v1/...`  
Fallback sin rewrite: `http://localhost/simac_webservice/index.php?page=api_v1&path=lotes`

### GET /salud

Ping + tenant autenticado.

```bash
curl -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/salud"
```

```json
{ "ok": true, "data": { "servicio": "simac_webservice", "api": "v1", "empresa": "1013", "time": "..." } }
```

### GET /lotes

Lista lotes locales. Query opcional: `estado=disponible|descargado|consumido`.

```bash
curl -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/lotes?estado=disponible"
```

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
        "en_disco": true,
        "descargado_en": "2026-09-24 ...",
        "disponible_en": "2026-09-24 ..."
      }
    ],
    "total": 1
  }
}
```

### GET /lotes/{codigo}

Detalle + manifiesto (empresa, fechas, admisiones con correlativo/paciente).

```bash
curl -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/lotes/20260923204831_1013_0001_L1"
```

### GET /lotes/{codigo}/archivos

Árbol de archivos. Query: `sub=admision_8` (opcional).

Cada item incluye `ruta_local` absoluta para que el ERP recoja la data sin adivinar carpetas.

```bash
curl -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/lotes/COD/archivos"
curl -H "X-API-Key: TU_KEY" "http://localhost/simac_webservice/api/v1/lotes/COD/archivos?sub=admision_8"
```

### POST /lotes/{codigo}/consumir

Marca el lote como **`consumido`**. Idempotente si ya estaba consumido (`ya_consumido: true`).

```bash
curl -X POST -H "X-API-Key: TU_KEY" -H "Content-Type: application/json" \
  "http://localhost/simac_webservice/api/v1/lotes/COD/consumir"
```

```json
{ "ok": true, "data": { "codigo": "COD", "estado": "consumido", "consumido_en": "..." } }
```

---

## Errores

| HTTP | Cuerpo |
|------|--------|
| 401 | `{ "ok": false, "error": "API key inválida o inactiva." }` |
| 404 | Lote o ruta inexistente |
| 405 | Método no permitido |
| 409 | Estado no permite la operación |

---

## Cobertura por empresa

Toda respuesta se filtra por el **`company_code` de la API key**. Una key de `1013` no ve lotes de otra clínica.

## Relación con la nube

| Quién | Token | Hacia dónde |
|-------|-------|-------------|
| Webservice → nube | `empresas.simac_api_token` (o fallback config) | `https://simacweb.app` |
| ERP → webservice | `api_tokens.token` (`X-API-Key`) | esta API `/api/v1` |

## Pull automático (Task Scheduler)

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
