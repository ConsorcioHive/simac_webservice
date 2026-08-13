<?php
// app/Services/FileDropService.php
namespace App\Services;

// Contenedor de transición de archivos: carpetas donde se colocan archivos
// para moverlos hacia/desde la nube (inbox = entrada, outbox = salida).
class FileDropService
{
    private $inbox;
    private $outbox;

    public function __construct()
    {
        $this->inbox  = BASE_PATH . '/storage/inbox/';
        $this->outbox = BASE_PATH . '/storage/outbox/';
    }

    public function listarInbox()
    {
        return $this->ls($this->inbox);
    }

    public function listarOutbox()
    {
        return $this->ls($this->outbox);
    }

    // Mueve un archivo de inbox a outbox (listo para subir a la nube).
    public function moverAOutbox($nombre)
    {
        if (!file_exists($this->inbox . $nombre)) {
            return false;
        }
        return rename($this->inbox . $nombre, $this->outbox . $nombre);
    }

    // Coloca un archivo directamente en outbox (salida a la nube).
    public function colocarEnOutbox($contenido, $nombre)
    {
        if (!is_dir($this->outbox)) {
            mkdir($this->outbox, 0755, true);
        }
        return file_put_contents($this->outbox . $nombre, $contenido);
    }

    private function ls($dir)
    {
        if (!is_dir($dir)) {
            return [];
        }
        $out = [];
        foreach (array_diff(scandir($dir), ['.', '..']) as $f) {
            if (is_file($dir . $f)) {
                $out[] = $f;
            }
        }
        return $out;
    }
}
