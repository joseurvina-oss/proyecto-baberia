<?php
// public/index.php
session_start();

// 1. Obtener la ruta solicitada de forma limpia
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 2. Limpiar la subcarpeta local de AppServ para que funcione igual en tu PC y en la nube
$request = str_replace('/saas-barberia/public', '', $request);

// 3. Si la ruta queda vacía, la forzamos a la página principal '/'
if ($request === '' || $request === '/index.php') {
    $request = '/';
}

// 4. Router principal (El "Portero" de tu aplicación)
switch ($request) {
    // ---- VISTAS PÚBLICAS Y CLIENTES ----
    case '/':
        require __DIR__ . '/../views/cliente/index.php';
        break;

    case '/reserva':
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

    // ---- CONTROLADORES Y ACCIONES (No tienen vista, solo ejecutan código) ----
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
            // Cambia la ruta para que apunte al archivo correcto que acabamos de hacer
        require __DIR__ . '/../views/barbero/editar_perfil.php';
        break;

    // ---- SI LA RUTA NO EXISTE ----
    default:
        http_response_code(404);
        echo "<h1>404 - Página no encontrada</h1>";
        echo "<p>Ruta que intentaste buscar: " . htmlspecialchars($request) . "</p>"; 
        break;
}