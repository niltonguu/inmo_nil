<?php
// ContactsController.php - controlador mínimo para lista
require_once __DIR__ . '/../Models/ContactModel.php';

class ContactsController {
    public function list(){
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?c=auth&a=loginForm'); exit;
        }
        // carga vista principal con datatable
        require __DIR__ . '/../Views/main.php';
    }
}
