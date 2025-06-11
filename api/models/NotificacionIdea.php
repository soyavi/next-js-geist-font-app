<?php
require_once __DIR__ . '/BaseModel.php';

class NotificacionIdea extends BaseModel {
    protected $table = 'notificaciones_ideas';

    public function __construct() {
        parent::__construct();
    }

    public function createNotificacion($data) {
        // Set default values if not provided
        if (empty($data['fecha'])) {
            $data['fecha'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['leida'])) {
            $data['leida'] = false;
        }

        return $this->create($data);
    }

    public function getNotificacionesPendientes($ideaId = null) {
        $query = "SELECT n.*, i.descripcion_corta as idea_descripcion 
                 FROM " . $this->table . " n
                 JOIN ideas i ON n.id_idea = i.id
                 WHERE n.leida = FALSE";
        
        if ($ideaId) {
            $query .= " AND n.id_idea = :ideaId";
        }
        
        $query .= " ORDER BY n.fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        if ($ideaId) {
            $stmt->bindParam(":ideaId", $ideaId);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarComoLeida($id) {
        return $this->update($id, ['leida' => true]);
    }

    public function getRecordatoriosHoy() {
        $query = "SELECT n.*, i.descripcion_corta, i.id_usuario, u.email 
                 FROM " . $this->table . " n
                 JOIN ideas i ON n.id_idea = i.id
                 JOIN usuarios u ON i.id_usuario = u.id
                 WHERE n.tipo = 'recordatorio' 
                 AND DATE(n.fecha) = CURDATE()
                 AND n.leida = FALSE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResumenNotificaciones($userId) {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN n.leida = FALSE THEN 1 ELSE 0 END) as no_leidas,
                    COUNT(DISTINCT n.id_idea) as ideas_con_notificaciones
                 FROM " . $this->table . " n
                 JOIN ideas i ON n.id_idea = i.id
                 WHERE i.id_usuario = :userId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function enviarNotificacionEmail($notificacion) {
        // Implement email sending logic here
        // This is a placeholder - you would typically integrate with an email service
        
        $to = $notificacion['email'];
        $subject = "Recordatorio de Idea - " . $notificacion['descripcion_corta'];
        $message = "Hola,\n\n";
        $message .= "Este es un recordatorio para tu idea: " . $notificacion['descripcion_corta'] . "\n";
        $message .= "Mensaje: " . $notificacion['mensaje'] . "\n\n";
        $message .= "Saludos,\nEquipo AVI";
        
        $headers = "From: notificaciones@avi.com\r\n";
        $headers .= "Reply-To: no-reply@avi.com\r\n";
        
        // For development, you might want to log instead of actually sending
        error_log("Email would be sent to: $to");
        error_log("Subject: $subject");
        error_log("Message: $message");
        
        // Uncomment to actually send emails in production
        // return mail($to, $subject, $message, $headers);
        
        return true;
    }

    public function limpiarNotificacionesAntiguas($dias = 30) {
        $query = "DELETE FROM " . $this->table . "
                 WHERE leida = TRUE 
                 AND fecha < DATE_SUB(NOW(), INTERVAL :dias DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":dias", $dias, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
