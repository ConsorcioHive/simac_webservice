-- database/schema.sql
-- Esquema MySQL local (MariaDB/XAMPP), derivado de las tablas reales de SIMAC
-- (public.companies y public.usuarios en PostgreSQL), adaptado a MySQL.
-- Sistema single-tenant: una sola empresa por instalación.

CREATE TABLE IF NOT EXISTS empresas (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    company_code       VARCHAR(20)  NOT NULL,
    nombre             VARCHAR(150) NOT NULL,
    slogan             VARCHAR(255) DEFAULT NULL,
    rif                VARCHAR(20)  DEFAULT NULL,
    direccion_fiscal   VARCHAR(255) DEFAULT NULL,
    telefono           VARCHAR(50)  DEFAULT NULL,
    email              VARCHAR(150) DEFAULT NULL,
    logo_url           VARCHAR(255) DEFAULT NULL,
    caratula_url       VARCHAR(255) DEFAULT NULL,
    empresa_descripcion TEXT,
    moneda_codigo      CHAR(3)      DEFAULT NULL,
    moneda_simbolo     VARCHAR(10)  DEFAULT NULL,
    idioma             VARCHAR(5)   DEFAULT 'es',
    timezone           VARCHAR(50)  DEFAULT 'America/Caracas',
    created_at         DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME     DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_empresa_code (company_code)
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
