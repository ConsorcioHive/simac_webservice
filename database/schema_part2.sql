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