<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Importa namespaces
use Core\Env;
// Carrega .env
Env::load();

// Inclui rotas
$router = require __DIR__ . '/../app/routes.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
