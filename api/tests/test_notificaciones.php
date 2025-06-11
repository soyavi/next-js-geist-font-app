<?php
/**
 * Script de prueba para el sistema de notificaciones y recordatorios
 * Ejecutar desde línea de comandos: php test_notificaciones.php
 */

require_once __DIR__ . '/../models/NotificacionIdea.php';
require_once __DIR__ . '/../models/Idea.php';
require_once __DIR__ . '/../models/Usuario.php';

class TestNotificaciones {
    private $notificacionModel;
    private $ideaModel;
    private $usuarioModel;

    public function __construct() {
        $this->notificacionModel = new NotificacionIdea();
        $this->ideaModel = new Idea();
        $this->usuarioModel = new Usuario();
    }

    public function ejecutarPruebas() {
        echo "\n=== Iniciando pruebas de notificaciones ===\n";

        // 1. Crear usuario de prueba
        $userId = $this->crearUsuarioPrueba();
        echo "Usuario de prueba creado con ID: $userId\n";

        // 2. Crear ideas de prueba con recordatorios
        $ideasIds = $this->crearIdeasPrueba($userId);
        echo "Ideas de prueba creadas: " . implode(", ", $ideasIds) . "\n";

        // 3. Probar creación de notificaciones
        $this->probarCreacionNotificaciones($ideasIds[0]);

        // 4. Probar obtención de recordatorios
        $this->probarObtenerRecordatorios();

        // 5. Probar marcado de notificaciones como leídas
        $this->probarMarcarComoLeida();

        // 6. Probar limpieza de notificaciones
        $this->probarLimpiezaNotificaciones();

        // 7. Probar detección de ideas estancadas
        $this->probarDeteccionIdeasEstancadas();

        echo "\n=== Pruebas completadas ===\n";
    }

    private function crearUsuarioPrueba() {
        $userData = [
            'nombre' => 'Usuario Prueba Notificaciones',
            'email' => 'test.notificaciones@ejemplo.com',
            'password' => 'test123'
        ];

        return $this->usuarioModel->createUsuario($userData);
    }

    private function crearIdeasPrueba($userId) {
        $ideas = [
            [
                'id_usuario' => $userId,
                'tipo_idea' => 'Negocio',
                'descripcion_corta' => 'Idea de prueba 1',
                'motivacion' => 'Prueba de notificaciones',
                'problema_oportunidad' => 'Testing',
                'fecha_recordatorio' => date('Y-m-d', strtotime('+1 day'))
            ],
            [
                'id_usuario' => $userId,
                'tipo_idea' => 'Proyecto',
                'descripcion_corta' => 'Idea de prueba 2',
                'motivacion' => 'Prueba de ideas estancadas',
                'problema_oportunidad' => 'Testing',
                'fecha_recordatorio' => date('Y-m-d', strtotime('+30 days'))
            ]
        ];

        $ideaIds = [];
        foreach ($ideas as $idea) {
            $ideaIds[] = $this->ideaModel->createIdea($idea);
        }

        return $ideaIds;
    }

    private function probarCreacionNotificaciones($ideaId) {
        echo "\nProbando creación de notificaciones...\n";

        $notificacion = [
            'id_idea' => $ideaId,
            'tipo' => 'recordatorio',
            'mensaje' => 'Notificación de prueba',
            'fecha' => date('Y-m-d H:i:s')
        ];

        $notifId = $this->notificacionModel->createNotificacion($notificacion);
        
        if ($notifId) {
            echo "✓ Notificación creada correctamente (ID: $notifId)\n";
        } else {
            echo "✗ Error al crear notificación\n";
        }
    }

    private function probarObtenerRecordatorios() {
        echo "\nProbando obtención de recordatorios...\n";
        
        $recordatorios = $this->notificacionModel->getRecordatoriosHoy();
        echo "Recordatorios encontrados: " . count($recordatorios) . "\n";
        
        foreach ($recordatorios as $recordatorio) {
            echo "- Recordatorio para idea: {$recordatorio['descripcion_corta']}\n";
        }
    }

    private function probarMarcarComoLeida() {
        echo "\nProbando marcar notificaciones como leídas...\n";
        
        $notificaciones = $this->notificacionModel->getNotificacionesPendientes();
        foreach ($notificaciones as $notif) {
            if ($this->notificacionModel->marcarComoLeida($notif['id'])) {
                echo "✓ Notificación {$notif['id']} marcada como leída\n";
            }
        }
    }

    private function probarLimpiezaNotificaciones() {
        echo "\nProbando limpieza de notificaciones antiguas...\n";
        
        $resultado = $this->notificacionModel->limpiarNotificacionesAntiguas(30);
        if ($resultado) {
            echo "✓ Limpieza de notificaciones completada\n";
        } else {
            echo "✗ Error en limpieza de notificaciones\n";
        }
    }

    private function probarDeteccionIdeasEstancadas() {
        echo "\nProbando detección de ideas estancadas...\n";
        
        $ideasEstancadas = $this->ideaModel->getIdeasEstancadas();
        echo "Ideas estancadas encontradas: " . count($ideasEstancadas) . "\n";
        
        foreach ($ideasEstancadas as $idea) {
            echo "- Idea estancada: {$idea['descripcion_corta']}\n";
            
            // Crear notificación de estancamiento
            $notificacion = [
                'id_idea' => $idea['id'],
                'tipo' => 'alerta_estancamiento',
                'mensaje' => 'La idea no ha tenido actualizaciones en los últimos 30 días'
            ];
            
            $this->notificacionModel->createNotificacion($notificacion);
        }
    }
}

// Ejecutar las pruebas
try {
    $tester = new TestNotificaciones();
    $tester->ejecutarPruebas();
} catch (Exception $e) {
    echo "\n❌ Error durante las pruebas: " . $e->getMessage() . "\n";
}

echo "\nPruebas de notificaciones completadas!\n";
