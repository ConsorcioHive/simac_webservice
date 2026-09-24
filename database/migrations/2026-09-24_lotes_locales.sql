-- database/migrations/2026-09-24_lotes_locales.sql
-- Estado local de cada lote descargado (para el ERP externo).
-- Flujo: (implícito al confirmar) descargado → disponible → consumido

CREATE TABLE IF NOT EXISTS lotes_locales (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    company_code     VARCHAR(20)  NOT NULL,
    codigo           VARCHAR(64)  NOT NULL,
    estado           VARCHAR(20)  NOT NULL DEFAULT 'descargado',
    ruta_local       TEXT,
    total_admisiones INT          DEFAULT 0,
    total_documentos INT          DEFAULT 0,
    docs_ok          INT          DEFAULT 0,
    metadata         JSON         DEFAULT NULL,
    descargado_en    DATETIME     DEFAULT NULL,
    disponible_en    DATETIME     DEFAULT NULL,
    consumido_en     DATETIME     DEFAULT NULL,
    created_at       DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_lotes_locales_code (company_code, codigo),
    KEY idx_lotes_locales_estado (company_code, estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
