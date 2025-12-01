<?php
// app/Controllers/PersonasController.php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Models/PersonasModel.php';

class PersonasController extends Controller
{
    private $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?c=auth&a=loginForm');
            exit;
        }
        $this->model = new PersonasModel();
    }

    public function index()
    {
        $this->loadView('personas/index');
    }

    // JSON para DataTables
    public function list()
    {
    header('Content-Type: application/json; charset=utf-8');

    $user  = $_SESSION['user'] ?? null;
    $id    = (int)($user['id'] ?? 0);
    $role  = $user['role'] ?? 'visita';

    $filtro = $_GET['etiqueta'] ?? '';

    if ($role === 'admin') {
        if ($filtro) {
            $rows = $this->model->getAllByEtiqueta($filtro);
        } else {
            $rows = $this->model->getAll();
        }
    } else {
        if ($filtro) {
            $rows = $this->model->getAllByEtiquetaAndUser($filtro, $id);
        } else {
            $rows = $this->model->getAllByAssigned($id);
        }
    }

    echo json_encode(['data' => $rows]);
    exit;
    }


    public function get()
    {
        $id = intval($_GET['id'] ?? 0);
        header('Content-Type: application/json; charset=utf-8');
        if (!$id) { echo json_encode(null); exit; }
        echo json_encode($this->model->getById($id));
        exit;
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status'=>false,'msg'=>'Método no permitido']);
            exit;
        }
        $id_user = (int)($_SESSION['user']['id'] ?? 0);
        $ok = $this->model->save($_POST, $id_user);
        echo json_encode(['status' => $ok]);
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status'=>false]);
            exit;
        }
        $id = intval($_POST['id'] ?? 0);
        $ok = $this->model->delete($id);
        echo json_encode(['status' => $ok]);
        exit;
    }

    // NOTAS
    public function notes()
    {
        $id = intval($_GET['id'] ?? 0);
        header('Content-Type: application/json; charset=utf-8');
        if (!$id) { echo json_encode([]); exit; }
        echo json_encode($this->model->getNotes($id, 200));
        exit;
    }

    public function save_note()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok'=>false]);
            exit;
        }
        $id = intval($_POST['id_persona'] ?? 0);
        $nota = trim($_POST['nota'] ?? '');
        if (!$id || $nota === '') {
            echo json_encode(['ok'=>false,'msg'=>'Datos incompletos']);
            exit;
        }
        $ok = $this->model->insertNote($id, $nota);
        echo json_encode(['ok'=>$ok]);
        exit;
    }

    // Exportar contactos a CSV para Google
    public function export_google()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?c=auth&a=loginForm');
            exit;
        }

        $rows = $this->model->getAll();
        $filename = 'personas_google_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        $header = [
            'Name Prefix','First Name','Middle Name','Last Name','Name Suffix',
            'Phonetic First Name','Phonetic Middle Name','Phonetic Last Name',
            'Nickname','File As',
            'E-mail 1 - Label','E-mail 1 - Value',
            'Phone 1 - Label','Phone 1 - Value',
            'Address 1 - Label','Address 1 - Country','Address 1 - Street',
            'Address 1 - Extended Address','Address 1 - City','Address 1 - Region',
            'Address 1 - Postal Code','Address 1 - PO Box',
            'Organization Name','Organization Title','Organization Department',
            'Birthday','Event 1 - Label','Event 1 - Value',
            'Relation 1 - Label','Relation 1 - Value',
            'Website 1 - Label','Website 1 - Value',
            'Custom Field 1 - Label','Custom Field 1 - Value',
            'Notes','Labels'
        ];
        fputcsv($output, $header);

        foreach ($rows as $r) {
            $firstName = $r['nombres'] ?? '';
            $lastName  = $r['apellidos'] ?? '';
            $email     = $r['email'] ?? '';
            $phoneRaw  = $r['telefono'] ?? '';
            $phone     = trim($phoneRaw);

            $city = '';
            $region = '';
            if (!empty($r['ubigeo_descripcion'])) {
                $parts = explode(' - ', $r['ubigeo_descripcion']);
                if (count($parts) >= 1) $city   = trim($parts[0]);
                if (count($parts) >= 3) $region = trim($parts[2]);
            }

            $numeroDocumento = $r['numero_documento'] ?? '';
            $estado = $r['estado'] ?? '';

            $nota = 'Estado: ' . $estado;
            if (!empty($r['ubigeo_descripcion'])) {
                $nota .= ' | Ubigeo: ' . $r['ubigeo_descripcion'];
            }
            if (!empty($r['ultima_nota'])) {
                $nota .= ' | Última nota: ' . $r['ultima_nota'];
            }

            $fileAs = trim($lastName . ', ' . $firstName, ' ,');

            $row = [
                '',
                $firstName,
                '',
                $lastName,
                '',
                '','','',
                '',
                $fileAs,
                'Home',
                $email,
                'Mobile',
                $phone,
                'Home',
                'Peru',
                '',
                '',
                $city,
                $region,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'DNI',
                $numeroDocumento,
                $nota,
                'PUBLICIDAD APP'
            ];
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    // Asignar persona a usuario (solo admin)
    public function assign()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $role = $_SESSION['user']['role'] ?? 'visita';
        if ($role !== 'admin') {
            http_response_code(403);
            echo json_encode(['status' => false, 'msg' => 'No autorizado']);
            exit;
        }

        $id_persona = (int)($_POST['id'] ?? 0);
        $id_user    = (isset($_POST['asignado']) && $_POST['asignado'] !== '')
                        ? (int)$_POST['asignado'] : null;

        if (!$id_persona) {
            echo json_encode(['status' => false, 'msg' => 'ID inválido']);
            exit;
        }

        $ok = $this->model->updateAssigned($id_persona, $id_user);
        echo json_encode(['status' => $ok]);
        exit;
    }

    // Registrar click en teléfono / WhatsApp
    public function phone_click()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
        if (!isset($_SESSION['user'])) exit;

        $id_user    = (int)$_SESSION['user']['id'];
        $id_persona = (int)($_POST['id'] ?? 0);

        if (!$id_persona) exit;

        $this->model->logPhoneClick($id_user, $id_persona);
        exit;
    }

    // Cambiar etiqueta
    public function label()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['status' => false, 'msg' => 'No autenticado']);
            exit;
        }

        $role = $_SESSION['user']['role'] ?? 'visita';
        if (!in_array($role, ['admin','usuario'], true)) {
            http_response_code(403);
            echo json_encode(['status' => false, 'msg' => 'No autorizado']);
            exit;
        }

        $id_persona = (int)($_POST['id'] ?? 0);
        $etiqueta   = $_POST['etiqueta'] ?? 'NULL';

        if (!$id_persona) {
            echo json_encode(['status' => false, 'msg' => 'ID inválido']);
            exit;
        }

        $valid = [
            'NULL',
            'SIN_RESPUESTA',
            'CONTACTADO',
            'PROSPECTO',
            'SEPARADO',
            'VENDIDO',
            'PROBLEMAS'
        ];
        if (!in_array($etiqueta, $valid, true)) {
            echo json_encode(['status' => false, 'msg' => 'Etiqueta inválida']);
            exit;
        }

        $ok = $this->model->updateEtiqueta($id_persona, $etiqueta);
        echo json_encode(['status' => $ok]);
        exit;
    }
}
