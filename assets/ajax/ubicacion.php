<?php
// Desde /assets/ajax subimos dos niveles hasta la raíz del proyecto local
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__, 2));   // raíz de simac_webservice
if (!defined('APP_PATH'))  define('APP_PATH', BASE_PATH . '/app');

require_once BASE_PATH . '/config/config.php';
require_once APP_PATH . '/Core/Database.php';

use App\Core\Database;

$pdo    = Database::getConnection();
$action = $_GET['action'] ?? '';

header('Content-Type: text/html; charset=utf-8');

switch ($action) {
    case 'estados':
        $pais = $_GET['pais'] ?? '';
        
        // Estados de Venezuela (la tabla no tiene nivel 2, solo nivel 3 y 4)
        $estados = [
            ['codigo' => '001', 'nombre' => 'Amazonas'],
            ['codigo' => '002', 'nombre' => 'Anzoátegui'],
            ['codigo' => '003', 'nombre' => 'Apure'],
            ['codigo' => '004', 'nombre' => 'Aragua'],
            ['codigo' => '005', 'nombre' => 'Barinas'],
            ['codigo' => '006', 'nombre' => 'Bolívar'],
            ['codigo' => '007', 'nombre' => 'Carabobo'],
            ['codigo' => '008', 'nombre' => 'Cojedes'],
            ['codigo' => '009', 'nombre' => 'Delta Amacuro'],
            ['codigo' => '010', 'nombre' => 'Falcón'],
            ['codigo' => '011', 'nombre' => 'Guárico'],
            ['codigo' => '012', 'nombre' => 'Lara'],
            ['codigo' => '013', 'nombre' => 'Mérida'],
            ['codigo' => '014', 'nombre' => 'Miranda'],
            ['codigo' => '015', 'nombre' => 'Monagas'],
            ['codigo' => '016', 'nombre' => 'Nueva Esparta'],
            ['codigo' => '017', 'nombre' => 'Portuguesa'],
            ['codigo' => '018', 'nombre' => 'Sucre'],
            ['codigo' => '019', 'nombre' => 'Táchira'],
            ['codigo' => '020', 'nombre' => 'Trujillo'],
            ['codigo' => '021', 'nombre' => 'La Guaira'],
            ['codigo' => '022', 'nombre' => 'Yaracuy'],
            ['codigo' => '023', 'nombre' => 'Zulia'],
            ['codigo' => '024', 'nombre' => 'Distrito Capital'],
        ];
        
        echo '<option value="">Selecciona estado...</option>';
        foreach ($estados as $estado) {
            echo '<option value="'.htmlspecialchars($estado['codigo']).'">'
               . htmlspecialchars($estado['nombre'])
               . '</option>';
        }
        break;

    case 'municipios':
        $pais   = $_GET['pais'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $stmt = $pdo->prepare("SELECT DISTINCT codigo_municipio, nombre 
                               FROM pais_edo_ciudad 
                               WHERE codigo_pais = ? AND codigo_estado = ? AND codigo_municipio IS NOT NULL AND (codigo_parroquia IS NULL OR codigo_parroquia = '')
                               ORDER BY nombre");
        $stmt->execute([$pais, $estado]);
        echo '<option value="">Selecciona municipio...</option>';
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            echo '<option value="'.htmlspecialchars($row['codigo_municipio']).'">'
               . htmlspecialchars($row['nombre'])
               . '</option>';
        }
        break;

    case 'parroquias':
        $pais      = $_GET['pais'] ?? '';
        $estado    = $_GET['estado'] ?? '';
        $municipio = $_GET['municipio'] ?? '';
        $stmt = $pdo->prepare("SELECT DISTINCT codigo_parroquia, nombre 
                               FROM pais_edo_ciudad 
                               WHERE codigo_pais = ? AND codigo_estado = ? 
                                 AND codigo_municipio = ? AND codigo_parroquia IS NOT NULL AND codigo_parroquia != ''
                               ORDER BY nombre");
        $stmt->execute([$pais, $estado, $municipio]);
        echo '<option value="">Selecciona parroquia...</option>';
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            echo '<option value="'.htmlspecialchars($row['codigo_parroquia']).'">'
               . htmlspecialchars($row['nombre'])
               . '</option>';
        }
        break;
	
	case 'monedas':
		header('Content-Type: application/json; charset=utf-8');
		$pais      = $_GET['pais'] ?? '';
		$estado    = $_GET['estado'] ?? '';
		$municipio = $_GET['municipio'] ?? '';
		
		// Obtener el país para determinar moneda por defecto
		$stmt = $pdo->prepare("
			SELECT moneda_codigo, moneda_nombre, moneda_simbolo
			FROM pais_edo_ciudad
			WHERE codigo_pais = ? AND codigo_estado = ? AND codigo_municipio = ?
			LIMIT 1
		");
		$stmt->execute([$pais, $estado, $municipio]);
		$ubicacion = $stmt->fetch(PDO::FETCH_ASSOC);
		
		// Obtener todas las monedas activas
$stmt = $pdo->query("
			SELECT id, codigo, nombre, simbolo, factor_conversion
			FROM monedas
			WHERE activa = TRUE
			ORDER BY es_moneda_base DESC, nombre ASC
		");
		$monedas = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		echo json_encode([
			'monedas_disponibles' => $monedas,
			'moneda_default'      => $ubicacion['moneda_codigo'] ?? 'USD'
		]);
		break;


    case 'info':
        header('Content-Type: application/json; charset=utf-8');
        $pais      = $_GET['pais'] ?? '';
        $estado    = $_GET['estado'] ?? '';
        $municipio = $_GET['municipio'] ?? '';
        $stmt = $pdo->prepare("SELECT codigo_postal, moneda_codigo, moneda_nombre, moneda_simbolo
                               FROM pais_edo_ciudad
                               WHERE codigo_pais = ? AND codigo_estado = ? 
                                 AND codigo_municipio = ?
                               ORDER BY nivel DESC
                               LIMIT 1");
        $stmt->execute([$pais, $estado, $municipio]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        echo json_encode([
            'codigo_postal'  => $row['codigo_postal']  ?? null,
            'moneda_codigo'  => $row['moneda_codigo']  ?? null,
            'moneda'         => isset($row['moneda_nombre'], $row['moneda_simbolo'])
                                ? $row['moneda_nombre'].' ('.$row['moneda_simbolo'].')'
                                : null,
        ]);
        break;

    default:
        http_response_code(400);
        echo 'Acción inválida';
}

