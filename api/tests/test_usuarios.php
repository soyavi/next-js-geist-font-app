<?php
/**
 * Script de prueba para endpoints de Usuarios
 * Ejecutar desde línea de comandos: php test_usuarios.php
 */

$BASE_URL = 'http://localhost/api';

// Función para hacer peticiones HTTP
function makeRequest($endpoint, $method = 'GET', $data = null) {
    global $BASE_URL;
    $url = $BASE_URL . $endpoint;
    
    $curl = curl_init();
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
    ];

    if ($data) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
        $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
    }

    curl_setopt_array($curl, $options);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    return [
        'code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Función para imprimir resultados
function printResult($testName, $result) {
    echo "\n=== Test: $testName ===\n";
    echo "Status Code: " . $result['code'] . "\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
    echo "================\n";
}

// 1. Crear un nuevo usuario
$nuevoUsuario = [
    "nombre" => "Ana Martínez",
    "email" => "ana.martinez@ejemplo.com",
    "password" => "Contraseña123!"
];

$result = makeRequest('/usuarios', 'POST', $nuevoUsuario);
printResult('Crear Nuevo Usuario', $result);

// Guardar el ID del usuario creado para pruebas posteriores
$userId = $result['response']['usuario']['id'] ?? 1;

// 2. Autenticar usuario
$credenciales = [
    "email" => "ana.martinez@ejemplo.com",
    "password" => "Contraseña123!"
];

$result = makeRequest('/usuarios/auth', 'POST', $credenciales);
printResult('Autenticar Usuario', $result);

// 3. Obtener ideas del usuario
$result = makeRequest("/usuarios/$userId/ideas", 'GET');
printResult('Obtener Ideas del Usuario', $result);

// 4. Obtener resumen del usuario
$result = makeRequest("/usuarios/$userId/resumen", 'GET');
printResult('Obtener Resumen del Usuario', $result);

// 5. Actualizar email del usuario
$nuevoEmail = [
    "email" => "ana.martinez.nueva@ejemplo.com"
];

$result = makeRequest("/usuarios/$userId/email", 'PUT', $nuevoEmail);
printResult('Actualizar Email', $result);

// 6. Actualizar contraseña
$nuevaContraseña = [
    "password_actual" => "Contraseña123!",
    "password_nuevo" => "NuevaContraseña456!"
];

$result = makeRequest("/usuarios/$userId/password", 'PUT', $nuevaContraseña);
printResult('Actualizar Contraseña', $result);

// 7. Probar autenticación con nuevas credenciales
$nuevasCredenciales = [
    "email" => "ana.martinez.nueva@ejemplo.com",
    "password" => "NuevaContraseña456!"
];

$result = makeRequest('/usuarios/auth', 'POST', $nuevasCredenciales);
printResult('Autenticar con Nuevas Credenciales', $result);

// Nota: No probamos el endpoint de eliminar cuenta aquí para mantener los datos de prueba

echo "\nPruebas de Usuario completadas!\n";

// Función para limpiar datos de prueba (comentada por seguridad)
/*
function limpiarDatosPrueba($userId) {
    $eliminarCuenta = [
        "password" => "NuevaContraseña456!"
    ];
    
    $result = makeRequest("/usuarios/$userId", 'DELETE', $eliminarCuenta);
    printResult('Eliminar Cuenta de Prueba', $result);
}

// Descomentar para limpiar los datos de prueba
// limpiarDatosPrueba($userId);
*/
