<?php
// router.php - versión simple (usa ?c=...&a=...)
class Router {
    public static function dispatch(){
        $c = $_GET['c'] ?? 'auth';
        $a = $_GET['a'] ?? 'loginForm';

        $controllerFile = __DIR__ . "/app/Controllers/" . ucfirst($c) . "Controller.php";

        if (!file_exists($controllerFile)) {
            http_response_code(404);
            echo "Controlador no encontrado: $controllerFile";
            exit;
        }

        require_once $controllerFile;
        $class = ucfirst($c) . 'Controller';
        if (!class_exists($class)) {
            http_response_code(500);
            echo "Clase controlador no encontrada: $class";
            exit;
        }

        $ctl = new $class();

        if (!method_exists($ctl, $a)) {
            http_response_code(404);
            echo "Acción no encontrada: $a";
            exit;
        }

        $ctl->{$a}();
    }
}
