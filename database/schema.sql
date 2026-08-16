-- database/schema.sql
-- Esquema MySQL local (MariaDB/XAMPP), derivado de las tablas reales de SIMAC
-- (public.companies y public.usuarios en PostgreSQL), adaptado a MySQL.
-- Sistema single-tenant: una sola empresa por instalación.

CREATE TABLE IF NOT EXISTS empresas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    parent_id           INT          DEFAULT NULL,
    company_code        VARCHAR(20)  NOT NULL,
    tipo                SMALLINT     NOT NULL DEFAULT 0,
    codigo_pais         CHAR(2)      DEFAULT NULL,
    codigo_estado       CHAR(3)      DEFAULT NULL,
    codigo_municipio    CHAR(4)      DEFAULT NULL,
    codigo_parroquia    CHAR(5)      DEFAULT NULL,
    nombre              VARCHAR(150) NOT NULL,
    slogan              VARCHAR(255) DEFAULT NULL,
    rif                 VARCHAR(20)  DEFAULT NULL,
    direccion_fiscal    VARCHAR(255) DEFAULT NULL,
    codigo_postal       VARCHAR(15)  DEFAULT NULL,
    moneda_codigo       CHAR(3)      DEFAULT NULL,
    moneda_nombre       VARCHAR(100) DEFAULT NULL,
    moneda_simbolo      VARCHAR(10)  DEFAULT NULL,
    telefono            VARCHAR(50)  DEFAULT NULL,
    email               VARCHAR(150) DEFAULT NULL,
    latitud             VARCHAR(30)  DEFAULT NULL,
    longitud            VARCHAR(30)  DEFAULT NULL,
    usuario_contacto_id INT          DEFAULT NULL,
    idioma              VARCHAR(5)   DEFAULT 'es',
    timezone            VARCHAR(50)  DEFAULT 'America/Caracas',
    notif_email         TINYINT(1)   DEFAULT 0,
    notif_sms           TINYINT(1)   DEFAULT 0,
    logo_url            VARCHAR(255) DEFAULT NULL,
    business_card_url   VARCHAR(255) DEFAULT NULL,
    empresa_description TEXT,
    empresa_documento_url VARCHAR(255) DEFAULT NULL,
    empresa_caratula_url  VARCHAR(255) DEFAULT NULL,
    acepta_terminos     TINYINT(1)   DEFAULT 0,
    acepta_privacidad   TINYINT(1)   DEFAULT 0,
    redes               VARCHAR(255) DEFAULT NULL,
    redes_id            TEXT,
    redes_url           TEXT,
    moneda_pago_id      INT          DEFAULT NULL,
    serial_licencia     VARCHAR(60)  DEFAULT NULL,
    identificador_saint VARCHAR(10)  DEFAULT NULL,
    fecha_expiracion    DATE         DEFAULT NULL,
    entity_folders      JSON         DEFAULT NULL,
    -- Campos específicos del webservice local (recepción JSON / conexión SIMAC)
    caratula_url        VARCHAR(255) DEFAULT NULL,
    empresa_descripcion TEXT,
    simac_cloud_url     VARCHAR(255) DEFAULT NULL,
    simac_api_token     VARCHAR(255) DEFAULT NULL,
    contenedor_carpeta  VARCHAR(100) DEFAULT NULL,
    contenedor_url      VARCHAR(255) DEFAULT NULL,
    created_at          DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_empresa_code (company_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla geográfica (Venezuela) clonada de SIMAC public.pais_edo_ciudad
CREATE TABLE IF NOT EXISTS pais_edo_ciudad (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    codigo_pais     VARCHAR(5)  NOT NULL,
    codigo_estado   VARCHAR(10) DEFAULT NULL,
    codigo_municipio VARCHAR(10) DEFAULT NULL,
    codigo_parroquia VARCHAR(10) DEFAULT NULL,
    nombre          VARCHAR(150) NOT NULL,
    nivel           SMALLINT    NOT NULL,
    codigo_postal   VARCHAR(15) DEFAULT NULL,
    moneda_codigo   VARCHAR(10) DEFAULT NULL,
    moneda_nombre   VARCHAR(50) DEFAULT NULL,
    moneda_simbolo  VARCHAR(5)  DEFAULT NULL,
    activo          TINYINT(1)  NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Monedas del sistema, clonadas de SIMAC public.monedas
CREATE TABLE IF NOT EXISTS monedas (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    codigo            VARCHAR(3)  NOT NULL,
    nombre            VARCHAR(100) NOT NULL,
    simbolo           VARCHAR(10) NOT NULL,
    es_moneda_base    TINYINT(1)  DEFAULT 0,
    factor_conversion DECIMAL(20,10) DEFAULT NULL,
    fecha_actualizacion DATETIME   DEFAULT NULL,
    activa            TINYINT(1)  DEFAULT 1,
    creada_en         DATETIME    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    cedula           VARCHAR(20)  DEFAULT NULL,
    nombre           VARCHAR(100) NOT NULL,
    apellido         VARCHAR(100) DEFAULT NULL,
    login            VARCHAR(50)  DEFAULT NULL,
    email            VARCHAR(100) NOT NULL,
    foto             VARCHAR(255) DEFAULT NULL,
    password_hash    VARCHAR(255) NOT NULL,
    telefono         VARCHAR(20)  DEFAULT NULL,
    cargo            VARCHAR(50)  DEFAULT NULL,
    rol              VARCHAR(50)  NOT NULL DEFAULT 'asistente',
    empresa_id       INT          DEFAULT NULL,
    estado           VARCHAR(50)  NOT NULL DEFAULT 'pendiente',
    fecha_registro   DATETIME     DEFAULT CURRENT_TIMESTAMP,
    created_by       INT          DEFAULT NULL,
    UNIQUE KEY uq_empresa_login (empresa_id, login),
    UNIQUE KEY uq_empresa_email (empresa_id, email),
    CONSTRAINT fk_usuario_empresa FOREIGN KEY (empresa_id)
        REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sync_log (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    tipo       VARCHAR(50)  DEFAULT NULL,
    direccion  VARCHAR(10)  DEFAULT NULL,
    estado     VARCHAR(20)  DEFAULT NULL,
    registros  INT          DEFAULT 0,
    mensaje    TEXT,
    creado_en  DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_sync_creado ON sync_log (creado_en);
