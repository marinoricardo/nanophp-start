<?php


use App\Controllers\StarterController;
use Core\Router;

$router = new Router();
$startController = new StarterController();
$router->get('/', [$startController, 'index']);

return $router;
