<?php
// AuthController.php
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Config/Database.php';

class AuthController {
    public function loginForm(){
        if (isset($_SESSION['user'])) {
            header('Location: index.php?c=dashboard&a=index');
            exit;
        }
        require __DIR__ . '/../Views/login.php';
    }

    public function login(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); 
            echo json_encode(['ok'=>false,'msg'=>'Método no permitido']); 
            exit;
        }
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $um = new UserModel();
        $user = $um->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {

            // Actualizar contador de logins, último login y última actividad
            try {
                $db = (new Database())->connect();
                $stmt = $db->prepare("
                    UPDATE users 
                    SET login_count = login_count + 1,
                        last_login  = NOW(),
                        last_active = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([':id' => $user['id']]);
            } catch (Exception $e) {
                // opcional: loguear error
                // error_log('Error actualizando login_count: ' . $e->getMessage());
            }

            $_SESSION['user'] = [
                'id'       => $user['id'],
                'username' => $user['username'],
                'fullname' => $user['fullname'],
                'role'     => $user['role']
            ];
            echo json_encode(['ok'=>true]);
            exit;

        } else {
            echo json_encode(['ok'=>false,'msg'=>'Usuario o contraseña incorrectos']);
            exit;
        }
    }

    public function logout(){
        session_start();
        session_unset();
        session_destroy();
        header('Location: index.php?c=auth&a=loginForm');
        exit;
    }
}
