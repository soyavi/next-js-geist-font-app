<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Idea.php';

class UsuariosController {
    private $usuarioModel;
    private $ideaModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->ideaModel = new Idea();
    }

    public function obtenerIdeas($userId) {
        try {
            // Validate user exists
            $usuario = $this->usuarioModel->findById($userId);
            if (!$usuario) {
                throw new Exception("Usuario no encontrado");
            }

            $ideas = $this->ideaModel->getIdeasByUser($userId);

            return $this->jsonResponse(['ideas' => $ideas]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function obtenerResumen($userId) {
        try {
            // Validate user exists
            $usuario = $this->usuarioModel->findById($userId);
            if (!$usuario) {
                throw new Exception("Usuario no encontrado");
            }

            // Get ideas summary
            $resumen = $this->usuarioModel->getResumenIdeas($userId);

            // Get stalled ideas
            $ideasEstancadas = $this->usuarioModel->getIdeasEstancadas($userId);

            // Get notifications
            $notificaciones = $this->usuarioModel->getNotificacionesPendientes($userId);

            // Get activity statistics
            $estadisticas = $this->usuarioModel->getEstadisticasActividad($userId);

            $response = [
                'resumen' => $resumen,
                'ideas_estancadas' => count($ideasEstancadas),
                'notificaciones_pendientes' => count($notificaciones),
                'estadisticas' => $estadisticas
            ];

            return $this->jsonResponse($response);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function crear($data) {
        try {
            // Validate required fields
            $requiredFields = ['nombre', 'email', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Formato de email inválido");
            }

            // Create user
            $userId = $this->usuarioModel->createUsuario($data);

            if (!$userId) {
                throw new Exception("Error al crear el usuario");
            }

            // Get created user (without password)
            $usuario = $this->usuarioModel->findById($userId);
            unset($usuario['password']);

            return $this->jsonResponse([
                'mensaje' => 'Usuario creado exitosamente',
                'usuario' => $usuario
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function autenticar($data) {
        try {
            // Validate required fields
            if (empty($data['email']) || empty($data['password'])) {
                throw new Exception("Email y password son requeridos");
            }

            // Authenticate user
            $usuario = $this->usuarioModel->authenticate($data['email'], $data['password']);

            if (!$usuario) {
                throw new Exception("Credenciales inválidas");
            }

            // Here you would typically generate a JWT token
            // For this example, we'll just return the user
            return $this->jsonResponse([
                'mensaje' => 'Autenticación exitosa',
                'usuario' => $usuario
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 401);
        }
    }

    public function actualizarEmail($userId, $data) {
        try {
            if (empty($data['email'])) {
                throw new Exception("Nuevo email es requerido");
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Formato de email inválido");
            }

            if (!$this->usuarioModel->updateEmail($userId, $data['email'])) {
                throw new Exception("Error al actualizar el email");
            }

            return $this->jsonResponse(['mensaje' => 'Email actualizado exitosamente']);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function actualizarPassword($userId, $data) {
        try {
            if (empty($data['password_actual']) || empty($data['password_nuevo'])) {
                throw new Exception("Password actual y nuevo son requeridos");
            }

            if (!$this->usuarioModel->updatePassword($userId, $data['password_actual'], $data['password_nuevo'])) {
                throw new Exception("Error al actualizar el password");
            }

            return $this->jsonResponse(['mensaje' => 'Password actualizado exitosamente']);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function eliminarCuenta($userId, $data) {
        try {
            if (empty($data['password'])) {
                throw new Exception("Password es requerido para eliminar la cuenta");
            }

            if (!$this->usuarioModel->deleteAccount($userId, $data['password'])) {
                throw new Exception("Error al eliminar la cuenta");
            }

            return $this->jsonResponse(['mensaje' => 'Cuenta eliminada exitosamente']);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }
}
