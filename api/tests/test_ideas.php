<?php
/**
 * Script de prueba para endpoints de Ideas
 * Ejecutar desde línea de comandos: php test_ideas.php
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

// 1. Probar el explorador de ideas
$ideaExplorador = [
    "tipo_idea" => "Negocio",
    "descripcion_corta" => "Tienda de productos naturales en línea",
    "motivacion" => "Ofrecer productos saludables y artesanales",
    "problema_oportunidad" => "Falta de opciones confiables sin químicos",
    "ubicacion" => "Costa Rica",
    "audiencia_objetivo" => "Personas entre 25 y 50 interesadas en salud",
    "recursos_disponibles" => "Tiempo parcial, $1,000 de capital",
    "primer_paso" => "Buscar proveedores y definir catálogo"
];

$result = makeRequest('/ideas/explorador', 'POST', $ideaExplorador);
printResult('Explorador de Ideas', $result);

// 2. Crear una nueva idea
$nuevaIdea = [
    "id_usuario" => 1,
    "tipo_idea" => "Negocio",
    "descripcion_corta" => "Cafetería con enfoque en salud y sostenibilidad",
    "motivacion" => "Quiero emprender en algo que combine salud y ecología",
    "problema_oportunidad" => "En mi ciudad hay pocas opciones saludables que sean también económicas",
    "ubicacion" => "San José, Costa Rica",
    "audiencia_objetivo" => "Adultos jóvenes y profesionales interesados en salud y medio ambiente",
    "diferenciador" => "Tendremos alimentos orgánicos locales, empaques biodegradables y eventos de comunidad",
    "recursos_disponibles" => "$2,000 de capital y un local en alquiler",
    "primer_paso" => "Definir menú mínimo viable y hacer pruebas con amigos",
    "posibles_barreras" => "Competencia de cadenas grandes",
    "vision_6_meses" => "50 clientes fijos por semana",
    "medicion_exito" => "Clientes recurrentes y comentarios positivos",
    "fecha_recordatorio" => "2024-07-01"
];

$result = makeRequest('/ideas', 'POST', $nuevaIdea);
printResult('Crear Nueva Idea', $result);

// Guardar el ID de la idea creada para pruebas posteriores
$ideaId = $result['response']['idea']['id'] ?? 1;

// 3. Agregar seguimiento a la idea
$seguimiento = [
    "detalle" => "Contacté a tres proveedores locales de productos orgánicos",
    "estado_actual" => "en_proceso",
    "fecha" => date('Y-m-d H:i:s')
];

$result = makeRequest("/ideas/$ideaId/seguimiento", 'POST', $seguimiento);
printResult('Agregar Seguimiento', $result);

// 4. Obtener recomendaciones
$result = makeRequest("/ideas/$ideaId/recomendaciones", 'GET');
printResult('Obtener Recomendaciones', $result);

// 5. Obtener detalle de la idea
$result = makeRequest("/ideas/$ideaId", 'GET');
printResult('Obtener Detalle de Idea', $result);

echo "\nPruebas completadas!\n";
