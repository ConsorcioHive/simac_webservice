# Sincronización Control de Parámetros — Webservice local (simac_webservice)

Fecha: 2026-08-15

## Rol de este sistema

Es el componente **local de cada clínica**. Un sistema externo (SAINT u otro) deposita
los archivos JSON de control de parámetros en la carpeta local configurada, y este
webservice se encarga de llevarlos a SIMAC cloud (nube) automáticamente.

## Configuración por clínica (pestaña Administración de la empresa)

| Campo | Descripción |
|-------|-------------|
| `simac_cloud_url` | URL de la nube (p. ej. `https://simacweb.app`). Vacío = valor global. |
| `simac_api_token` | Token exclusivo de la empresa (lo define el admin de SIMAC). Si hay token, el cliente sale de modo stub. |
| `ruta_control_parametros` | Ruta (relativa a la raíz o absoluta) donde el sistema externo deposita `prespuestos.json` y `servicios.json`. Vacío = ruta por defecto `public/uploads/empresas/<company_code>/archivos/control_parametros/`. |

Estos valores viven en la tabla `empresas` (MySQL local). El SyncController los lee para
construir el manifiesto y enrutar los archivos.

## Proceso automático

`sync/procesar.php` (para Task Scheduler / cron local, ejecución periódica):

1. Mueve archivos de `storage/inbox/` a `storage/outbox/` (compatibilidad flujo anterior).
2. **Detección de cambios**: calcula el hash de `prespuestos.json` + `servicios.json` y
   lo compara con el último hash subido con éxito (registro `control_parametros_hash` en
   `sync_log`). Solo sube si cambió → evita reenviar lo mismo en cada corrida.
3. Construye el `manifest.json` (token, codigo_clinica, fecha_envio, enviado_por, estado
   pendiente, archivos) y hace **POST multipart** a
   `SIMAC_CLOUD_URL/index.php?page=control_parametros_sync_recibir`.
4. Lee la respuesta que deja la nube: `respuesta_<token>.json` en la carpeta de control.

## Scripts CLI

| Script | Descripción |
|--------|-------------|
| `sync/procesar.php` | Proceso automático completo (detectar cambios → subir → leer respuesta). |
| `sync/subir_archivos.php` | Subida forzada de control de parámetros. |
| `sync/leer_respuesta.php` | Lee la respuesta de la nube. |
| `sync/subir.php` / `sync/bajar.php` | Paquete clínica (subida/bajada general, compatible). |

## Área de consulta de log local

- `index.php?page=sync_log` — historial completo local paginado (50 por página):
  cada subida, detección, respuesta y transición de archivos.
- El historial de la **nube** se consulta en el módulo Control de Parámetros → pestaña
  "Historial de Sincronización".

## Código

- `app/Controllers/SyncController.php` — `subirArchivos($soloSiCambio)`, `leerRespuesta()`,
  `log()`, `carpetaControlParametros()`, `ultimoHashSubido()`.
- `app/Services/SimacCloudClient.php` — `postMultipart()` (envío real de archivos) y
  modo real automático cuando hay token.
- `app/Controllers/EmpresaController.php` + `app/views/empresas/form.php` — campos de
  configuración por clínica.
- `app/views/sync/index.php` — botones de subir control / leer respuesta.
- `app/views/sync/log.php` — historial local.
- `index.php` — ruta `sync_log`.

## Notas

- Tabla `empresas` local requiere las columnas: `simac_cloud_url`, `simac_api_token`,
  `ruta_control_parametros` (migración ALTER aplicada en la instalación 1013).
- Modo stub (`SIMAC_API_STUB`) se desactiva automáticamente si hay token configurado.