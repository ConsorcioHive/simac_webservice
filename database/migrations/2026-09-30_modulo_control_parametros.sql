-- database/migrations/2026-09-30_modulo_control_parametros.sql
-- Espejo local del flag de la nube (simacweb.app companies.modulo_control_parametros).
-- Solo controla la UI del webservice: si no es 'sismed', el input migracion.zip
-- no se muestra y el POST se rechaza. La nube sigue siendo la autoridad y
-- vuelve a validar en apiSyncRecibir(), asi que un flag local obsoleto
-- solo produce un rechazo claro, nunca una carga indebida.

ALTER TABLE empresas
    ADD COLUMN modulo_control_parametros VARCHAR(20) NOT NULL DEFAULT 'estandar'
    AFTER tipo;

-- Unica empresa SISMED por ahora: CECOF (RIF J305831157, company_code 1051).
UPDATE empresas
   SET modulo_control_parametros = 'sismed'
 WHERE REPLACE(REPLACE(REPLACE(COALESCE(rif, ''), '-', ''), ' ', ''), '.', '') = 'J305831157';