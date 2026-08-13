<?php
// app/Services/Mailer.php
namespace App\Services;

// Envío de correos vía SMTP (PHPMailer), igual que en SIMAC cloud.
class Mailer
{
    public static function enviar($paraEmail, $paraNombre, $asunto, $html, $textoPlano = '', $adjuntos = [])
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'unare.ryz.tepuyserver.net';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'info@simacweb.app';
            $mail->Password   = 'ceph7065079';
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('info@simacweb.app', 'SIMAC Notificaciones');
            $mail->addAddress($paraEmail, $paraNombre ?: $paraEmail);

            foreach ($adjuntos as $adj) {
                $ruta = $adj['ruta'] ?? '';
                if ($ruta && file_exists($ruta)) {
                    $mail->addAttachment($ruta, $adj['nombre'] ?? basename($ruta));
                }
            }

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $html;
            $mail->AltBody = $textoPlano ?: strip_tags($html);

            return $mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }
}
