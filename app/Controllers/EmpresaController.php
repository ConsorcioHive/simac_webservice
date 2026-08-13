<?php
// app/Controllers/EmpresaController.php
namespace App\Controllers;

use App\Models\Empresa;

class EmpresaController
{
    private $model;

    public function __construct()
    {
        $this->model = new Empresa();
    }

    public function obtener()
    {
        return $this->model->obtenerUnica();
    }

    public function guardar($datos)
    {
        $empresa = $this->obtener();
        if (!$empresa) {
            return ['ok' => false, 'msg' => 'No hay empresa configurada.'];
        }
        $this->model->actualizar($empresa['id'], $datos);
        return ['ok' => true];
    }
}
