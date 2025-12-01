<?php
// app/Controllers/LotesController.php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Models/LotesModel.php';
require_once __DIR__ . '/../Models/FactoresModel.php';

class LotesController extends Controller
{
    private LotesModel $model;
    private FactoresModel $factoresModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?c=auth&a=loginForm');
            exit;
        }

        $this->model         = new LotesModel();
        $this->factoresModel = new FactoresModel();
    }

    // =========================================================
    // HELPERS
    // =========================================================

    private function currentUserId(): int
    {
        return (int)($_SESSION['user']['id'] ?? 0);
    }

    private function currentUserRole(): string
    {
        return (string)($_SESSION['user']['role'] ?? 'user');
    }

    private function isAdmin(): bool
    {
        return $this->currentUserRole() === 'admin';
    }

    private function jsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'Método no permitido'
            ], 405);
        }
    }

    private function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'No autorizado'
            ], 403);
        }
    }

    // =========================================================
    // VISTAS
    // =========================================================

    public function index()
    {
        if ($this->isAdmin()) {
            $this->loadView('lotes/index_admin');
        } else {
            $this->loadView('lotes/index_usuario');
        }
    }

    // =========================================================
    // LISTADOS
    // =========================================================

    public function list_admin()
    {
        if (!$this->isAdmin()) {
            $this->jsonResponse(['data' => []]);
        }

        $filtros = [
            'id_proyecto' => isset($_GET['id_proyecto']) && $_GET['id_proyecto'] !== '' ? (int)$_GET['id_proyecto'] : null,
            'id_etapa'    => isset($_GET['id_etapa']) && $_GET['id_etapa'] !== '' ? (int)$_GET['id_etapa'] : null,
            'id_manzana'  => isset($_GET['id_manzana']) && $_GET['id_manzana'] !== '' ? (int)$_GET['id_manzana'] : null,
            'estado_lote' => isset($_GET['estado_lote']) && $_GET['estado_lote'] !== '' ? $_GET['estado_lote'] : null,
        ];

        $rows = $this->model->getLotesAdmin($filtros);
        $this->jsonResponse(['data' => $rows]);
    }

    public function list_usuario()
    {
        $filtros = [
            'id_proyecto' => isset($_GET['id_proyecto']) && $_GET['id_proyecto'] !== '' ? (int)$_GET['id_proyecto'] : null,
            'id_etapa'    => isset($_GET['id_etapa']) && $_GET['id_etapa'] !== '' ? (int)$_GET['id_etapa'] : null,
            'id_manzana'  => isset($_GET['id_manzana']) && $_GET['id_manzana'] !== '' ? (int)$_GET['id_manzana'] : null,
            'estado_lote' => isset($_GET['estado_lote']) && $_GET['estado_lote'] !== '' ? $_GET['estado_lote'] : null,
        ];

        $rows = $this->model->getLotesUsuario($filtros);
        $this->jsonResponse(['data' => $rows]);
    }

    public function get()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->jsonResponse(null);
        }

        $row = $this->model->getLoteById($id);
        $this->jsonResponse($row);
    }

    // =========================================================
    // CRUD LOTE (ADMIN)
    // =========================================================

    public function save()
    {
        $this->requirePost();
        $this->requireAdmin();

        $data = $_POST;

        $id_proyecto = (int)($data['id_proyecto'] ?? 0);
        $id_etapa    = (int)($data['id_etapa'] ?? 0);
        $id_manzana  = (int)($data['id_manzana'] ?? 0);
        $numero      = (int)($data['numero'] ?? 0);
        $area_m2     = (float)($data['area_m2'] ?? 0);

        if ($id_proyecto <= 0 || $id_etapa <= 0 || $id_manzana <= 0 || $numero <= 0 || $area_m2 <= 0) {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'Proyecto, etapa, manzana, número y área son obligatorios'
            ]);
        }

        $ok = $this->model->saveLote($data, $this->currentUserId());

        $this->jsonResponse([
            'status' => $ok,
            'msg'    => $ok ? 'Lote guardado' : 'No se pudo guardar el lote'
        ]);
    }

    public function delete()
    {
        $this->requirePost();
        $this->requireAdmin();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'ID inválido'
            ]);
        }

        $ok = $this->model->deleteLote($id);

        $this->jsonResponse([
            'status' => $ok,
            'msg'    => $ok ? 'Lote eliminado' : 'No se pudo eliminar el lote (quizás tiene historial, vértices o factores asociados)'
        ]);
    }

    // =========================================================
    // FACTORES DEL PROYECTO
    // =========================================================

    public function factores_list()
    {
        $id_proyecto = (int)($_GET['id_proyecto'] ?? 0);
        if ($id_proyecto <= 0) {
            $this->jsonResponse([]);
        }

        $rows = $this->factoresModel->getFactoresByProyecto($id_proyecto);
        $this->jsonResponse($rows);
    }

    public function factores_save()
    {
        $this->requirePost();
        $this->requireAdmin();

        $data = $_POST;

        $id_proyecto = (int)($data['id_proyecto'] ?? 0);
        $nombre      = trim($data['nombre'] ?? '');
        $cat_factor  = trim($data['cat_factor'] ?? '');
        $valor_pct   = isset($data['valor_pct']) ? (float)$data['valor_pct'] : null;

        if ($id_proyecto <= 0 || $nombre === '' || $cat_factor === '' || $valor_pct === null) {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'Proyecto, nombre, categoría y valor % son obligatorios'
            ]);
        }

        $ok = $this->factoresModel->saveFactor($data);

        $this->jsonResponse([
            'status' => $ok,
            'msg'    => $ok ? 'Factor guardado' : 'No se pudo guardar el factor'
        ]);
    }

    public function factores_delete()
    {
        $this->requirePost();
        $this->requireAdmin();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'ID inválido'
            ]);
        }

        $ok = $this->factoresModel->deleteFactor($id);

        $this->jsonResponse([
            'status' => $ok,
            'msg'    => $ok ? 'Factor eliminado' : 'No se pudo eliminar el factor'
        ]);
    }

    // =========================================================
    // FACTORES APLICADOS AL LOTE
    // =========================================================

    public function lote_factores_get()
    {
        $id_lote = (int)($_GET['id_lote'] ?? 0);
        if ($id_lote <= 0) {
            $this->jsonResponse([]);
        }

        $ids = $this->factoresModel->getFactorIdsByLote($id_lote);
        $out = [];
        foreach ($ids as $id_factor) {
            $out[] = ['id_factor' => $id_factor];
        }

        $this->jsonResponse($out);
    }

    public function lote_factores_save()
    {
        $this->requirePost();
        $this->requireAdmin();

        $id_lote  = (int)($_POST['id_lote'] ?? 0);
        $factores = isset($_POST['factores']) ? (array)$_POST['factores'] : [];

        if ($id_lote <= 0) {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'Lote inválido'
            ]);
        }

        $ids = [];
        foreach ($factores as $f) {
            $f = (int)$f;
            if ($f > 0) $ids[] = $f;
        }

        $ok = $this->factoresModel->saveLoteFactores($id_lote, $ids, $this->currentUserId());

        if ($ok) {
            $this->model->recalcularPrecioLote($id_lote);
        }

        $this->jsonResponse([
            'status' => $ok,
            'msg'    => $ok ? 'Factores del lote actualizados' : 'No se pudieron actualizar los factores'
        ]);
    }

    // =========================================================
    // VÉRTICES
    // =========================================================

    public function vertices_list()
    {
        $id_lote = (int)($_GET['id_lote'] ?? 0);
        if ($id_lote <= 0) {
            $this->jsonResponse([]);
        }

        $rows = $this->model->getVerticesByLote($id_lote);
        $this->jsonResponse($rows);
    }

    public function vertices_get()
    {
        $this->vertices_list();
    }

    public function vertices_save()
    {
        $this->requirePost();
        $this->requireAdmin();

        $id_lote = (int)($_POST['id_lote'] ?? 0);
        if ($id_lote <= 0) {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'Lote inválido'
            ]);
        }

        $vertices = [];

        if (isset($_POST['vertices'])) {
            $raw = $_POST['vertices'];
            if (is_string($raw)) {
                $vertices = json_decode($raw, true);
            } else {
                $vertices = $raw;
            }

            if (!is_array($vertices)) {
                $this->jsonResponse([
                    'status' => false,
                    'msg'    => 'Formato de vértices inválido'
                ]);
            }
        } elseif (isset($_POST['lat'], $_POST['lng'])) {
            $ordenArr = isset($_POST['orden']) ? (array)$_POST['orden'] : [];
            $latArr   = (array)$_POST['lat'];
            $lngArr   = (array)$_POST['lng'];

            $count = min(count($latArr), count($lngArr));
            for ($i = 0; $i < $count; $i++) {
                $lat = trim($latArr[$i]);
                $lng = trim($lngArr[$i]);
                if ($lat === '' || $lng === '') continue;

                $orden = isset($ordenArr[$i]) ? (int)$ordenArr[$i] : ($i + 1);
                $vertices[] = [
                    'orden' => $orden,
                    'lat'   => (float)$lat,
                    'lng'   => (float)$lng,
                ];
            }
        } else {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'No se recibieron vértices'
            ]);
        }

        if (count($vertices) < 3) {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'Debes definir al menos 3 vértices válidos (en sentido horario)'
            ]);
        }

        $ok = $this->model->saveVertices($id_lote, $vertices);

        $this->jsonResponse([
            'status' => $ok,
            'msg'    => $ok ? 'Vértices guardados' : 'No se pudieron guardar los vértices'
        ]);
    }

    public function vertices_delete()
    {
        $this->requirePost();
        $this->requireAdmin();

        $id_lote = (int)($_POST['id_lote'] ?? 0);
        if ($id_lote <= 0) {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'Lote inválido'
            ]);
        }

        $ok = $this->model->deleteVerticesByLote($id_lote);

        $this->jsonResponse([
            'status' => $ok,
            'msg'    => $ok ? 'Vértices eliminados' : 'No se pudieron eliminar los vértices'
        ]);
    }

    // =========================================================
    // HISTORIAL
    // =========================================================

    public function historial_list()
    {
        $id_lote = (int)($_GET['id_lote'] ?? 0);
        if ($id_lote <= 0) {
            $this->jsonResponse([]);
        }

        $rows = $this->model->getHistorialByLote($id_lote, $this->currentUserRole());
        $this->jsonResponse($rows);
    }

    // =========================================================
    // CAMBIO DE ESTADO (ADMIN + VENDEDOR)
    // =========================================================

    public function cambiar_estado()
    {
        $this->requirePost();

        $id_lote      = (int)($_POST['id_lote'] ?? 0);
        $estado_nuevo = trim($_POST['estado_nuevo'] ?? '');
        $id_cliente   = isset($_POST['id_cliente']) && $_POST['id_cliente'] !== '' ? (int)$_POST['id_cliente'] : null;
        $motivo       = trim($_POST['motivo'] ?? '');

        if ($id_lote <= 0 || $estado_nuevo === '') {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'Datos inválidos para cambio de estado'
            ]);
        }

        $lote = $this->model->getLoteById($id_lote);
        if (!$lote) {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'Lote no encontrado'
            ]);
        }

        $estado_actual = $lote['estado_lote'] ?? 'DISPONIBLE';
        $rol           = $this->currentUserRole();
        $id_usuario    = $this->currentUserId();

        // Reglas para vendedores (user)
        if ($rol !== 'admin') {
            $permitidas = [
                'DISPONIBLE' => ['RESERVADO'],
                'RESERVADO'  => ['SEPARADO'],
                'SEPARADO'   => ['VENDIDO'],
            ];

            if (!isset($permitidas[$estado_actual]) ||
                !in_array($estado_nuevo, $permitidas[$estado_actual], true)) {

                $this->jsonResponse([
                    'status' => false,
                    'msg'    => 'Cambio de estado no permitido para este usuario'
                ]);
            }
        }

        // Regla general: si nuevo estado != DISPONIBLE, debe tener cliente
        if ($estado_nuevo !== 'DISPONIBLE' && !$id_cliente) {
            $this->jsonResponse([
                'status' => false,
                'msg'    => 'Debe seleccionar un cliente para este cambio de estado'
            ]);
        }

        $ok = $this->model->cambiarEstadoLote(
            $id_lote,
            $estado_actual,
            $estado_nuevo,
            $id_cliente,
            $id_usuario,
            $motivo
        );

        $this->jsonResponse([
            'status' => $ok,
            'msg'    => $ok ? 'Estado del lote actualizado' : 'No se pudo cambiar el estado del lote'
        ]);
    }

    // =========================================================
    // CREACIÓN MASIVA
    // =========================================================

    // POST: index.php?c=lotes&a=crear_masivos
    public function crear_masivos()
    {
        $this->requirePost();
        $this->requireAdmin();

        $idProyecto = (int)($_POST['id_proyecto'] ?? 0);
        $idEtapa    = isset($_POST['id_etapa']) && $_POST['id_etapa'] !== '' ? (int)$_POST['id_etapa'] : null;
        $idManzana  = (int)($_POST['id_manzana'] ?? 0);

        $numDesde   = (int)($_POST['numero_desde'] ?? 0);
        $numHasta   = (int)($_POST['numero_hasta'] ?? 0);

        $areaDefault     = isset($_POST['area_m2_default']) && $_POST['area_m2_default'] !== ''
                            ? (float)$_POST['area_m2_default'] : null;
        $precioM2Default = isset($_POST['precio_m2_default']) && $_POST['precio_m2_default'] !== ''
                            ? (float)$_POST['precio_m2_default'] : null;

        $result = $this->model->crearLotesMasivos(
            $idProyecto,
            $idEtapa,
            $idManzana,
            $numDesde,
            $numHasta,
            $areaDefault,
            $precioM2Default,
            $this->currentUserId()
        );

        $statusCode = $result['status'] ? 200 : 400;
        $this->jsonResponse($result, $statusCode);
    }
}
