-- database/seed.sql
-- Datos iniciales: una empresa (single-tenant) y el superadmin local.
-- La empresa de ejemplo es "Clínica La Isabelica".
-- Superadmin -> login: admin | password: 123456  (cámbiala tras el primer login).

INSERT INTO empresas (company_code, nombre, rif, direccion_fiscal, telefono, email, logo_url, caratula_url)
VALUES ('LAISABELICA', 'Clínica La Isabelica', 'J-00000000', 'Dirección fiscal de ejemplo', '0212-0000000', 'contacto@laisabelica.com',
        'assets/images/empresa/logo_laisabelica.jpg', 'assets/images/empresa/caratula_laisabelica.png');

INSERT INTO usuarios (empresa_id, cedula, nombre, apellido, login, email, password_hash, rol, estado)
SELECT 1, '00000000', 'Superadmin', 'La Isabelica', 'admin', 'admin@clinica.local',
       '$2y$10$2gAsB0XASka8KIwlL52x4.shoX64cmUtAxGwuPDruI/TtB/Kg9ItS', 'superadmin', 'activo'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE login = 'admin');
