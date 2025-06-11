<?php
/**
 * Configuración para los tests
 */

// URL base de la API
define('API_BASE_URL', 'http://localhost/api');

// Configuración de la base de datos de pruebas
define('DB_HOST', 'localhost');
define('DB_NAME', 'avi_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de pruebas
define('CLEANUP_AFTER_TESTS', false); // Si es true, elimina los datos de prueba al finalizar
define('VERBOSE_OUTPUT', true);       // Si es true, muestra más detalles en la salida

// Datos de prueba
define('TEST_USER_EMAIL', 'test@ejemplo.com');
define('TEST_USER_PASSWORD', 'Test123!');

// Función helper para formatear la salida
function printTestResult($testName, $success, $message = '') {
    $status = $success ? '✅' : '❌';
    echo "\n$status $testName";
    if ($message && VERBOSE_OUTPUT) {
        echo "\n   $message";
    }
    echo "\n";
}
