<?php
// app/Services/PdfService.php
namespace App\Services;

// Wrapper sobre Dompdf para generar PDF desde HTML (igual que en SIMAC).
class PdfService
{
    public static function render($html, $nombreArchivo = 'documento.pdf', $descargar = true)
    {
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        if ($descargar) {
            $dompdf->stream($nombreArchivo, ['Attachment' => true]);
            exit;
        }
        return $dompdf->output();
    }

    // Guarda el PDF en disco y devuelve la ruta.
    public static function guardar($html, $rutaDestino)
    {
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($rutaDestino, $dompdf->output());
        return $rutaDestino;
    }
}
