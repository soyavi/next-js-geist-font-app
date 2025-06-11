<?php
require_once __DIR__ . '/BaseModel.php';

class Usuario extends BaseModel {
    protected $table = 'usuarios';

    public function __construct() {
        parent::__construct();
    }

    public function createUsuario($data) {
        // Hash password before storing
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $this->create($data);
    }

    public function authenticate($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // Don't return password in response
            return $user;
        }

        return false;
    }

    public function getResumenIdeas($userId) {
        $ideaModel = new Idea();
        return $ideaModel->getResumenUsuario($userId);
    }

    public function getIdeasActivas($userId) {
        $ideaModel = new Idea();
        return $ideaModel->getIdeasByUser($userId, 'activa');
    }

    public function getIdeasEstancadas($userId) {
        $ideaModel = new Idea();
        return $ideaModel->getIdeasEstancadas($userId);
    }

    public function getNotificacionesPendientes($userId) {
        $query = "SELECT n.* 
                 FROM notificaciones_ideas n
                 JOIN ideas i ON n.id_idea = i.id
                 WHERE i.id_usuario = :userId
                 AND n.leida = FALSE
                 ORDER BY n.fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateEmail($userId, $newEmail) {
        // Verify email is not already in use
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email AND id != :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $newEmail);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();

        if ($stmt->fetch()) {
            throw new Exception("Email already in use");
        }

        return $this->update($userId, ['email' => $newEmail]);
    }

    public function updatePassword($userId, $currentPassword, $newPassword) {
        // Verify current password
        $user = $this->findById($userId);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            throw new Exception("Current password is incorrect");
        }

        // Update to new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }

    public function getEstadisticasActividad($userId) {
        $query = "SELECT 
                    (SELECT COUNT(*) FROM ideas WHERE id_usuario = :userId) as total_ideas,
                    (SELECT COUNT(*) FROM seguimiento_ideas s 
                     JOIN ideas i ON s.id_idea = i.id 
                     WHERE i.id_usuario = :userId) as total_seguimientos,
                    (SELECT COUNT(*) FROM ideas 
                     WHERE id_usuario = :userId AND estado = 'completada') as ideas_completadas,
                    (SELECT COUNT(*) FROM notificaciones_ideas n 
                     JOIN ideas i ON n.id_idea = i.id 
                     WHERE i.id_usuario = :userId) as total_notificaciones";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteAccount($userId, $password) {
        // Verify password before deletion
        $user = $this->findById($userId);
        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Invalid password");
        }

        try {
            $this->beginTransaction();

            // Delete related records (notifications, seguimientos, ideas)
            $query = "DELETE n FROM notificaciones_ideas n 
                     JOIN ideas i ON n.id_idea = i.id 
                     WHERE i.id_usuario = :userId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":userId", $userId);
            $stmt->execute();

            $query = "DELETE s FROM seguimiento_ideas s 
                     JOIN ideas i ON s.id_idea = i.id 
                     WHERE i.id_usuario = :userId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":userId", $userId);
            $stmt->execute();

            // Delete ideas
            $query = "DELETE FROM ideas WHERE id_usuario = :userId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":userId", $userId);
            $stmt->execute();

            // Finally, delete user
            if (!$this->delete($userId)) {
                throw new Exception("Error deleting user");
            }

            $this->commit();
            return true;

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
