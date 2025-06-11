<?php
require_once __DIR__ . '/../models/NotificacionIdea.php';
require_once __DIR__ . '/../models/Idea.php';

class RecordatoriosJob {
    private $notificacionModel;
    private $ideaModel;
    private $logFile;

    public function __construct() {
        $this->notificacionModel = new NotificacionIdea();
        $this->ideaModel = new Idea();
        $this->logFile = __DIR__ . '/recordatorios.log';
    }

    public function ejecutar() {
        $this->log("Iniciando proceso de recordatorios " . date('Y-m-d H:i:s'));

        try {
            // Get today's reminders
            $recordatorios = $this->notificacionModel->getRecordatoriosHoy();
            $this->log("Encontrados " . count($recordatorios) . " recordatorios para hoy");

            foreach ($recordatorios as $recordatorio) {
                try {
                    // Send notification (email in this case)
                    $enviado = $this->notificacionModel->enviarNotificacionEmail($recordatorio);
                    
                    if ($enviado) {
                        // Mark notification as read
                        $this->notificacionModel->marcarComoLeida($recordatorio['id']);
                        $this->log("Recordatorio enviado para idea {$recordatorio['id_idea']}");
                    } else {
                        throw new Exception("Error enviando recordatorio");
                    }

                } catch (Exception $e) {
                    $this->log("Error procesando recordatorio {$recordatorio['id']}: " . $e->getMessage());
                }
            }

            // Clean up old notifications
            $this->notificacionModel->limpiarNotificacionesAntiguas(30);
            $this->log("Proceso de limpieza de notificaciones antiguas completado");

            // Check for stalled ideas (no updates in 30 days)
            $ideasEstancadas = $this->ideaModel->getIdeasEstancadas();
            foreach ($ideasEstancadas as $idea) {
                try {
                    // Create reminder notification
                    $notificacionData = [
                        'id_idea' => $idea['id'],
                        'tipo' => 'alerta_estancamiento',
                        'mensaje' => 'Tu idea no ha tenido actualizaciones en los últimos 30 días. ¿Necesitas ayuda para avanzar?'
                    ];
                    
                    $this->notificacionModel->createNotificacion($notificacionData);
                    $this->log("Alerta de estancamiento creada para idea {$idea['id']}");

                } catch (Exception $e) {
                    $this->log("Error creando alerta de estancamiento para idea {$idea['id']}: " . $e->getMessage());
                }
            }

            $this->log("Proceso de recordatorios completado exitosamente");

        } catch (Exception $e) {
            $this->log("Error general en el proceso: " . $e->getMessage());
            throw $e;
        }
    }

    private function log($mensaje) {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] " . $mensaje . "\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
}

// Execute the job
try {
    $job = new RecordatoriosJob();
    $job->ejecutar();
} catch (Exception $e) {
    // Log to system error log in case file logging fails
    error_log("Error en RecordatoriosJob: " . $e->getMessage());
}
