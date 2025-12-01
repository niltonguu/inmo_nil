<?php
// app/Controllers/ApiController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/Database.php';

class ApiController
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    private function jsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    private function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->jsonResponse(['error' => 'No autenticado'], 401);
        }
    }

    private function currentUserId(): int
    {
        return (int)($_SESSION['user']['id'] ?? 0);
    }

    private function currentUserRole(): string
    {
        return (string)($_SESSION['user']['role'] ?? 'user');
    }

    // =========================================================
    // EXISTENTES
    // =========================================================

    // Lista de tipos de persona
    public function tipo_persona_list()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $sql = "SELECT id, nombre FROM tipo_persona ORDER BY id";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($rows);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener tipos de persona']);
        }
        exit;
    }

    // Lista de tipos de documentos de identidad
    public function tipo_documentos_list()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $sql = "SELECT id, nombre, codigo 
                    FROM tipo_documentos_identidad 
                    WHERE estado = 'ACTIVO'
                    ORDER BY id";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($rows);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener tipos de documentos']);
        }
        exit;
    }

    // Lista de ubigeos
    public function ubigeos_list()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $sql = "SELECT id, descripcion 
                    FROM ubigeos 
                    ORDER BY descripcion 
                    LIMIT 50000";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($rows);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener ubigeos']);
        }
        exit;
    }

    // Lista de usuarios (para asignar personas)
    public function users_list()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $sql = "SELECT id, fullname, role 
                    FROM users 
                    ORDER BY fullname";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($rows);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener usuarios']);
        }
        exit;
    }

    // Ping para actualizar last_active (usuarios online)
    public function ping()
    {
        if (!isset($_SESSION['user'])) exit;

        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET last_active = NOW()
                WHERE id = :id
            ");
            $stmt->execute([':id' => (int)$_SESSION['user']['id']]);
        } catch (Exception $e) {
            // silencioso
        }
        exit;
    }

    // =========================================================
    // NUEVO: LISTAS PARA MÓDULO LOTES (SELECTS)
    // =========================================================

    // Proyectos activos para selects
    public function proyectos_list()
    {
        $this->requireLogin();

        try {
            $sql = "SELECT 
                        id,
                        nombre,
                        codigo,
                        precio_m2_base,
                        factor_min_pct,
                        factor_max_pct
                    FROM proyectos
                    WHERE estado = 'ACTIVO'
                    ORDER BY nombre";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse($rows);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Error al obtener proyectos'], 500);
        }
    }

    // Etapas por proyecto (solo habilitadas para venta)
    public function etapas_list()
    {
        $this->requireLogin();

        $idProyecto = isset($_GET['id_proyecto']) ? (int)$_GET['id_proyecto'] : 0;
        if ($idProyecto <= 0) {
            $this->jsonResponse([]);
        }

        try {
            $sql = "SELECT 
                        id,
                        nombre,
                        numero,
                        habilitada_venta
                    FROM etapas
                    WHERE id_proyecto = :id_proyecto
                    ORDER BY numero, nombre";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_proyecto' => $idProyecto]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse($rows);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Error al obtener etapas'], 500);
        }
    }

    // Manzanas por etapa
    public function manzanas_list()
    {
        $this->requireLogin();

        $idEtapa = isset($_GET['id_etapa']) ? (int)$_GET['id_etapa'] : 0;
        if ($idEtapa <= 0) {
            $this->jsonResponse([]);
        }

        try {
            $sql = "SELECT 
                        id,
                        codigo,
                        descripcion
                    FROM manzanas
                    WHERE id_etapa = :id_etapa
                    ORDER BY codigo";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_etapa' => $idEtapa]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse($rows);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Error al obtener manzanas'], 500);
        }
    }

    // Clientes para selects (filtrado por responsable para vendedores)
    public function clientes_select()
    {
        $this->requireLogin();

        $role = $this->currentUserRole();
        $uid  = $this->currentUserId();

        try {
            $params = [];
            $where  = "c.estado = 'ACTIVO' AND c.deleted_at IS NULL";

            if ($role !== 'admin') {
                // Solo clientes donde el usuario es responsable
                $where .= " AND c.id_user_responsable = :uid";
                $params[':uid'] = $uid;
            }

            $sql = "SELECT 
                        c.id,
                        c.tipo_persona,
                        c.numero_documento,
                        c.nombres,
                        c.apellidos,
                        c.razon_social
                    FROM clientes c
                    WHERE $where
                    ORDER BY c.razon_social, c.apellidos, c.nombres, c.numero_documento";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Armamos etiqueta amigable
            $out = [];
            foreach ($rows as $r) {
                $label = '';
                if (!empty($r['razon_social'])) {
                    $label = trim($r['razon_social']) . ' - ' . $r['numero_documento'];
                } else {
                    $nombre = trim(trim($r['nombres'] . ' ' . $r['apellidos']));
                    if ($nombre === '') {
                        $nombre = 'Sin nombre';
                    }
                    $label = $nombre . ' - ' . $r['numero_documento'];
                }

                $out[] = [
                    'id'    => (int)$r['id'],
                    'label' => $label,
                ];
            }

            $this->jsonResponse($out);
        } catch (Exception $e) {
            $this->jsonResponse(['ok' => false, 'msg' => 'Error al listar clientes para select'], 500);
        }
    }
}
