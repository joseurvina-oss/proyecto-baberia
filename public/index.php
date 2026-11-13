<?php
// public/index.php
session_start();

// 1. Obtener la ruta solicitada de forma limpia
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 2. Limpiar las subcarpetas locales y de despliegue
$request = str_replace('/saas-barberia/public', '', $request);
$request = str_replace('/public', '', $request);

// 3. Eliminar la barra final si existe para evitar conflictos
$request = rtrim($request, '/');

// 4. Si la ruta queda vacía, la forzamos a la página principal '/'
if ($request === '' || $request === '/index.php') {
    $request = '/';
}

// 5. Router principal de la aplicación
switch ($request) {
    // ---- VISTAS PÚBLICAS Y CLIENTES ----
    case '':
    case '/':
        require __DIR__ . '/../views/cliente/index.php';
        break;

    case '/barberos':
        require __DIR__ . '/../views/cliente/cliente_barbero.php';
        break;

    case '/reserva':
    case '/reservar': // Soporta ambas variantes para evitar errores 404
        require __DIR__ . '/../views/cliente/cliente_reserva.php';
        break;

    // ---- VISTAS DE AUTENTICACIÓN ----
    case '/login':
        require __DIR__ . '/../views/autenticacion/login_barbero.php';
        break;

    case '/registro':
        require __DIR__ . '/../views/autenticacion/registro.php';
        break;

    // ---- VISTAS DE BARBEROS Y ADMIN ----
    case '/dashboard':
        require __DIR__ . '/../views/barbero/dashboard_barbero.php';
        break;

    case '/admin/barberos':
        require __DIR__ . '/../views/administracion/admin_barberos.php';
        break;

    // ---- CONTROLADORES Y ACCIONES ----
    case '/logout':
        require __DIR__ . '/../src/controllers/logout.php';
        break;

    case '/auth_google':
        require __DIR__ . '/../src/controllers/auth_google.php';
        break;

    case '/oauth_callback':
        require __DIR__ . '/../src/controllers/oauth_callback.php';
        break;

    case '/editar_perfil':
        require __DIR__ . '/../views/barbero/editar_perfil.php';
        break;

    // ---- SI LA RUTA NO EXISTE ----
    default:
        http_response_code(404);
        echo "<h1>404 - Página no encontrada</h1>";
        echo "<p>Ruta que intentaste buscar: " . htmlspecialchars($request) . "</p>"; 
        break;
}