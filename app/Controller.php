<?php
// app/Controller.php
class Controller
{
    protected function loadView(string $view, array $data = [])
    {
        // variables para la vista (ej: $personas)
        if (!empty($data)) {
            extract($data);
        }

        // vista de contenido del módulo, por ej: app/Views/personas/index.php
        $content_view = __DIR__ . "/Views/{$view}.php";

        if (!file_exists($content_view)) {
            echo "Vista de contenido no encontrada: {$content_view}";
            return;
        }

        // layout principal: app/Views/main.php
        require __DIR__ . "/Views/main.php";
    }
}
