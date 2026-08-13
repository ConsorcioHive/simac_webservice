<?php
// app/Controllers/UsuarioController.php
namespace App\Controllers;

use App\Models\Usuario;
use App\Helpers\Session;

class UsuarioController
{
    private $model;

    public function __construct()
    {
        $this->model = new Usuario();
    }

    public function index()
    {
        $empresaId = Session::get('empresa_id');
        return $this->model->listarPorEmpresa($empresaId);
    }

    public function store($datos)
    {
        $datos['empresa_id'] = Session::get('empresa_id');
        if (empty($datos['password'])) {
            return ['ok' => false, 'msg' => 'La contraseña es obligatoria.'];
        }
        $this->model->crear($datos);
        return ['ok' => true];
    }

    public function update($id, $datos)
    {
        $this->model->actualizar($id, $datos);
        return ['ok' => true];
    }

    public function delete($id)
    {
        $this->model->eliminar($id, Session::get('empresa_id'));
        return ['ok' => true];
    }
}
