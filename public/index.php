<?php
require __DIR__ . '/../vendor/autoload.php';

use Core\Env;

// Carrega .env do novo projeto
Env::load(__DIR__ . '/../.env');

// Rotas do novo projeto
$router = require __DIR__ . '/../routes.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
