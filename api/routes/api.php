<?php
require_once __DIR__ . '/../controllers/IdeasController.php';
require_once __DIR__ . '/../controllers/UsuariosController.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);

// Get request path and method
$request = parse_url($_SERVER['REQUEST_URI']);
$path = $request['path'];
$method = $_SERVER['REQUEST_METHOD'];

// Initialize controllers
$ideasController = new IdeasController();
$usuariosController = new UsuariosController();

// Route patterns
$routes = [
    // Ideas routes
    ['pattern' => '#^/api/ideas/explorador$#', 'method' => 'POST', 'handler' => function() use ($ideasController, $input) {
        return $ideasController->explorador($input);
    }],
    
    ['pattern' => '#^/api/ideas$#', 'method' => 'POST', 'handler' => function() use ($ideasController, $input) {
        return $ideasController->crear($input);
    }],
    
    ['pattern' => '#^/api/ideas/(\d+)/seguimiento$#', 'method' => 'POST', 'handler' => function($matches) use ($ideasController, $input) {
        return $ideasController->agregarSeguimiento($matches[1], $input);
    }],
    
    ['pattern' => '#^/api/ideas/(\d+)/recomendaciones$#', 'method' => 'GET', 'handler' => function($matches) use ($ideasController) {
        return $ideasController->obtenerRecomendaciones($matches[1]);
    }],
    
    ['pattern' => '#^/api/ideas/(\d+)$#', 'method' => 'GET', 'handler' => function($matches) use ($ideasController) {
        return $ideasController->obtenerDetalle($matches[1]);
    }],

    // Usuarios routes
    ['pattern' => '#^/api/usuarios$#', 'method' => 'POST', 'handler' => function() use ($usuariosController, $input) {
        return $usuariosController->crear($input);
    }],
    
    ['pattern' => '#^/api/usuarios/auth$#', 'method' => 'POST', 'handler' => function() use ($usuariosController, $input) {
        return $usuariosController->autenticar($input);
    }],
    
    ['pattern' => '#^/api/usuarios/(\d+)/ideas$#', 'method' => 'GET', 'handler' => function($matches) use ($usuariosController) {
        return $usuariosController->obtenerIdeas($matches[1]);
    }],
    
    ['pattern' => '#^/api/usuarios/(\d+)/resumen$#', 'method' => 'GET', 'handler' => function($matches) use ($usuariosController) {
        return $usuariosController->obtenerResumen($matches[1]);
    }],
    
    ['pattern' => '#^/api/usuarios/(\d+)/email$#', 'method' => 'PUT', 'handler' => function($matches) use ($usuariosController, $input) {
        return $usuariosController->actualizarEmail($matches[1], $input);
    }],
    
    ['pattern' => '#^/api/usuarios/(\d+)/password$#', 'method' => 'PUT', 'handler' => function($matches) use ($usuariosController, $input) {
        return $usuariosController->actualizarPassword($matches[1], $input);
    }],
    
    ['pattern' => '#^/api/usuarios/(\d+)$#', 'method' => 'DELETE', 'handler' => function($matches) use ($usuariosController, $input) {
        return $usuariosController->eliminarCuenta($matches[1], $input);
    }]
];

// Route handling
$routeFound = false;

foreach ($routes as $route) {
    if (preg_match($route['pattern'], $path, $matches) && $route['method'] === $method) {
        $routeFound = true;
        echo $route['handler']($matches);
        break;
    }
}

if (!$routeFound) {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no encontrada']);
}
