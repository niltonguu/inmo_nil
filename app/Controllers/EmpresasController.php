<?php
// app/Controllers/EmpresasController.php

require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Models/EmpresasModel.php';

class EmpresasController extends Controller
{
    private EmpresasModel $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?c=auth&a=loginForm');
            exit;
        }
        $this->model = new EmpresasModel();
    }

    public function index()
    {
        $this->loadView('empresas/index');
    }

    private function json(array $resp, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resp);
    }

    // =========================
    // EMPRESAS
    // =========================

    public function list()
    {
        try {
            $estado = trim($_GET['estado'] ?? '');
            $q = trim($_GET['q'] ?? '');
            $this->json(['ok'=>true, 'data'=>$this->model->listEmpresas($estado, $q)]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error list empresas','err'=>$e->getMessage()], 500);
        }
    }

    public function get()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { $this->json(['ok'=>false,'msg'=>'ID inválido'], 400); return; }

        try {
            $this->json(['ok'=>true, 'data'=>$this->model->getEmpresa($id)]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error get empresa','err'=>$e->getMessage()], 500);
        }
    }

    public function save()
    {
        try {
            $this->json($this->model->saveEmpresa($_POST));
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error save empresa','err'=>$e->getMessage()], 500);
        }
    }

    public function delete()
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { $this->json(['ok'=>false,'msg'=>'ID inválido'], 400); return; }

        try {
            $this->json($this->model->inactivarEmpresa($id));
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error delete empresa','err'=>$e->getMessage()], 500);
        }
    }

    // =========================
    // REPRESENTANTES
    // =========================

    public function reps_list()
    {
        $idEmpresa = (int)($_GET['id_empresa'] ?? 0);
        if ($idEmpresa <= 0) { $this->json(['ok'=>false,'msg'=>'id_empresa inválido'], 400); return; }

        try {
            $this->json(['ok'=>true, 'data'=>$this->model->listRepresentantes($idEmpresa)]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error list representantes','err'=>$e->getMessage()], 500);
        }
    }

    public function rep_get()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { $this->json(['ok'=>false,'msg'=>'ID inválido'], 400); return; }

        try {
            $this->json(['ok'=>true, 'data'=>$this->model->getRepresentante($id)]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error get representante','err'=>$e->getMessage()], 500);
        }
    }

    public function rep_save()
    {
        try {
            $this->json($this->model->saveRepresentante($_POST));
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error save representante','err'=>$e->getMessage()], 500);
        }
    }

    public function rep_delete()
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { $this->json(['ok'=>false,'msg'=>'ID inválido'], 400); return; }

        try {
            $this->json($this->model->inactivarRepresentante($id));
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error delete representante','err'=>$e->getMessage()], 500);
        }
    }

    // =========================
    // PODERES
    // =========================

    public function poderes_list()
    {
        $idRep = (int)($_GET['id_representante'] ?? 0);
        if ($idRep <= 0) { $this->json(['ok'=>false,'msg'=>'id_representante inválido'], 400); return; }

        try {
            $this->json(['ok'=>true, 'data'=>$this->model->listPoderes($idRep)]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error list poderes','err'=>$e->getMessage()], 500);
        }
    }

    public function poder_get()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { $this->json(['ok'=>false,'msg'=>'ID inválido'], 400); return; }

        try {
            $this->json(['ok'=>true, 'data'=>$this->model->getPoder($id)]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error get poder','err'=>$e->getMessage()], 500);
        }
    }

    public function poder_save()
    {
        try {
            $this->json($this->model->savePoder($_POST));
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error save poder','err'=>$e->getMessage()], 500);
        }
    }

    public function poder_activar()
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { $this->json(['ok'=>false,'msg'=>'ID inválido'], 400); return; }

        try {
            $this->json($this->model->activarPoder($id));
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error activar poder','err'=>$e->getMessage()], 500);
        }
    }

    public function poder_vigente()
    {
        $idRep = (int)($_GET['id_representante'] ?? 0);
        if ($idRep <= 0) { $this->json(['ok'=>false,'msg'=>'id_representante inválido'], 400); return; }

        try {
            $this->json(['ok'=>true, 'data'=>$this->model->getPoderVigenteActivo($idRep)]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'msg'=>'Error poder vigente','err'=>$e->getMessage()], 500);
        }
    }

    public function ver()
{
    $idRep = (int)($_GET['id_representante'] ?? 0);
    echo '<pre>';
    print_r($this->model->listEmpresas($idRep));
    echo '</pre>';
    exit;
    //index.php?c=empresas&a=ver&id_representante=1

}



}
