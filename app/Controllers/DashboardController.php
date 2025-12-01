<?php
// app/Controllers/DashboardController.php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Models/DashboardModel.php';

class DashboardController extends Controller
{
    private $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user'])) {
            header('Location: index.php?c=auth&a=loginForm');
            exit;
        }

        $this->model = new DashboardModel();
    }

    public function index()
    {
        $currentUser   = $_SESSION['user'];
        $summary       = $this->model->getSummary();
        $usersActivity = $this->model->getUsersActivity();

        $this->loadView('dashboard/index', [
            'currentUser'   => $currentUser,
            'summary'       => $summary,
            'usersActivity' => $usersActivity
        ]);
    }
}
