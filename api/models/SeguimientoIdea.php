<?php
require_once __DIR__ . '/BaseModel.php';

class SeguimientoIdea extends BaseModel {
    protected $table = 'seguimiento_ideas';

    public function __construct() {
        parent::__construct();
    }

    public function createSeguimiento($data) {
        try {
            $this->beginTransaction();

            // Create the seguimiento record
            $seguimientoId = $this->create($data);

            if (!$seguimientoId) {
                throw new Exception("Error creating seguimiento");
            }

            // Update the idea's status if provided
            if (!empty($data['estado_actual'])) {
                $ideaModel = new Idea();
                $updateData = ['estado' => $data['estado_actual']];
                if (!$ideaModel->update($data['id_idea'], $updateData)) {
                    throw new Exception("Error updating idea status");
                }
            }

            $this->commit();
            return $seguimientoId;

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function getSeguimientosByIdea($ideaId, $limit = null, $offset = null) {
        return $this->findAll(['id_idea' => $ideaId], $limit, $offset);
    }

    public function getUltimoSeguimiento($ideaId) {
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE id_idea = :ideaId 
                 ORDER BY fecha DESC 
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ideaId", $ideaId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getResumenSeguimientos($ideaId) {
        $query = "SELECT 
                    COUNT(*) as total_seguimientos,
                    MIN(fecha) as primer_seguimiento,
                    MAX(fecha) as ultimo_seguimiento,
                    GROUP_CONCAT(DISTINCT estado_actual) as estados
                 FROM " . $this->table . "
                 WHERE id_idea = :ideaId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ideaId", $ideaId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getIdeasSinSeguimiento($dias = 30) {
        $query = "SELECT i.* FROM ideas i
                 LEFT JOIN (
                     SELECT id_idea, MAX(fecha) as ultima_fecha
                     FROM " . $this->table . "
                     GROUP BY id_idea
                 ) s ON i.id = s.id_idea
                 WHERE s.ultima_fecha IS NULL 
                 OR s.ultima_fecha < DATE_SUB(NOW(), INTERVAL :dias DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":dias", $dias, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function validarArchivo($archivo) {
        // Implement file validation logic here
        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        $fileInfo = pathinfo($archivo['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $allowedTypes)) {
            throw new Exception("Tipo de archivo no permitido");
        }

        if ($archivo['size'] > $maxSize) {
            throw new Exception("El archivo excede el tamaño máximo permitido (5MB)");
        }

        return true;
    }

    public function guardarArchivo($archivo) {
        // Implement file saving logic here
        $uploadDir = __DIR__ . '/../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($archivo['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($archivo['tmp_name'], $targetPath)) {
            return $fileName;
        }

        throw new Exception("Error al guardar el archivo");
    }
}
