-- database/migrations/2026-09-24_api_tokens.sql
-- API pública del webservice (ERP externo → simac_webservice).
-- Un token por company_code (multi-tenant). Auth: header X-API-Key.

CREATE TABLE IF NOT EXISTS api_tokens (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    company_code VARCHAR(20)  NOT NULL,
    token       VARCHAR(64)  NOT NULL,
    nombre      VARCHAR(80)  NOT NULL DEFAULT 'integracion',
    activo      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_api_tokens_token (token),
    KEY idx_api_tokens_code (company_code),
    KEY idx_api_tokens_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
