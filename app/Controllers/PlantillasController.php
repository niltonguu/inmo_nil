<?php
// app/Controllers/PlantillasController.php

require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Models/PlantillasModel.php';

class PlantillasController extends Controller
{
    private PlantillasModel $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user'])) {
            header('Location: index.php?c=auth&a=loginForm');
            exit;
        }

        $this->model = new PlantillasModel();
    }

    private function jsonResponse($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function requireAdmin(): void
    {
        $role = (string)($_SESSION['user']['role'] ?? '');
        if ($role !== 'admin') {
            $this->jsonResponse(['status' => false, 'msg' => 'No autorizado'], 403);
        }
    }

    private function normTipoDocumento(string $tipo): string
    {
        $t = strtoupper(trim($tipo));
        $allow = ['RESERVA', 'SEPARACION', 'COMPRAVENTA', 'ANULACION'];
        return in_array($t, $allow, true) ? $t : '';
    }

    private function normTipoPersona(string $tp): string
    {
        $t = strtoupper(trim($tp));
        $allow = ['PN', 'PN_CONYUGE', 'PJ'];
        return in_array($t, $allow, true) ? $t : '';
    }

    /**
     * GET:
     * index.php?c=plantillas&a=resolve&tipo_documento=RESERVA&tipo_persona=PN
     *
     * Responde:
     * {status:true, id: 6, asunto:"...", tipo_documento:"RESERVA", tipo_persona:"PN"}
     */
public function resolve(): void
{
    $this->requireAdmin();

    $tipoDocumento = $this->normTipoDocumento((string)($_GET['tipo_documento'] ?? ''));
    $tipoPersona   = $this->normTipoPersona((string)($_GET['tipo_persona'] ?? ''));

    if ($tipoDocumento === '') {
        $this->jsonResponse([
            'status' => false,
            'msg' => 'Parámetro inválido (tipo_documento)'
        ], 422);
    }

    // ✅ Solo exigir tipo_persona si NO es ANULACION
    if ($tipoDocumento !== 'ANULACION' && $tipoPersona === '') {
        $this->jsonResponse([
            'status' => false,
            'msg' => 'Parámetro inválido (tipo_persona)'
        ], 422);
    }

    // Función para resolver según el model actual
    $resolver = function(string $td, string $tp) {
        if (method_exists($this->model, 'resolveActive')) {
            return $this->model->resolveActive($td, $tp);
        }
        return $this->model->findActiveByTipo($td, $tp);
    };

    $row = null;

    if ($tipoDocumento === 'ANULACION') {
        // 1) intenta con el tipo_persona que venga (si viene)
        if ($tipoPersona !== '') {
            $row = $resolver($tipoDocumento, $tipoPersona);
        }

        // 2) fallback: busca cualquier plantilla activa de ANULACION
        if (!$row) {
            foreach (['PN', 'PN_CONYUGE', 'PJ'] as $tpTry) {
                $row = $resolver($tipoDocumento, $tpTry);
                if ($row) {
                    $tipoPersona = $tpTry; // importante para responder coherente
                    break;
                }
            }
        }
    } else {
        // comportamiento normal
        $row = $resolver($tipoDocumento, $tipoPersona);
    }

    $id = (int)($row['id'] ?? 0);

    if ($id <= 0) {
        $this->jsonResponse([
            'status' => false,
            'msg' => "No hay plantilla ACTIVA para: {$tipoDocumento} / {$tipoPersona}"
        ], 404);
    }

    $this->jsonResponse([
        'status' => true,
        'id' => $id,
        'asunto' => (string)($row['asunto'] ?? ''),
        'tipo_documento' => (string)($row['tipo_documento'] ?? $tipoDocumento),
        'tipo_persona' => (string)($row['tipo_persona'] ?? $tipoPersona),
    ]);
}

}
