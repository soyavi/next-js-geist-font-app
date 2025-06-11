<?php
require_once __DIR__ . '/BaseModel.php';

class Idea extends BaseModel {
    protected $table = 'ideas';

    public function __construct() {
        parent::__construct();
    }

    public function createIdea($data) {
        try {
            $this->beginTransaction();

            // Create the idea
            $ideaId = $this->create($data);

            if (!$ideaId) {
                throw new Exception("Error creating idea");
            }

            // If there's a reminder date, create a notification
            if (!empty($data['fecha_recordatorio'])) {
                $notificationData = [
                    'id_idea' => $ideaId,
                    'tipo' => 'recordatorio',
                    'mensaje' => 'Recordatorio programado para la idea: ' . $data['descripcion_corta'],
                    'fecha' => $data['fecha_recordatorio']
                ];

                $notificationModel = new NotificacionIdea();
                if (!$notificationModel->create($notificationData)) {
                    throw new Exception("Error creating notification");
                }
            }

            $this->commit();
            return $ideaId;

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function getIdeasByUser($userId, $estado = null) {
        $conditions = ['id_usuario' => $userId];
        if ($estado) {
            $conditions['estado'] = $estado;
        }
        return $this->findAll($conditions);
    }

    public function getIdeasEstancadas($userId) {
        $query = "SELECT i.* FROM " . $this->table . " i 
                 LEFT JOIN seguimiento_ideas s ON i.id = s.id_idea 
                 WHERE i.id_usuario = :userId 
                 AND (s.fecha IS NULL OR s.fecha < DATE_SUB(NOW(), INTERVAL 30 DAY))
                 GROUP BY i.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResumenUsuario($userId) {
        $query = "SELECT 
                    COUNT(*) as total_ideas,
                    SUM(CASE WHEN estado = 'activa' THEN 1 ELSE 0 END) as ideas_activas,
                    SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as ideas_completadas,
                    tipo_idea,
                    COUNT(*) as cantidad_por_tipo
                 FROM " . $this->table . "
                 WHERE id_usuario = :userId
                 GROUP BY tipo_idea";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetalleCompleto($ideaId) {
        $query = "SELECT i.*, 
                    GROUP_CONCAT(s.detalle) as seguimientos,
                    GROUP_CONCAT(s.fecha) as fechas_seguimiento,
                    GROUP_CONCAT(s.estado_actual) as estados_seguimiento
                 FROM " . $this->table . " i
                 LEFT JOIN seguimiento_ideas s ON i.id = s.id_idea
                 WHERE i.id = :ideaId
                 GROUP BY i.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ideaId", $ideaId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecomendaciones($ideaId) {
        $idea = $this->findById($ideaId);
        if (!$idea) {
            return null;
        }

        $recomendaciones = [];

        // Lógica de recomendaciones basada en el tipo de idea y otros factores
        if ($idea['tipo_idea'] == 'negocio' && empty($idea['recursos_disponibles'])) {
            $recomendaciones[] = [
                'tipo' => 'financiamiento',
                'mensaje' => 'Considera buscar socios o explorar opciones de crowdfunding'
            ];
        }

        if (!empty($idea['motivacion']) && empty($idea['diferenciador'])) {
            $recomendaciones[] = [
                'tipo' => 'mentoria',
                'mensaje' => 'Busca mentoría para definir mejor tu propuesta de valor'
            ];
        }

        // Más lógica de recomendaciones según otros factores...

        return $recomendaciones;
    }
}
