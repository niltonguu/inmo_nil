<?php
// app/Controllers/ClientesController.php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Models/ClientesModel.php';

class ClientesController extends Controller
{
    /** @var ClientesModel */
    private $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?c=auth&a=loginForm');
            exit;
        }

        $this->model = new ClientesModel();
    }

    /* =======================================================
     * VISTA PRINCIPAL
     * =======================================================
     */

    public function index()
    {
        // Renderiza app/Views/clientes/index.php dentro de main.php
        $this->loadView('clientes/index');
    }

    /* =======================================================
     * CLIENTES - JSON
     * =======================================================
     */

    // GET: index.php?c=clientes&a=list
    public function list()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Tomamos usuario logueado
        $user = $_SESSION['user'] ?? null;
        $id   = (int)($user['id'] ?? 0);
        $role = $user['role'] ?? 'visita';

        try {
            // Filtrado por rol/usuario
            $rows = $this->model->getAllClientes($id, $role);

            echo json_encode(['data' => $rows]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'data'  => [],
                'error' => true,
                'msg'   => 'Error al obtener clientes'
            ]);
        }
        exit;
    }


    // GET: index.php?c=clientes&a=get&id=1
    public function get()
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(null);
            exit;
        }

        try {
            $row = $this->model->getClienteById($id);
            echo json_encode($row ?: null);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(null);
        }
        exit;
    }

    // POST: index.php?c=clientes&a=save
    public function save()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = $_POST;
        $currentUserId = (int)($_SESSION['user']['id'] ?? 0);

        try {
            $result = $this->model->saveCliente($data, $currentUserId);

            if (!isset($result['status'])) {
                $result['status'] = false;
            }
            if (!isset($result['msg'])) {
                $result['msg'] = $result['status']
                    ? 'Cliente guardado correctamente'
                    : 'No se pudo guardar el cliente';
            }

            echo json_encode($result);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status' => false,
                'msg'    => 'Error al guardar el cliente'
            ]);
        }
        exit;
    }

    // POST: index.php?c=clientes&a=delete
    public function delete()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => false, 'msg' => 'ID inválido']);
            exit;
        }

        try {
            $ok = $this->model->deleteCliente($id);
            echo json_encode([
                'status' => $ok,
                'msg'    => $ok ? 'Cliente eliminado' : 'No se pudo eliminar el cliente'
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status' => false,
                'msg'    => 'Error al eliminar el cliente'
            ]);
        }
        exit;
    }

    /* =======================================================
     * COPROPIETARIOS - JSON
     * =======================================================
     */

    // GET: index.php?c=clientes&a=coprop_list&id_cliente=1
    public function coprop_list()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idCliente = (int)($_GET['id_cliente'] ?? 0);
        if ($idCliente <= 0) {
            echo json_encode([]);
            exit;
        }

        try {
            $rows = $this->model->getCopropietariosByCliente($idCliente);
            echo json_encode($rows ?: []);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([]);
        }
        exit;
    }

    // GET: index.php?c=clientes&a=coprop_get&id=1
    public function coprop_get()
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(null);
            exit;
        }

        try {
            $row = $this->model->getCopropietarioById($id);
            echo json_encode($row ?: null);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(null);
        }
        exit;
    }

    // POST: index.php?c=clientes&a=coprop_save
    public function coprop_save()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = $_POST;

        try {
            $result = $this->model->saveCopropietario($data);

            if (!isset($result['status'])) {
                $result['status'] = false;
            }
            if (!isset($result['msg'])) {
                $result['msg'] = $result['status']
                    ? 'Copropietario guardado correctamente'
                    : 'No se pudo guardar el copropietario';
            }

            echo json_encode($result);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status' => false,
                'msg'    => 'Error al guardar el copropietario'
            ]);
        }
        exit;
    }

    // POST: index.php?c=clientes&a=coprop_delete
    public function coprop_delete()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => false, 'msg' => 'ID inválido']);
            exit;
        }

        try {
            $ok = $this->model->deleteCopropietario($id);
            echo json_encode([
                'status' => $ok,
                'msg'    => $ok ? 'Copropietario eliminado' : 'No se pudo eliminar el copropietario'
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status' => false,
                'msg'    => 'Error al eliminar el copropietario'
            ]);
        }
        exit;
    }
}
