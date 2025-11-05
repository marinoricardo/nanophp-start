<?php

namespace App\Controllers;

use Core\ControllerBase;

class StarterController extends ControllerBase
{
    /**
     * @throws \Exception
     */
    public function index()
    {
        $data = ["message" => "Welcome", "success" => "true", "age" => 22];

        return $this->success($data);
    }

}
