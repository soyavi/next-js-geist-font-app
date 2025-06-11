<?php
// Configuración inicial
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Zona horaria
date_default_timezone_set('America/Costa_Rica');

// Verificar el método OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit(0);
}

// Incluir el archivo de rutas
require_once __DIR__ . '/routes/api.php';

/**
 * Documentación de la API
 * 
 * Endpoints principales:
 * 
 * Módulo de Ideas:
 * ----------------
 * POST /api/ideas/explorador
 * - Guía inicial para entender y analizar nuevas ideas
 * 
 * POST /api/ideas
 * - Crear una nueva idea
 * 
 * POST /api/ideas/{id}/seguimiento
 * - Agregar seguimiento a una idea existente
 * 
 * GET /api/ideas/{id}/recomendaciones
 * - Obtener recomendaciones personalizadas
 * 
 * GET /api/ideas/{id}
 * - Obtener detalle completo de una idea
 * 
 * Módulo de Usuarios:
 * ------------------
 * POST /api/usuarios
 * - Crear nuevo usuario
 * 
 * POST /api/usuarios/auth
 * - Autenticar usuario
 * 
 * GET /api/usuarios/{id}/ideas
 * - Listar ideas de un usuario
 * 
 * GET /api/usuarios/{id}/resumen
 * - Obtener resumen de actividad del usuario
 * 
 * PUT /api/usuarios/{id}/email
 * - Actualizar email del usuario
 * 
 * PUT /api/usuarios/{id}/password
 * - Actualizar contraseña del usuario
 * 
 * DELETE /api/usuarios/{id}
 * - Eliminar cuenta de usuario
 * 
 * Ejemplos de uso:
 * 
 * 1. Crear un nuevo usuario:
 * POST /api/usuarios
 * {
 *     "nombre": "Juan Pérez",
 *     "email": "juan@ejemplo.com",
 *     "password": "contraseña123"
 * }
 * 
 * 2. Explorar una nueva idea:
 * POST /api/ideas/explorador
 * {
 *     "tipo_idea": "Negocio",
 *     "descripcion_corta": "Tienda de productos naturales",
 *     "motivacion": "Ofrecer productos saludables",
 *     "problema_oportunidad": "Falta de opciones naturales",
 *     "ubicacion": "San José",
 *     "audiencia_objetivo": "Personas interesadas en salud",
 *     "recursos_disponibles": "Capital inicial $1000"
 * }
 * 
 * 3. Crear una idea:
 * POST /api/ideas
 * {
 *     "id_usuario": 1,
 *     "tipo_idea": "Negocio",
 *     "descripcion_corta": "Cafetería sostenible",
 *     "motivacion": "Combinar salud y ecología",
 *     "problema_oportunidad": "Pocas opciones saludables económicas",
 *     "ubicacion": "San José",
 *     "audiencia_objetivo": "Adultos jóvenes y profesionales",
 *     "diferenciador": "Productos orgánicos locales",
 *     "recursos_disponibles": "$2000 y local",
 *     "primer_paso": "Definir menú inicial",
 *     "fecha_recordatorio": "2024-07-01"
 * }
 * 
 * 4. Agregar seguimiento:
 * POST /api/ideas/1/seguimiento
 * {
 *     "detalle": "Contacté a 3 proveedores locales",
 *     "estado_actual": "en_proceso"
 * }
 */
