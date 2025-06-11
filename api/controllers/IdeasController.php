<?php
require_once __DIR__ . '/../models/Idea.php';
require_once __DIR__ . '/../models/SeguimientoIdea.php';
require_once __DIR__ . '/../models/NotificacionIdea.php';

class IdeasController {
    private $ideaModel;
    private $seguimientoModel;
    private $notificacionModel;

    public function __construct() {
        $this->ideaModel = new Idea();
        $this->seguimientoModel = new SeguimientoIdea();
        $this->notificacionModel = new NotificacionIdea();
    }

    public function explorador($data) {
        try {
            // Validate required fields
            $requiredFields = ['tipo_idea', 'descripcion_corta', 'motivacion', 'problema_oportunidad'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }

            // Process the idea and generate recommendations
            $recomendaciones = [];

            // Add recommendations based on idea type
            switch ($data['tipo_idea']) {
                case 'Negocio':
                    if (empty($data['recursos_disponibles']) || 
                        stripos($data['recursos_disponibles'], 'capital') === false) {
                        $recomendaciones[] = "Valida tu mercado antes de escalar";
                        $recomendaciones[] = "Considera buscar socios o inversores";
                    }
                    break;
                case 'Proyecto Personal':
                    $recomendaciones[] = "Establece metas pequeñas y medibles";
                    $recomendaciones[] = "Define un cronograma realista";
                    break;
                case 'Aplicación':
                    $recomendaciones[] = "Desarrolla un MVP (Producto Mínimo Viable)";
                    $recomendaciones[] = "Realiza pruebas con usuarios potenciales";
                    break;
            }

            // Add general recommendations
            if (empty($data['audiencia_objetivo'])) {
                $recomendaciones[] = "Define claramente tu público objetivo";
            }

            $response = [
                'idea_preparada' => [
                    'descripcion_corta' => $data['descripcion_corta'],
                    'categoria' => $data['tipo_idea'],
                    'estado' => 'Borrador',
                    'siguiente_paso' => $this->determinarSiguientePaso($data),
                    'alertas' => $this->identificarAlertas($data),
                    'recomendaciones' => $recomendaciones
                ]
            ];

            return $this->jsonResponse($response);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function crear($data) {
        try {
            // Validate required fields
            $requiredFields = [
                'id_usuario', 'tipo_idea', 'descripcion_corta', 'motivacion',
                'problema_oportunidad', 'audiencia_objetivo'
            ];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }

            // Create the idea
            $ideaId = $this->ideaModel->createIdea($data);

            if (!$ideaId) {
                throw new Exception("Error al crear la idea");
            }

            // Get the created idea with full details
            $idea = $this->ideaModel->getDetalleCompleto($ideaId);

            return $this->jsonResponse(['mensaje' => 'Idea creada exitosamente', 'idea' => $idea]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function agregarSeguimiento($ideaId, $data) {
        try {
            // Validate idea exists
            $idea = $this->ideaModel->findById($ideaId);
            if (!$idea) {
                throw new Exception("Idea no encontrada");
            }

            // Validate required fields
            if (empty($data['detalle']) || empty($data['estado_actual'])) {
                throw new Exception("Detalle y estado actual son requeridos");
            }

            $data['id_idea'] = $ideaId;

            // Handle file upload if present
            if (!empty($_FILES['archivo'])) {
                $this->seguimientoModel->validarArchivo($_FILES['archivo']);
                $fileName = $this->seguimientoModel->guardarArchivo($_FILES['archivo']);
                $data['archivo_opcional'] = $fileName;
            }

            // Create seguimiento
            $seguimientoId = $this->seguimientoModel->createSeguimiento($data);

            if (!$seguimientoId) {
                throw new Exception("Error al crear el seguimiento");
            }

            return $this->jsonResponse([
                'mensaje' => 'Seguimiento agregado exitosamente',
                'seguimiento' => $this->seguimientoModel->findById($seguimientoId)
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function obtenerRecomendaciones($ideaId) {
        try {
            $idea = $this->ideaModel->findById($ideaId);
            if (!$idea) {
                throw new Exception("Idea no encontrada");
            }

            $recomendaciones = $this->ideaModel->getRecomendaciones($ideaId);

            return $this->jsonResponse(['recomendaciones' => $recomendaciones]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function obtenerDetalle($ideaId) {
        try {
            $detalle = $this->ideaModel->getDetalleCompleto($ideaId);
            if (!$detalle) {
                throw new Exception("Idea no encontrada");
            }

            // Get seguimientos
            $seguimientos = $this->seguimientoModel->getSeguimientosByIdea($ideaId);
            $detalle['seguimientos'] = $seguimientos;

            return $this->jsonResponse(['idea' => $detalle]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    private function determinarSiguientePaso($data) {
        // Logic to determine next step based on idea type and provided information
        switch ($data['tipo_idea']) {
            case 'Negocio':
                return "Define MVP con 5 productos e inicia canal en Instagram";
            case 'Proyecto Personal':
                return "Establece un cronograma semanal con objetivos específicos";
            case 'Aplicación':
                return "Desarrolla wireframes y valida con usuarios potenciales";
            default:
                return "Define objetivos específicos y medibles";
        }
    }

    private function identificarAlertas($data) {
        $alertas = [];

        // Check for potential risks based on provided information
        if (empty($data['recursos_disponibles'])) {
            $alertas[] = "Valida tu mercado antes de escalar";
        }

        if (empty($data['audiencia_objetivo'])) {
            $alertas[] = "Define claramente tu público objetivo";
        }

        if (empty($data['diferenciador'])) {
            $alertas[] = "Identifica tu propuesta de valor única";
        }

        return $alertas;
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }
}
