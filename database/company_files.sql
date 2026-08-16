-- database/company_files.sql
-- Tabla para el contenedor de archivos de empresas (MySQL)
CREATE TABLE IF NOT EXISTS company_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    company_code VARCHAR(50) NOT NULL,
    parent_id INT DEFAULT NULL,
    is_folder TINYINT(1) NOT NULL DEFAULT 0,
    nombre VARCHAR(255) NOT NULL,
    path_relativo VARCHAR(500) NOT NULL,
    extension VARCHAR(10) DEFAULT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,
    size_bytes BIGINT DEFAULT NULL,
    subido_por INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_company (company_id),
    INDEX idx_parent (parent_id),
    INDEX idx_code (company_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;