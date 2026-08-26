<?php
require __DIR__ . '/backend/config/bootstrap.php';

$page = $_GET['page'] ?? 'login';

// Verificar autenticación para páginas protegidas
$protectedPages = ['dashboard', 'eventos', 'recursos', 'inventario', 'cronograma', 'presupuestos', 'alertas', 'reportes', 'auditoria', 'evento_nuevo', 'recurso_nuevo'];
if (in_array($page, $protectedPages) && empty($_SESSION['user'])) {
    include __DIR__ . '/frontend/views/login.html';
    exit;
}

// Cargar la página solicitada
if ($page === 'dashboard') {
    include __DIR__ . '/frontend/views/dashboard.html';
    exit;
}

if ($page === 'eventos') {
    include __DIR__ . '/frontend/views/eventos.html';
    exit;
}

if ($page === 'recursos') {
    include __DIR__ . '/frontend/views/recursos.html';
    exit;
}

if ($page === 'inventario') {
    include __DIR__ . '/frontend/views/inventario.html';
    exit;
}

if ($page === 'cronograma') {
    include __DIR__ . '/frontend/views/cronograma.html';
    exit;
}

if ($page === 'presupuestos') {
    include __DIR__ . '/frontend/views/presupuestos.html';
    exit;
}

if ($page === 'alertas') {
    include __DIR__ . '/frontend/views/alertas.html';
    exit;
}

if ($page === 'reportes') {
    include __DIR__ . '/frontend/views/reportes.html';
    exit;
}

if ($page === 'auditoria') {
    include __DIR__ . '/frontend/views/auditoria.html';
    exit;
}

if ($page === 'evento_nuevo') {
    include __DIR__ . '/frontend/views/eventos_form.html';
    exit;
}

if ($page === 'recurso_nuevo') {
    include __DIR__ . '/frontend/views/recursos_form.html';
    exit;
}

if ($page === 'login') {
    include __DIR__ . '/frontend/views/login.html';
    exit;
}

// Página por defecto si hay sesión activa
if (!empty($_SESSION['user'])) {
    include __DIR__ . '/frontend/views/dashboard.html';
    exit;
}

// Si no hay sesión, mostrar login
include __DIR__ . '/frontend/views/login.html';

