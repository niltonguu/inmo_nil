<?php
// app/Controllers/ProyectosController.php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Models/ProyectosModel.php';

class ProyectosController extends Controller
{
    private $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?c=auth&a=loginForm');
            exit;
        }

        $this->model = new ProyectosModel();
    }

    /* =========================
     * VISTA PRINCIPAL
     * =======================*/

    public function index()
    {
        $this->loadView('proyectos/index');
    }

    /* =========================
     * PROYECTOS - JSON
     * =======================*/

    public function list()
    {
        header('Content-Type: application/json; charset=utf-8');
        $rows = $this->model->getAllProyectos();
        echo json_encode(['data' => $rows]);
        exit;
    }

    public function get()
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(null);
            exit;
        }

        $row = $this->model->getProyectoById($id);
        echo json_encode($row);
        exit;
    }

    public function save()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = $_POST;

        if (empty($data['codigo']) || empty($data['nombre']) || empty($data['id_ubigeo'])) {
            echo json_encode(['status' => false, 'msg' => 'Código, nombre y ubigeo son obligatorios']);
            exit;
        }

        $ok = $this->model->saveProyecto($data);

        echo json_encode([
            'status' => $ok,
            'msg'    => $ok ? 'Proyecto guardado' : 'No se pudo guardar'
        ]);
        exit;
    }

    public function delete()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['status' => false, 'msg' => 'ID inválido']);
            exit;
        }

        $ok = $this->model->deleteProyecto($id);

        echo json_encode([
            'status' => $ok,
            'msg'    => $ok ? 'Proyecto eliminado' : 'No se pudo eliminar'
        ]);
        exit;
    }

    /* =========================
     * ETAPAS
     * =======================*/

    public function etapas_list()
    {
        header('Content-Type: application/json; charset=utf-8');

        $id_proyecto = (int)($_GET['id_proyecto'] ?? 0);
        if (!$id_proyecto) {
            echo json_encode([]);
            exit;
        }

        $rows = $this->model->getEtapasByProyecto($id_proyecto);
        echo json_encode($rows);
        exit;
    }

    public function etapas_save()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = $_POST;
        $id_proyecto = (int)($data['id_proyecto'] ?? 0);

        if (!$id_proyecto || empty($data['nombre'])) {
            echo json_encode(['status' => false, 'msg' => 'Proyecto y nombre son obligatorios']);
            exit;
        }

        $ok = $this->model->saveEtapa($data);

        echo json_encode([
            'status' => $ok,
            'msg'    => $ok ? 'Etapa guardada' : 'No se pudo guardar'
        ]);
        exit;
    }

    public function etapas_delete()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['status' => false, 'msg' => 'ID inválido']);
            exit;
        }

        $ok = $this->model->deleteEtapa($id);

        echo json_encode([
            'status' => $ok,
            'msg'    => $ok ? 'Etapa eliminada' : 'No se pudo eliminar'
        ]);
        exit;
    }

    public function etapas_generate()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $id_proyecto = (int)($_POST['id_proyecto'] ?? 0);
        $cantidad    = (int)($_POST['cantidad'] ?? 0);

        if (!$id_proyecto || $cantidad <= 0) {
            echo json_encode(['status' => false, 'msg' => 'Datos inválidos']);
            exit;
        }

        $ok = $this->model->generateEtapas($id_proyecto, $cantidad);

        echo json_encode([
            'status' => $ok,
            'msg'    => $ok ? 'Etapas generadas' : 'No se pudieron generar'
        ]);
        exit;
    }

    /* =========================
     * MANZANAS
     * =======================*/

    public function manzanas_list()
    {
        header('Content-Type: application/json; charset=utf-8');

        $id_proyecto = (int)($_GET['id_proyecto'] ?? 0);
        $id_etapa    = isset($_GET['id_etapa']) && $_GET['id_etapa'] !== ''
                       ? (int)$_GET['id_etapa']
                       : null;

        if (!$id_proyecto) {
            echo json_encode([]);
            exit;
        }

        $rows = $this->model->getManzanasByProyectoYEtapa($id_proyecto, $id_etapa);
        echo json_encode($rows);
        exit;
    }

    public function manzanas_save()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = $_POST;
        $id_etapa = (int)($data['id_etapa'] ?? 0);

        if (!$id_etapa || empty($data['codigo'])) {
            echo json_encode(['status' => false, 'msg' => 'Etapa y código son obligatorios']);
            exit;
        }

        $ok = $this->model->saveManzana($data);

        echo json_encode([
            'status' => $ok,
            'msg'    => $ok ? 'Manzana guardada' : 'No se pudo guardar'
        ]);
        exit;
    }

    public function manzanas_delete()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['status' => false, 'msg' => 'ID inválido']);
            exit;
        }

        $ok = $this->model->deleteManzana($id);

        echo json_encode([
            'status' => $ok,
            'msg'    => $ok ? 'Manzana eliminada' : 'No se pudo eliminar'
        ]);
        exit;
    }

    public function manzanas_generate()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $id_etapa     = (int)($_POST['id_etapa'] ?? 0);
        $letra_inicio = $_POST['letra_inicio'] ?? '';
        $letra_fin    = $_POST['letra_fin'] ?? '';

        if (!$id_etapa || $letra_inicio === '' || $letra_fin === '') {
            echo json_encode(['status' => false, 'msg' => 'Datos incompletos']);
            exit;
        }

        $ok = $this->model->generateManzanas($id_etapa, $letra_inicio, $letra_fin);

        echo json_encode([
            'status' => $ok,
            'msg'    => $ok ? 'Manzanas generadas' : 'No se pudieron generar'
        ]);
        exit;
    }
}
