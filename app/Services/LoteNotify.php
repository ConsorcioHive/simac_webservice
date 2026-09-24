<?php
// app/Services/LoteNotify.php
// Avisa al admin local cuando llega un lote nuevo (email + queda en badge/sidebar).
namespace App\Services;

use App\Models\Empresa;

class LoteNotify
{
    /**
     * Email al contacto de la empresa cuando un lote es NUEVO (no re-descarga).
     * Silencioso: si no hay email o falla SMTP, solo error_log.
     */
    public static function loteNuevoDisponible(string $codigo, array $opts = []): bool
    {
        $empresa = (new Empresa())->obtenerUnica();
        $email = trim((string)($empresa['email'] ?? ''));
        if ($email === '') {
            return false;
        }
        $nombre = trim((string)($empresa['nombre'] ?? 'Clínica'));
        $adm = (int)($opts['admisiones'] ?? 0);
        $docs = (int)($opts['documentos'] ?? 0);
        $ruta = (string)($opts['ruta_local'] ?? '');

        $asunto = "[$nombre] Lote de egreso disponible: $codigo";
        $html = '
        <div style="font-family:Arial,sans-serif;font-size:14px;color:#1e3a5f;">
          <h2 style="margin-top:0;">Nuevo lote de egreso listo</h2>
          <p>Se descargó y confirmó el lote <strong>' . htmlspecialchars($codigo) . '</strong>.</p>
          <ul>
            <li>Empresa: <strong>' . htmlspecialchars($nombre) . '</strong></li>
            <li>Admisiones: <strong>' . $adm . '</strong></li>
            <li>Documentos: <strong>' . $docs . '</strong></li>
            <li>Estado: <strong>disponible</strong> para el ERP</li>
          </ul>
          ' . ($ruta !== '' ? '<p>Ruta local:<br><code>' . htmlspecialchars($ruta) . '</code></p>' : '') . '
          <p style="color:#666;font-size:12px;">Mensaje automático de SIMAC Webservice.</p>
        </div>';
        $texto = "Lote $codigo disponible para el ERP.\nAdmisiones: $adm\nDocs: $docs\nRuta: $ruta\n";

        $ok = Mailer::enviar($email, $nombre, $asunto, $html, $texto);
        if (!$ok) {
            error_log("LoteNotify: fallo email lote $codigo → $email");
        }
        return $ok;
    }
}
