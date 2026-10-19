-- database/migrations/2026-10-19_01_lotes_locales_v2.sql
-- Estado local enriquecido para el flujo de lotes de egreso v2.
-- Formato nuevo: manifiesto + datos.json por admisión + consolidado de compatibilidad.

ALTER TABLE lotes_locales
    ADD COLUMN IF NOT EXISTS estado_nube        VARCHAR(30) NULL,
    ADD COLUMN IF NOT EXISTS formato            VARCHAR(10) DEFAULT '2.0',
    ADD COLUMN IF NOT EXISTS manifest_hash      CHAR(64) NULL,
    ADD COLUMN IF NOT EXISTS error_msg          TEXT NULL,
    ADD COLUMN IF NOT EXISTS intentos           INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS notificado_en      DATETIME NULL,
    ADD COLUMN IF NOT EXISTS cloud_confirmado_en DATETIME NULL,
    ADD COLUMN IF NOT EXISTS procesado_nube_en  DATETIME NULL;
