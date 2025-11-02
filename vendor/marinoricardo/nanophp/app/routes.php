<?php
use Core\Router;
use Core\Response;
use Core\Database;

$router = new Router();

$router->add('GET', '/', function() {
    Response::success(['message' => 'NanoPHP funcionando!', 'success' => 'true']);
});

$router->add('GET', '/api/test-db', function (){
    try {
        $db = Database::connect();
        $query = $db->query('SELECT * FROM userss');
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        Response::success($result, "Users retrievied Successfully");
    }catch (Exception $e){
        Response::error('Error ' . $e->getMessage(), 500);
    }
});


return $router;
